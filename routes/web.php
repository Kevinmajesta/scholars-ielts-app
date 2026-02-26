<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\IeltsController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});




