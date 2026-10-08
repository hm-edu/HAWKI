<?php
declare(strict_types=1);


namespace App\Services\AI\Providers\AmazonBedrock\Request;


use App\Services\AI\Providers\AbstractRequest;
use App\Services\AI\Value\AiModel;
use App\Services\AI\Value\AiResponse;
use Aws\BedrockRuntime\BedrockRuntimeClient;
use Aws\Exception\AwsException;
use RuntimeException;

class AmazonBedrockNonStreamingRequest extends AbstractRequest
{
    use AmazonBedrockRequestTrait;
    
    public function __construct(
        private array $payload
    )
    {
    }
    
    public function execute(AiModel $model): AiResponse
    {   
        $this->payload['stream'] = false;
        return $this->executeNonStreamingRequest(
            model: $model,
            payload: $this->payload,
            dataToResponse: function (\Aws\Result $data) use ($model) {
                $content = $data['output']['message']['content'][0]['text'];
                
                return new AiResponse(
                    content: [
                        'text' => $content
                    ],
                    usage: $this->extractUsage($model, $data)
                );
            }
        );
    }

    protected function executeNonStreamingRequest(
        AiModel   $model,
        array     $payload,
        callable  $dataToResponse,
        ?callable $getHttpHeaders = null,
        ?string   $apiUrl = null,
        ?int      $timeout = null
    ): AiResponse
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

        try {
            // Send the message to the model, using a basic inference configuration.
            $response = $client->converse([
                'modelId' => $model->getid(),
                'messages' => $payload['messages']
            ]);

            // Extract and return the response text.
            //$responseText = $response['output']['message']['content'][0]['text'];
            //return $responseText;

            //$data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            return $dataToResponse($response);
        } catch (AwsException $e) {
            echo "ERROR: Can't invoke {$model->getid()}. Reason: {$e->getAwsErrorMessage()}";
            throw new RuntimeException("Failed to invoke model: " . $e->getAwsErrorMessage(), 0, $e);
        }


        //$data = json_decode($response, true, 512, JSON_THROW_ON_ERROR);

        //return $dataToResponse($data);
    }

}
