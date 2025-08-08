<?php

namespace App\Services\AI;

use App\Models\General\Chat;
use App\Models\General\Conversation;
use App\Models\Hospital\HospitalPatient;
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

class DoctorAIAssistanceService
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
        return DB::transaction(function () use ($request) {
            $user = Auth::user();
            if (!$user->hospitalUser || strtolower(!$user->hospitalUser->role) === 'doctor') {
                throw new Exception('Please, these chats or conversations are only meant for doctors.');
            }
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
            $patientSummary = "";
            if ($request->filled('patient_id')) {
                $patient = HospitalPatient::find($request->patient_id);

                if ($patient) {
                    $user = optional($patient->user);
                    $details = [
                        "[Patient EMR Tag]: Patient with EMR found. Details:",
                        "Name: " . $user->full_name,
                        "Gender: " . $user->gender,
                        "Date of Birth: " . $patient->date_of_birth,
                    ];

                    if ($patient->temperature) $details[] = "Temperature: {$patient->temperature}";
                    if ($patient->weight) $details[] = "Weight: {$patient->weight}";
                    if ($patient->height) $details[] = "Height: {$patient->height}";
                    if ($patient->blood_pressure) $details[] = "Blood Pressure: {$patient->blood_pressure}";
                    if ($patient->heart_rate) $details[] = "Heart Rate: {$patient->heart_rate}";
                    if ($patient->respiratory_rate) $details[] = "Respiratory Rate: {$patient->respiratory_rate}";
                    if ($patient->oxygen_saturation) $details[] = "Oxygen Saturation: {$patient->oxygen_saturation}";
                    if ($patient->last_visit) $details[] = "Last Visit: {$patient->last_visit}";
                    if ($patient->emergency_contact_name) $details[] = "Emergency Contact Name: {$patient->emergency_contact_name}";
                    if ($patient->emergency_contact_phone) $details[] = "Emergency Contact Phone: {$patient->emergency_contact_phone}";
                    if ($patient->chief_complaints) $details[] = "Chief Complaints: {$patient->chief_complaints}";
                    if ($patient->pain_level) $details[] = "Pain Level: {$patient->pain_level}";
                    if ($patient->visit_priority) $details[] = "Visit Priority: {$patient->visit_priority}";
                    if ($patient->visit_type) $details[] = "Visit Type: {$patient->visit_type}";
                    if ($patient->referral_source) $details[] = "Referral Source: {$patient->referral_source}";
                    if ($patient->medical_history) $details[] = "Medical History: {$patient->medical_history}";
                    if ($patient->insurance_info) $details[] = "Insurance Info: {$patient->insurance_info}";

                    if (!empty($patient->current_symptoms)) {
                        $details[] = "Current Symptoms: " . implode(', ', (array) $patient->current_symptoms);
                    }

                    if (!empty($patient->know_allergies)) {
                        $details[] = "Known Allergies: " . implode(', ', (array) $patient->know_allergies);
                    }

                    if (!empty($patient->current_medications)) {
                        $details[] = "Current Medications: " . implode(', ', (array) $patient->current_medications);
                    }

                    $patientSummary = "\n\n" . implode("\n", $details);
                } else {
                    $patientSummary = "\n\n[Patient EMR Tag]: No patient found with the provided ID.";
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
            $instructionsJson = Storage::get('ai_contexts/doctor_instructions.json');
            $instructions = json_decode($instructionsJson, true);

            $systemPrompt = "You are an assistant for the Nyla app. Here's the app context:\n\n"
                . json_encode($instructions, JSON_PRETTY_PRINT)
                . $patientSummary
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
        $summary = "\n\nDoctor uploaded a medical file for analysis.";

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



    protected function generateTitleFromPrompt(?string $prompt): ?string
    {
        if (!is_string($prompt) || trim($prompt) === '') {
            return null;
        }

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
