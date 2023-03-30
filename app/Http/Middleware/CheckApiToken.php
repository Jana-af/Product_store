<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckApiToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!in_array($request->headers->get('accept'), ['application/json', 'Application/Json'])){
         return response()->json(['message' => 'Unauthenticated.'], 401);
        }
         return $next($request);
    }
}
