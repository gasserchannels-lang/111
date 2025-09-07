<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConvertEmptyStringsToNull
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $input = $request->all();
        $input = $this->convertEmptyStringsToNull($input);
        $request->merge($input);

        return $next($request);
    }

    private function convertEmptyStringsToNull($input)
    {
        foreach ($input as $key => $value) {
            if (is_string($value) && $value === '') {
                $input[$key] = null;
            } elseif (is_array($value)) {
                $input[$key] = $this->convertEmptyStringsToNull($value);
            }
        }

        return $input;
    }
}
