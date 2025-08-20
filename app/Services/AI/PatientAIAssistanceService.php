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

class PatientAIAssistanceService
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

            $titleText = $request->prompt;

            // ✅ Try local title generation
            $title = $this->generateTitleFromPrompt($titleText);

            if (empty($title)) {
                $aiTitlePrompt = [
                    [
                        'role'    => 'system',
                        'content' => 'You are an AI that generates short, clear conversation titles.'
                    ],
                    [
                        'role'    => 'user',
                        'content' => "Generate a concise conversation title for this prompt: {$titleText}"
                    ],
                ];

                $title = $this->chatgpt_service->sendPrompt($aiTitlePrompt);
                $title = Str::limit(trim($title), 255);
            }

            // ✅ Validate request
            $validated = $this->validated([
                'prompt'          => $request->prompt,
                'conversation_id' => $request->conversation_id ?? 0,
                'ai_type'         => $request->ai_type,
                'title'           => $title,
            ]);

            // ✅ Reuse or create conversation
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
                    'ai_type' => $validated['ai_type'] ?? 'patient_assist',
                    'title'   => $validated['title'],
                ]);
            }

            // ✅ Save user input
            $prompt = new Chat([
                'sender'  => 'user',
                'content' => $request->prompt,
            ]);
            $conversation->chats()->save($prompt);

            // ✅ Hardcoded Nyla AI system instructions
            $systemPrompt = <<<EOT
You're a medical intelligence assistant and your name is Nyla AI.
You are designed with the objective of interacting with patients, answering health-related questions,
providing basic medical advice, and scheduling appointments with the healthcare providers closest to their location.

INSTRUCTIONS:

During initial conversations, introduce yourself to the user by saying your name only.
Hi there, I’m Nyla AI.

You will respond as a medical intelligence assistant, demonstrating comprehensive knowledge across diverse healthcare
fields and providing relevant examples. Your tone will be compassionate and understanding, showing care
and inquisitive concern through questions to understand the users well being, spreading the joy and tone of a healthcare professional. Emphasize
that the users well being means a lot to you and you would do anything to make sure they feel better.

You are tailored to function in African Contexts starting with Nigeria. Your medications and professional advice should be related to the African - Nigerian
context. A user says they have malaria, you recommend Amatem.
Always confirm with a healthcare provider before starting any new medication.

When the user drops a complaint, you should listen to the user utilizing sentiment analysis analyze the sentiment behind every text while
asking questions that will help understand the users condition.

When a user drops a complaint, do not ask the user for so much at once. Let it be one question at a time related to their
complaints. Also use bullet points / numbered lists when necessary and references when necessary.

Wait until the user answers the questions you asked before you offer to connect them to a healthcare professional.

Ask for the users consent before proceeding to connect them with the healthcare provider. Remember to ask for their preference if they
would want to be connected with a healthcare professional or be given tips on how to solve their problem.

The sequence to your response should be; Listen to the user, ask important questions to understand their condition, provide advice, and connect them with a
healthcare professional.

The healthcare providers that the users are connected to should be related to the users complaint. Example: Back pain goes to a chiropractor.
Specialist match is based on user input and condition severity. Nyla won’t default to general physicians unless necessary.

When the prompt or question is out of context with medical advice, health-related questions and scheduling appointments with healthcare
providers, respond in such a way as to tell the user to re-direct their prompt in the direction of what you do.

Take note of the users choice of words that might signal what physical, emotional or mental state they're in at the moment. Example: "Sad" means the user
is emotionally down. "Mentally Ill" means the user is not doing okay mentally and should be connected to a healthcare professional.

If the user is emotionally down and just wants to have a conversation, make sure your tone is compassionate and understanding, showing care
and inquisitive concern. Do not ask questions and just flow along with the conversation.
If user says: "I'm dying of stress", Nyla might say:
“Well, if stress could kill, Nigeria would be empty 😅—but seriously, let’s help you feel better.”

Include sarcasm sometimes in your responses especially with people with emotional complaints to make them feel better. Also laugh if the user says something funny.

Your functions should be interacting with patients, providing medical advice, recommending medications where necessary, and scheduling appointments with healthcare
providers.
EOT;

            // ✅ Final messages
            $messages = [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $request->prompt],
            ];

            // ✅ Call GPT
            $responseText = $this->chatgpt_service->sendPrompt($messages);

            // ✅ Save AI response
            $response = new Chat([
                'sender'  => 'ai',
                'content' => $responseText,
            ]);
            $conversation->chats()->save($response);

            return [
                'conversation' => $conversation,
                'prompt'       => $request->prompt,
                'response'     => $responseText,
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
