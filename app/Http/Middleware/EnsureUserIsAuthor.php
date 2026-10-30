<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The studio belongs to the site author. Readers are sent to their own hub.
 */
class EnsureUserIsAuthor
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()?->isAuthor()) {
            return redirect()
                ->route('reader.index')
                ->with('status', 'The studio is for the author. Your reader area is here.');
        }

        return $next($request);
    }
}
