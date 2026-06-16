<?php
declare(strict_types=1);


namespace App\Services\AI\Providers\AmazonBedrock;


use App\Services\AI\Value\AiRequest;
use Illuminate\Container\Attributes\Singleton;

#[Singleton]
class AmazonBedrockRequestConverter
{
    public function convertRequestToPayload(AiRequest $request): array
    {
        $rawPayload = $request->payload;
        $model = $request->model;
        $messages = $rawPayload['messages'];
        $modelId = $rawPayload['model'];
        
        // Handle special cases for specific models
        $messages = $this->handleModelSpecificFormatting($modelId, $messages);
        
        // Format messages for OpenAI
        $formattedMessages = [];
        foreach ($messages as $message) {
            $formattedMessages[] = [
                'role' => $message['role'],
                'content' => [["text" => $message['content']['text'] ]]
               // $message['content']['text']
            ];
        }
        
        // Build payload with common parameters
        $payload = [
            'model' => $modelId,
            'messages' => $formattedMessages,
            //'stream' => $rawPayload['stream'] && $model->hasTool('stream'),
        ];
        
        // // Add optional parameters if present in the raw payload
        // if (isset($rawPayload['temperature'])) {
        //     $payload['temperature'] = $rawPayload['temperature'];
        // }
        
        // if (isset($rawPayload['top_p'])) {
        //     $payload['top_p'] = $rawPayload['top_p'];
        // }
        
        // if (isset($rawPayload['frequency_penalty'])) {
        //     $payload['frequency_penalty'] = $rawPayload['frequency_penalty'];
        // }
        
        // if (isset($rawPayload['presence_penalty'])) {
        //     $payload['presence_penalty'] = $rawPayload['presence_penalty'];
        // }
        
        return $payload;
    }
    
    /**
     * Handle special formatting requirements for specific models
     *
     * @param string $modelId
     * @param array $messages
     * @return array
     */
    protected function handleModelSpecificFormatting(string $modelId, array $messages): array
    {
        $array = ['mistral.mixtral-8x7b-instruct-v0:1','eu.mistral.pixtral-large-2502-v1:0'];
        $contains = in_array($modelId, $array, true);
        // Special case for o1-mini: convert system to user
        if ($contains && isset($messages[0]) && $messages[0]['role'] === 'system') {
            $messages[0]['role'] = 'user';
        }
        
        return $messages;
    }
}
