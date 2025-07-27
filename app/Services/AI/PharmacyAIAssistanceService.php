<?php

namespace App\Services\AI;

use App\Models\General\Chat;
use App\Models\General\Conversation;
use App\Models\Pharmacy\Order;
use App\Models\User\User;
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
            'prompt' => 'nullable|string',
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
        // dd($request->all());
        return DB::transaction(function () use ($request) {
            $user = Auth::user();
            if (!$user->hospitalUser || strtolower(!$user->hospitalUser->role) === 'doctor') {
                throw new Exception('Please, these chats or conversations are only meant for doctors.');
            }
            $titleText = $request->prompt ?? $request->quick_action;
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
                    'hospital_user_id' => $user->hospitalUser->id,
                ]);
            }

            $prompt = new Chat([
                'sender' => 'user',
                'content' => $request->prompt ?? $request->quick_action,
            ]);
            $conversation->chats()->save($prompt);

            $uploadedFileSummary = '';
            if ($request->hasFile('file')) {
                $uploadedFileSummary = $this->uploadedFile($request);
            }
            $orderSummary = "";
            if ($request->filled('order_id')) {
                $order = Order::with(['orderItems.medication', 'pharmacy', 'creator.hospitalUser'])->find($request->order_id);

                if ($order) {
                    $createdByUser = optional($order->createdBy);
                    $createdByInfo = $createdByUser->name ?? 'Unknown';

                    if ($createdByUser && $createdByUser->hospitalUser) {
                        $createdByInfo .= " ({$createdByUser->hospitalUser->role})";
                    }

                    $orderDetails = [
                        "[Order Info Tag]: Order found. Details:",
                        "Order ID: {$order->id}",
                        "Pharmacy: " . optional($order->pharmacy)->name,
                        "Created By: {$createdByInfo}",
                        "Priority: {$order->priority}",
                        "Status: {$order->status}",
                        "Total Price: ₦" . number_format($order->total_price, 2),
                    ];

                    foreach ($order->orderItems as $item) {
                        $orderDetails[] = "- Medication: " . optional($item->medication)->name .
                            ", Qty: {$item->quantity}, Price: ₦" . number_format($item->price, 2);
                    }

                    $orderSummary = "\n\n" . implode("\n", $orderDetails);
                } else {
                    $orderSummary = "\n\n[Order Info Tag]: No order found with the provided ID.";
                }
            }

            $quickAction = $request->quick_action ? "\n\n[Action Requested]: " . $request->quick_action : '';
            $promptText = trim($request->prompt ?? '');
            $hasFile = $request->hasFile('file');
            if (empty($promptText) && empty($quickAction) && $hasFile) {
                $promptText = "You have uploaded a file. Please specify what you want Nyla AI to do with this file.";
            }

            if (empty($promptText) && empty($quickAction) && !$hasFile) {
                throw new Exception('Prompt or quick action is required.');
            }
            $instructionsJson = Storage::get('ai_contexts/pharmacy_instructions.json');
            $instructions = json_decode($instructionsJson, true);

            $systemPrompt = "You are an assistant for the Nyla app. Here's the app context:\n\n"
                . json_encode($instructions, JSON_PRETTY_PRINT)
                . $orderSummary
                . $uploadedFileSummary
                . $quickAction
                . "\n\nUser asked: " .  $promptText;

            $responseText = $this->chatgpt_service->sendPrompt($systemPrompt);

            $response = new Chat([
                'sender' => 'ai',
                'content' => $responseText,
            ]);
            $conversation->chats()->save($response);

            return [
                'conversation' => $conversation,
                'prompt' => $request->prompt ?? $request->quick_action,
                'response' => $responseText,
            ];
        });
    }

    public function uploadedFile(Request $request)
    {
        $summary = "\n\nPharmacist uploaded a medical file for analysis.";

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
