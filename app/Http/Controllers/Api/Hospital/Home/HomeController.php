<?php

namespace App\Http\Controllers\Api\Hospital\Home;

use App\Constants\General\ApiConstants;
use App\Helpers\ApiHelper;
use App\Http\Controllers\Controller;
use App\Services\Hospital\Home\Homeservice;
use Exception;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    protected $home_service;

    public function __construct()
    {
        $this->home_service = new Homeservice;
    }
    public function home()
    {
        try {
            $data = $this->home_service->getData();
            return ApiHelper::validResponse("Home Page Data retrieved successfully", $data);
        } catch (Exception $e) {
            return ApiHelper::problemResponse($this->serverErrorMessage, ApiConstants::SERVER_ERR_CODE, null, $e);
        }
    }
}
