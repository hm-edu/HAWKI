<?php
declare(strict_types=1);


namespace App\Services\AI\Providers\AmazonBedrock\Request;


use App\Services\AI\Providers\AbstractRequest;
use App\Services\AI\Value\AiModel;
use App\Services\AI\Value\AiResponse;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\Exception\AwsException;
use RuntimeException;

class AmazonBedrockStreamingRequest extends AbstractRequest
{
    use AmazonBedrockRequestTrait;
    
    public function __construct(
        private array             $payload,
        private readonly \Closure $onData
    )
    {
    }
    
    public function execute(AiModel $model): void
    {
        $this->payload['stream'] = true;
        $this->executeStreamingRequest(
            model: $model,
            payload: $this->payload,
            onData: $this->onData,
            chunkToResponse: [$this, 'chunkToResponse']
        );
    }
    
    protected function chunkToResponse(AiModel $model, array $chunk): AiResponse
    {
        
        $content = '';
        $isDone = false;
        $usage = null;
        
        // Check for the finish_reason flag
        if (isset($chunk['messageStop']['stopReason']) && $chunk['messageStop']['stopReason'] == 'end_turn' ) {
            $isDone = true;
        }
        
        // Extract usage data if available
        if (!empty($chunk['metadata'])) {
            $usage = $this->extractUsage($model, null, $chunk['metadata']);
            //$isDone = true;
        }
        
        // Extract content if available
        if (isset($chunk['contentBlockDelta'])) {
            $content = $chunk['contentBlockDelta']['delta']['text'];
        }
        
        return new AiResponse(
            content: [
                'text' => $content,
            ],
            usage: $usage,
            isDone: $isDone
        );
    }



    /* Executes a streaming request to the AI model.
     *
     * @param AiModel $model The AI model to interact with.
     * @param array $payload The request payload to send.
     * @param callable(AiResponse $response): void $onData Callback executed for each chunk of data received.
     * @param callable(AiModel $model, string $chunk): AiResponse $chunkToResponse Callback to transform a chunk into a response.
     * @param callable():array|null $getHttpHeaders Optional callback to generate HTTP headers.
     * @param string|null $apiUrl Optional API URL to override the model's default.
     * @param int|null $timeout Optional timeout for the request in seconds.
     * @return void
     */
    protected function executeStreamingRequest(
        AiModel   $model,
        array     $payload,
        callable  $onData,
        callable  $chunkToResponse,
        ?callable $getHttpHeaders = null,
        ?string   $apiUrl = null,
        ?int      $timeout = null
    ): void
    {
        set_time_limit($timeout ?? 120);

        $region = $model->getRegion();
        if ($region == ''){
            $region = $model->getProvider()->getConfig()->getRegion();
        }

        $client = new BedrockRuntimeClient([
            'region' => $region,
            'profile' => 'default'
        ]);

        $response = $client->converseStream([
                'modelId' => $model->getid(),
                'messages' => $payload['messages']
        ]);

        foreach($response['stream'] as $event) {
            $onData($chunkToResponse($model, $event));
        }
    }
}
