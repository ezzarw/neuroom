<?php

namespace App\Http\Controllers;

use App\Services\PomodoroService;
use Illuminate\Http\Request;

class PomodoroController extends Controller
{
    public function start(Request $request, PomodoroService $service)
    {
        return response()->json(
            $service->start(
                user: $request->user(),
                duration: $request->integer('duration', 1500),
                type: 'focus',
                autoStartBreak: $request->boolean('auto_start_break', true),
                autoStartFocus: $request->boolean('auto_start_focus', false),
            )
        );
    }

    public function startBreak(Request $request, PomodoroService $service)
    {
        return response()->json(
            $service->startBreak(
                user: $request->user(),
                duration: $request->integer('duration', 300),
                autoStartBreak: $request->boolean('auto_start_break', true),
                autoStartFocus: $request->boolean('auto_start_focus', false),
            )
        );
    }

    public function pause(Request $request, PomodoroService $service)
    {
        return response()->json($service->pause($request->user()));
    }

    public function resume(Request $request, PomodoroService $service)
    {
        return response()->json($service->resume($request->user()));
    }

    public function stop(Request $request, PomodoroService $service)
    {
        return response()->json($service->stop($request->user()));
    }

    public function finish(Request $request, PomodoroService $service)
    {
        return response()->json($service->finish($request->user()));
    }

    public function current(Request $request, PomodoroService $service)
    {
        return response()->json($service->current($request->user()));
    }
}
