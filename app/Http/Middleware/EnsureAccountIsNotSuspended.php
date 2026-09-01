<?php

namespace App\Http\Middleware;

use App\Services\AccountSuspensionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAccountIsNotSuspended
{
    public function __construct(private AccountSuspensionService $accountSuspensionService) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->accountSuspensionService->findSuspendedUserByToken($request->bearerToken());

        if ($user) {
            return $this->accountSuspensionService->response($user);
        }

        return $next($request);
    }
}
