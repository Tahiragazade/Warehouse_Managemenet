<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
     protected $except = [
        //
        'http://127.0.0.1:8000/warehouse/store', //This is the url that I dont want Csrf for postman.
        'http://127.0.0.1:8000/transaction/store', //This is the url that I dont want Csrf for postman.
    ];
}
