<?php
declare(strict_types=1);


namespace App\Services\AI\Providers\AmazonBedrock\Request;


use App\Services\AI\Value\AiModel;
use App\Services\AI\Value\TokenUsage;

trait AmazonBedrockRequestTrait
{
    protected function extractUsage(AiModel $model, ?\Aws\Result $data, array $stream = null): ?TokenUsage
    {
        // if (is_null($data)&& is_null($stream)) {
        //     return null;
        // }
        if(!is_null($data)){
            return new TokenUsage(
                model: $model,
                promptTokens: (int)$data['usage']['inputTokens'],
                completionTokens: (int)$data['usage']['outputTokens'],
            );
        }
        if(!is_null($stream)){
            return new TokenUsage(
                model: $model,
                promptTokens: (int)$stream['usage']['inputTokens'],
                completionTokens: (int)$stream['usage']['outputTokens'],
            );
        }
        return null;
    }
    
    private function containsKey($obj, $targetKey)
    {
        if (!is_array($obj)) {
            return false;
        }
        if (array_key_exists($targetKey, $obj)) {
            return true;
        }
        foreach ($obj as $value) {
            if ($this->containsKey($value, $targetKey)) {
                return true;
            }
        }
        return false;
    }
    
    private function getValueForKey($obj, $targetKey)
    {
        if (!is_array($obj)) {
            return null;
        }
        if (array_key_exists($targetKey, $obj)) {
            return $obj[$targetKey];
        }
        foreach ($obj as $value) {
            $result = $this->getValueForKey($value, $targetKey);
            if ($result !== null) {
                return $result;
            }
        }
        return null;
    }
}
