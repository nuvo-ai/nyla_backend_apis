<?php

namespace App\Services\AI\ChatGPT;

use Exception;
use Illuminate\Support\Facades\Http;

class ChatGPTService
{
   public function sendPrompt(array $messages, string $model = 'gpt-3.5-turbo')
{
    $response = Http::withHeaders([
        'Content-Type' => 'application/json',
    ])
        ->withToken(config('services.openai.api_key'))
        ->post(config('services.openai.base_url') . '/chat/completions', [
            'model' => $model,
            'messages' => $messages,
        ]);

    if (!$response->successful()) {
        throw new Exception('OpenAI API error: ' . $response->body());
    }

    $data = $response->json();
    return $data['choices'][0]['message']['content'] ?? 'No reply';
}

}
