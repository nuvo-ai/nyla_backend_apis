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
use Smalot\PdfParser\Parser as PdfParser;
use Illuminate\Support\Str;
use thiagoalessio\TesseractOCR\TesseractOCR;

class FoodAnalyzerAIAssistanceService
{
    protected $chatgpt_service;

    public function __construct()
    {
        $this->chatgpt_service = new ChatGPTService();
    }

    public function validated(array $data)
    {
        $validator = Validator::make($data, [
            'prompt' => 'nullable',
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
            $titleText = $request->prompt ?? $request->quick_action ?? '';
            $title = $this->generateTitleFromPrompt($titleText);
            if (empty($title)) {
                $aiTitlePrompt = "Generate a concise conversation title for this prompt: " . $titleText;
                $title = $this->chatgpt_service->sendPrompt($aiTitlePrompt);
                $title = Str::limit(trim($title), 255);
            }
            $title = $this->generateTitleFromPrompt($titleText);

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

            $uploadedFileSummary = '';
            $fileUrl = '';
            $promptText = '';
            $hasFile = $request->hasFile('prompt');

            if ($hasFile) {
                $path = $request->file('prompt')->store('uploads', 'public');
                $fileUrl = asset('storage/' . $path);
                $request->merge(['file' => $request->file('prompt')]);
                $uploadedFileSummary = $this->uploadedFile($request);
                $promptText = "File uploaded: " . $fileUrl;
            } else {
                $promptText = trim((string) $request->prompt);
            }

            if (empty($promptText)) {
                throw new Exception('Prompt (text or file) is required.');
            }

            $prompt = new Chat([
                'sender' => 'user',
                'content' => $promptText,
            ]);
            $conversation->chats()->save($prompt);

            $instructionsJson = Storage::get('ai_contexts/food_analysis_instructions.json');
            $instructions = json_decode($instructionsJson, true);

            $systemPrompt = "You are an assistant for the Nyla app. Analyze the uploaded food report and provide health-related insights and dietary suggestions.";

            if (!empty($uploadedFileSummary)) {
                $systemPrompt .= "\n\n" . $uploadedFileSummary;
            }

            $responseText = $this->chatgpt_service->sendPrompt($systemPrompt);

            $response = new Chat([
                'sender' => 'ai',
                'content' => $responseText,
            ]);
            $conversation->chats()->save($response);

            return [
                'conversation' => $conversation,
                'prompt' => $promptText,
                'response' => $responseText,
            ];
        });
    }





    public function uploadedFile(Request $request)
    {
        $summary = "\n\nUser uploaded a food sample for analysis.";

        if (!$request->hasFile('file')) {
            return '';
        }

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $originalName = $file->getClientOriginalName();

        // Validate file type before saving
        if (!in_array($extension, ['txt', 'csv', 'json', 'pdf', 'jpg', 'jpeg', 'png'])) {
            return "[Unsupported file type: $originalName. Please upload a .txt, .csv, .json, .pdf, .jpg, or .png file.]";
        }

        $filePath = $file->store('uploads/doctor_ai', 'public');
        $fullPath = storage_path('app/public/' . $filePath);
        $contentSummary = '';

        try {
            if (in_array($extension, ['txt', 'csv', 'json', 'md'])) {
                $contentSummary = file_get_contents($fullPath);
            } elseif ($extension === 'pdf') {
                $parser = new PdfParser();
                $pdf = $parser->parseFile($fullPath);
                $contentSummary = $pdf->getText();
            } elseif (in_array($extension, ['jpg', 'jpeg', 'png'])) {
                $contentSummary = (new TesseractOCR($fullPath))->run();
            } else {
                $contentSummary = "[File uploaded: $originalName. Please specify what you want me to do with it.]";
            }
        } catch (\Exception $e) {
            $contentSummary = "[Error processing file: " . $e->getMessage() . "]";
        }

        return $summary
            . "\nFilename: $originalName"
            . "\nExtracted Content (truncated):\n"
            . Str::limit(trim($contentSummary), 2000);
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
