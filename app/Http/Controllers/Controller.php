<?php

namespace App\Http\Controllers;

abstract class Controller
{
    //

    public string $validationErrorMessage = "The given data is invalid";
    public string $serverErrorMessage = "An error occurred while processing the request.";
}
