<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Exceptions\HttpResponseException;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // បើកាលណាវាជា API Request ឬចង់ឱ្យវាឆ្លើយតបជា JSON ជានិច្ចនៅពេលមិនទាន់ Login
        if ($request->expectsJson() || $request->is('api/*')) {
            throw new HttpResponseException(
                response()->json(['message' => 'Unauthenticated.'], 401)
            );
        }

        // សម្រាប់ Web ធម្មតាទុកឱ្យវាលោតទៅទំព័រ Login ដដែល
        return route('login');
    }
}