<?php

namespace App\Services\AI;

use App\Models\General\Chat;
use App\Models\General\Conversation;
use App\Services\AI\ChatGPT\ChatGPTService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class AssessmentAIAssistanceService
{
    protected $chatgpt_service;

    public function __construct()
    {
        $this->chatgpt_service = new ChatGPTService();
    }

    /**
     * Validate incoming request data
     */
    public function validated(array $data)
    {
        $validator = Validator::make($data, [
            'responses'            => 'required|array',
            'responses.*.question' => 'required|string',
            'responses.*.answer'   => 'required|string',
            'conversation_id'      => 'nullable|integer|min:0',
            'ai_type'              => 'nullable|string',
            'title'                => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    /**
     * Create a new conversation or continue an existing one
     */
    public function createConversation(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $user = Auth::user();

            $titleText = "Mental Health Assessment for User: " . $user->name;

            $title = $this->generateTitleFromPrompt($titleText);

            if (empty($title)) {
                $aiTitlePrompt = "Generate a concise title for a mental health assessment conversation: " . $titleText;
                $title = $this->chatgpt_service->sendPrompt($aiTitlePrompt);
                $title = Str::limit(trim($title), 255);
            }

            // ✅ Validate request with question + answer
            $validated = $this->validated($request->all());

            // ✅ Create or get conversation
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
                    'ai_type' => $validated['ai_type'] ?? 'mental_health',
                    'title'   => $validated['title'] ?? $title,
                ]);
            }

            // ✅ Save user responses into chats (with question + answer)
            foreach ($validated['responses'] as $resp) {
                $prompt = new Chat([
                    'sender'  => 'user',
                    'content' => "Q: {$resp['question']}\nA: {$resp['answer']}"
                ]);
                $conversation->chats()->save($prompt);
            }

            // ✅ Load structured AI instructions
            $systemInstructions = $this->getSystemPrompt();

            // ✅ Build final prompt with responses
            $systemPrompt = $systemInstructions . "\n\nUser responses:\n";
            foreach ($validated['responses'] as $resp) {
                $systemPrompt .= "Q: {$resp['question']}\nA: {$resp['answer']}\n";
            }

            // ✅ Send to AI
            $responseText = $this->chatgpt_service->sendPrompt($systemPrompt);

            // ✅ Save AI response
            $response = new Chat([
                'sender'  => 'ai',
                'content' => $responseText,
            ]);
            $conversation->chats()->save($response);

            return [
                'conversation' => $conversation,
                'responses'    => $validated['responses'],
                'ai_response'  => $responseText,
            ];
        });
    }

    /**
     * Generate a short title for conversation
     */
    protected function generateTitleFromPrompt(string $prompt): ?string
    {
        $text = trim(strip_tags($prompt));
        $words = preg_split('/\s+/', $text);

        if (count($words) <= 1) return null;

        $cleanText = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
        $cleanWords = preg_split('/\s+/', trim($cleanText));
        $title = implode(' ', array_slice($cleanWords, 0, 6));

        return ucfirst($title) . (count($cleanWords) > 6 ? '...' : '');
    }

    /**
     * Structured system instructions for Nyla AI
     */
    protected function getSystemPrompt(): string
    {
        return <<<EOT
Nyla AI for Mental Health – Prompt Template.

You are Nyla AI, a mental health intelligence assistant designed to provide AI-driven insights and support based on user responses to standard mental health screening questions.

ROLE & FUNCTIONALITY:
- Act as an empathetic, supportive, and professional assistant.
- Analyze the user's responses carefully.
- Identify possible mental health challenges (depression, anxiety, stress, sleep disorders, etc.).
- Provide practical coping strategies, lifestyle recommendations, and when professional help may be needed.

INSTRUCTIONS:
- Always maintain an empathetic, respectful, and supportive tone.
- Begin every response with: "Hello, I’m Nyla AI, your mental health assistant. I will provide you with insights based on your responses."
- Provide structured feedback in this format:
  1. **Summary**: Summarize the user’s current emotional/mental state.
  2. **Possible Concerns**: Point out any potential mental health issues.
  3. **Coping Strategies**: Provide actionable, practical suggestions (including culturally relevant ones, e.g., exercise, journaling, talking with trusted family, faith practices, community support in Nigeria/Africa).
  4. **Next Steps**: Recommend professional support or helplines if symptoms are moderate/severe.

CONVERSATION GUIDELINES:
- Be concise but empathetic.
- Avoid generic advice like “take care of your health.” Instead, give specific, convincing suggestions.

SAFETY CLAUSE:
- Always end responses with: "Note: These insights are AI-generated and should not replace professional medical advice. If you are experiencing severe distress or suicidal thoughts, please seek immediate help from a qualified professional or contact emergency services in your area."
EOT;
    }

    /**
     * Predefined questions (optional for frontend)
     */
    private function getMentalHealthQuestions(): array
    {
        return [
            ['question' => 'Over the past 2 weeks, how often have you felt little interest or pleasure doing things?', 'category' => 'Mental Health'],
            ['question' => 'Over the past 2 weeks, how often have you felt down, depressed, or hopeless?', 'category' => 'Mental Health'],
            ['question' => 'Over the past 2 weeks, how often have you had trouble falling or staying asleep, or sleeping too much?', 'category' => 'Mental Health'],
            ['question' => 'Over the past 2 weeks, how often have you felt tired or had little energy?', 'category' => 'Mental Health'],
            ['question' => 'Over the past 2 weeks, how often have you had a poor appetite or overeating?', 'category' => 'Mental Health'],
            ['question' => 'Over the past 2 weeks, how often have you felt bad about yourself or that you are a failure or have let yourself or your family down?', 'category' => 'Mental Health'],
            ['question' => 'Over the past 2 weeks, how often have you had trouble concentrating on things, such as reading or watching TV?', 'category' => 'Mental Health'],
            ['question' => 'Over the past 2 weeks, how often have you been feeling anxious, nervous or on edge?', 'category' => 'Mental Health'],
            ['question' => 'Over the past 2 weeks, how often have you not been able to stop or control worrying?', 'category' => 'Mental Health'],
        ];
    }
}
