<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->web(append: [
            HandleInertiaRequests::class,
        ]);

        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // API Standards (pamora-foundation/04-engineering/07-api-specification.md) mandate a
        // {success, error: {code, message}} envelope and forbid leaking stack traces. Laravel's
        // default JSON error shape ({message, errors}) doesn't match, so every exception type
        // reachable from /api/* is normalized here. Web/Inertia requests are untouched — each
        // callback returns null for non-API requests, falling through to default handling.
        $exceptions->render(function (ValidationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                    'message' => 'Validation failed.',
                    'fields' => $e->errors(),
                ],
            ], $e->status);
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => ['code' => 'UNAUTHENTICATED', 'message' => 'Unauthenticated.'],
            ], 401);
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage() ?: 'This action is unauthorized.'],
            ], 403);
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => ['code' => 'FORBIDDEN', 'message' => $e->getMessage() ?: 'This action is unauthorized.'],
            ], 403);
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Resource not found.'],
            ], 404);
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Resource not found.'],
            ], 404);
        });

        $exceptions->render(function (ThrottleRequestsException $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            return response()->json([
                'success' => false,
                'error' => ['code' => 'TOO_MANY_REQUESTS', 'message' => 'Too many requests.'],
            ], 429);
        });

        $exceptions->render(function (Throwable $e, Request $request) {
            if (! $request->is('api/*')) {
                return null;
            }

            $status = $e instanceof HttpExceptionInterface ? $e->getStatusCode() : 500;

            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $status === 500 ? 'SERVER_ERROR' : 'HTTP_ERROR',
                    'message' => $status === 500 ? 'Something went wrong.' : $e->getMessage(),
                ],
            ], $status);
        });
    })
    ->withSchedule(function (Schedule $schedule): void {
        $schedule->command('reminders:dispatch')->everyFiveMinutes();
    })
    ->create();
