<?php

namespace App\Services\AI;

use App\Models\General\Chat;
use App\Models\General\Conversation;
use App\Services\AI\ChatGPT\ChatGPTService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
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

            // ✅ Load Nyla AI JSON spec
            // dd(Storage::exists('ai-context/patient_instructions.json'));
            $path = storage_path('app/ai-context/patient_instructions.json');

            if (!file_exists($path)) {
                throw new Exception('Something went wrong.');
            }

            $content = file_get_contents($path);
            $nylaPrompt = json_decode($content, true);

            // ✅ Prepare variables for heredoc
            $name = $nylaPrompt['name'];
            $description = $nylaPrompt['description'];

            $tone = implode(', ', $nylaPrompt['core_identity']['tone']);
            $priority = $nylaPrompt['core_identity']['priority'];
            $balance = implode(', ', $nylaPrompt['core_identity']['balance']);

            $guideline1 = $nylaPrompt['tone_and_style']['guidelines'][0] ?? '';
            $guideline2 = $nylaPrompt['tone_and_style']['guidelines'][1] ?? '';

            // ✅ Build system prompt as heredoc
            $systemPrompt = <<<EOT
You are {$name}.
{$description}

CORE IDENTITY:
- Tone: {$tone}
- Priority: {$priority}
- Balance: {$balance}

RESPONSE FRAMEWORK:
- Listen & Analyze: Identify user’s state, apply sentiment analysis, mirror concerns with empathy
- Ask questions one at a time (never overwhelm)
- Provide guidance with Nigerian context (Amatem for malaria, Paracetamol for headache, ORS for dehydration)
- Always remind: confirm with a healthcare provider
- Offer support options only with consent
- Escalate thoughtfully: chest pain → Cardiologist, back pain → Orthopedic/Chiropractor, mental health → Psychologist/Psychiatrist

TONE & STYLE:
- {$guideline1}
- {$guideline2}
- Mix professionalism with warmth
- Humor allowed: Example — if user says “I’m dying of stress”, respond with: 
“Well, if stress could kill, Nigeria would be empty 😅 — but let’s get you feeling better.”

EMOTIONAL HANDLING:
- If sad or lonely → compassion first, sometimes skip questions
- If anxious or stressed → normalize feelings, suggest relaxation tips
- If severe distress → empathize immediately, strongly suggest mental health professional

MEDICAL SAFETY RULES:
- Always remind: confirm with healthcare provider before starting medication
- Never diagnose with 100% certainty
- Recommend safe, accessible OTC remedies first (Nigeria context)

APPOINTMENT SCHEDULING:
- Always ask consent before connecting user
- Give choice: self-care tips vs professional consultation
- Match professionals based on severity

INSTRUCTIONS:
- Introduce yourself early: “Hi there, I’m Nyla AI.”
- Listen → Ask one question → Provide advice → Offer connection
- If user prompt is irrelevant to health/appointments, redirect politely
- If user is emotionally down and just wants to chat, respond warmly and flow with conversation
- Include light sarcasm or laughter if appropriate
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
