<?php

namespace App\Services\AI;

use App\Models\General\Chat;
use App\Models\General\Conversation;
use App\Models\User\User;
use App\Services\AI\ChatGPT\ChatGPTService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class PharmacyAIAssistanceService
{
    protected $chatgpt_service;

    public function __construct()
    {
        $this->chatgpt_service = new ChatGPTService();
    }

    public function validated(array $data)
    {
        $validator = Validator::make($data, [
            'prompt' => 'required|string',
            'conversation_id' => 'nullable|integer|min:0',
            'ai_type' => 'nullable|string',
            'title' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    public function createConversation(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user = Auth::user();
            if (!$user->pharmacy) {
                throw new Exception('Please, these chats or conversations are only meant for pharmacy.');
            }
            $title = $this->generateTitleFromPrompt($request->prompt);

            $validated = $this->validated([
                'prompt' => $request->prompt,
                'conversation_id' => $request->conversation_id ?? 0,
                'ai_type' => $request->ai_type,
                'title' => $title,
            ]);

            if (!empty($validated['conversation_id']) && $validated['conversation_id'] > 0) {
                $conversation = Conversation::where('id', $validated['conversation_id'])
                    ->where('user_id', $user->id)
                    ->first();

                if (!$conversation) {
                    throw new Exception('Conversation not found.');
                }
            } else {
                $conversation = Conversation::create([
                    'user_id' => $user->id,
                    'ai_type' => $validated['ai_type'] ?? '',
                    'title' => $validated['title'],
                ]);
            }

            $prompt = new Chat([
                'sender' => 'user',
                'content' => $request->prompt,
            ]);
            $conversation->chats()->save($prompt);

            $instructionsJson = Storage::get('ai_contexts/pharmacy_instructions.json');
            $instructions = json_decode($instructionsJson, true);

            $systemPrompt = "You are an assistant for the Nyla app. Here's the app context:\n\n"
                . json_encode($instructions, JSON_PRETTY_PRINT);

            $responseText = $this->chatgpt_service->sendPrompt($systemPrompt . "\n\nUser asked: " . $request->prompt);

            $response = new Chat([
                'sender' => 'ai',
                'content' => $responseText,
            ]);
            $conversation->chats()->save($response);

            return [
                'conversation' => $conversation,
                'prompt' => $request->prompt,
                'response' => $responseText,
            ];
        });
    }

    protected function generateTitleFromPrompt(string $prompt): ?string
    {
        $text = trim(strip_tags($prompt));
        $words = preg_split('/\s+/', $text);

        if (count($words) <= 1) {
            return null;
        }

        $cleanText = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $cleanWords = preg_split('/\s+/', trim($cleanText));
        $title = implode(' ', array_slice($cleanWords, 0, 6));

        return ucfirst($title) . (count($cleanWords) > 6 ? '...' : '');
    }
}
