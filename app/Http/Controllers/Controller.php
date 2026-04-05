<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * Base controller class for all application controllers.
 */
abstract class Controller
{
    use AuthorizesRequests;
}
