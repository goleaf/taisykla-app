<?php

namespace App\Http\Controllers;

abstract class Controller
{
    use \Illuminate\Foundation\Auth\Access\AuthorizesRequests, \Illuminate\Foundation\Bus\DispatchesJobs, \Illuminate\Foundation\Validation\ValidatesRequests;
}
