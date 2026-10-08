<?php
declare(strict_types=1);


namespace App\Services\AI\Providers\AmazonBedrock;


use App\Services\AI\Providers\AbstractClient;
use App\Services\AI\Providers\AmazonBedrock\Request\AmazonBedrockNonStreamingRequest;
use App\Services\AI\Providers\AmazonBedrock\Request\AmazonBedrockStreamingRequest;
use App\Services\AI\Value\AiModelStatusCollection;
use App\Services\AI\Value\AiRequest;
use App\Services\AI\Value\AiResponse;

class AmazonBedrockClient extends AbstractClient
{
    public function __construct(
        private readonly AmazonBedrockRequestConverter $requestConverter
    )
    {
    }
    
    /**
     * @inheritDoc
     */
    protected function executeRequest(AiRequest $request): AiResponse
    {
        return (new AmazonBedrockNonStreamingRequest($this->requestConverter->convertRequestToPayload($request)))
            ->execute($request->model);
    }
    
    /**
     * @inheritDoc
     */
    protected function executeStreamingRequest(AiRequest $request, callable $onData): void
    {
        (new AmazonBedrockStreamingRequest(
            $this->requestConverter->convertRequestToPayload($request), 
            $onData
        ))->execute($request->model);
    }
    
    /**
     * @inheritDoc
     */
    protected function resolveStatusList(AiModelStatusCollection $statusCollection): void
    {
        // @todo implement model status check for OpenWebUi
        $statusCollection->setAllOnline();
    }
    
}
