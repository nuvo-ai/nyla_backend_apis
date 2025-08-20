<?php

namespace App\Http\Controllers\Api\AI;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AI\V1\AssessmentAIAssistanceResource;
use App\Http\Resources\User\UserResource;
use App\Models\General\Conversation;
use App\Models\User\User;
use App\Services\AI\AssessmentAIAssistanceService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AssessmentAIAssistanceController extends Controller
{
    protected $assessment_ai_service;

    public function __construct()
    {
        $this->assessment_ai_service = new AssessmentAIAssistanceService();
    }

    /**
     * Start or continue a mental health AI conversation
     */
    public function ask(Request $request)
    {
        try {
            $result = $this->assessment_ai_service->createConversation($request);

            $conversation = $result['conversation'];
            $user = $conversation->user;
            $chats = AssessmentAIAssistanceResource::collection($conversation->chats()->get());

            return ApiHelper::validResponse('Conversation created successfully', [
                'user' => new UserResource($user),
                'responses' => $result['responses'],
                'ai_response' => $result['ai_response'],
                'chats' => $chats,
            ]);
        } catch (ValidationException $e) {
            return ApiHelper::inputErrorResponse(
                $this->validationErrorMessage,
                ApiConstants::VALIDATION_ERR_CODE,
                null,
                $e
            );
        } catch (Exception $e) {
            $message = $this->serverErrorMessage;
            return ApiHelper::problemResponse($message, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    /**
     * Get all mental health conversations for the authenticated user
     */
    public function getConversations()
    {
        try {
            $user = User::getAuthenticatedUser();
            $conversations = Conversation::with('chats')
                ->where('user_id', $user->id)
                ->where('ai_type', 'assessment')
                ->latest()
                ->get();

            if ($conversations->isEmpty()) {
                return ApiHelper::validResponse('No conversations found', [
                    'conversations' => [],
                ]);
            }

            return ApiHelper::validResponse('User conversations fetched successfully', [
                'conversations' => $conversations,
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::problemResponse($message, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }

    /**
     * Get a single conversation with chats by UUID
     */
    public function getConversationWithChats($uuid)
    {
        try {
            $user = User::getAuthenticatedUser();
            $conversation = Conversation::with('chats')
                ->where('user_id', $user->id)
                ->where('uuid', $uuid)
                ->where('ai_type', 'assessment')
                ->firstOrFail();

            return ApiHelper::validResponse('Conversation chats fetched successfully', [
                'conversation' => $conversation,
            ]);
        } catch (Exception $e) {
            $message = $e->getMessage() ?: $this->serverErrorMessage;
            return ApiHelper::problemResponse($message, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
