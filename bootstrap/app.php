<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => '/');
        $middleware->statefulApi();

        $middleware->alias([
            'admin.validate' => \App\Http\Middleware\AdminValidate::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $wantsJson = fn ($request): bool => $request->expectsJson() || $request->is('api/*');

        $jsonError = fn (string $reason, int $status, array $errors = []) => response()->json([
            'success' => false,
            'reason' => $reason,
            'errors' => (object) $errors,
        ], $status);

        $exceptions->render(function (ValidationException $exception, $request) use ($wantsJson, $jsonError) {
            if (! $wantsJson($request)) {
                return null;
            }

            return $jsonError('Validasi gagal.', $exception->status, $exception->errors());
        });

        $exceptions->render(function (AuthenticationException $exception, $request) use ($wantsJson, $jsonError) {
            if (! $wantsJson($request)) {
                return null;
            }

            return $jsonError('Silakan login terlebih dahulu.', Response::HTTP_UNAUTHORIZED);
        });

        $exceptions->render(function (\Throwable $exception, $request) use ($wantsJson, $jsonError) {
            if (! $wantsJson($request)) {
                return null;
            }

            $status = $exception instanceof HttpExceptionInterface
                ? $exception->getStatusCode()
                : Response::HTTP_INTERNAL_SERVER_ERROR;

            $reason = match ($status) {
                Response::HTTP_BAD_REQUEST => 'Request tidak valid.',
                Response::HTTP_UNAUTHORIZED => 'Silakan login terlebih dahulu.',
                Response::HTTP_FORBIDDEN => 'Akses ditolak.',
                Response::HTTP_NOT_FOUND => 'Data atau endpoint tidak ditemukan.',
                Response::HTTP_METHOD_NOT_ALLOWED => 'Method request tidak diizinkan.',
                Response::HTTP_TOO_MANY_REQUESTS => 'Terlalu banyak request. Coba lagi nanti.',
                Response::HTTP_CONFLICT => 'Request konflik dengan kondisi data saat ini.',
                default => $status >= 500
                    ? 'Terjadi kesalahan pada server. ' 
                    : ($exception->getMessage() !== '' ? $exception->getMessage() : 'Request gagal.'),
            };

            return $jsonError($reason, $status);
        });
    })->create();
