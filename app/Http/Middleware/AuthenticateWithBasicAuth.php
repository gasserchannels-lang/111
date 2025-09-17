<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateWithBasicAuth
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $username = $request->getUser();
        $password = $request->getPassword();
        if (!$username) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="API"',
            ]);
        }
        if (!$password) {
            return response('Unauthorized', 401, [
                'WWW-Authenticate' => 'Basic realm="API"',
            ]);
        }
        if ($username === config('app.basic_auth_username') &&
            $password === config('app.basic_auth_password')) {
            return $next($request);
        }

        return response('Unauthorized', 401, [
            'WWW-Authenticate' => 'Basic realm="API"',
        ]);
    }
}
