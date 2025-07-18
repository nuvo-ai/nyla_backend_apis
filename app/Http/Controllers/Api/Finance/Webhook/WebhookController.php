<?php

namespace App\Http\Controllers\Api\Finance\Webhook;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Billing\WebhookService;

class WebhookController extends Controller
{
    protected $service;

    public function __construct(WebhookService $service)
    {
        $this->service = $service;
    }

    public function handle(Request $request)
    {
        return $this->service->handle($request);
    }
}
