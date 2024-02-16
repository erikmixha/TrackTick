<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckToken
{
    public function handle(Request $request, Closure $next)
    {
        if (!$request->bearerToken()) {
            return response()->json(['error' => 'Unauthorized. Missing token.'], 401);
        }

        $user = Auth::user();

        if (!$user) {
            return response()->json(['error' => 'Unauthorized. Invalid token.'], 401);
        }

        $providerId = $user->currentAccessToken()->claims()->get('provider_id');

        if (!$providerId) {
            return response()->json(['error' => 'Unauthorized. Provider ID not found in token.'], 401);
        }

        $request->merge(['provider_id' => $providerId]);

        return $next($request);
    }
}
