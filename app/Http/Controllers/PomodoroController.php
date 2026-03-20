<?php

namespace App\Http\Controllers;

use App\Models\PomodoroHistory;
use Illuminate\Http\Request;

class PomodoroController extends Controller
{
    // Method ini disimpan dulu untuk integrasi frontend berikutnya.
    public function post_to_pomodoro_history(Request $request)
    {
        $current_date = now()->toDateString();
        $session = 1;
        $username = $request->user()->username;
        $user_id = $request->user()->id;
        // nanti yang diisi itu username, user_id, session, sama date
        PomodoroHistory::create([
            'username' => $username,
            'user_id' => $user_id,
            'session' => $session,
            'date' => $current_date
        ]);

        return back()
            ->with('success', 'Data pomodoro berhasil ditambahkan.');
    }
    
    // Method ini disimpan dulu untuk integrasi frontend berikutnya.
    public function get_to_pomodoro_history(Request $request)
    {
        $current_date = now()->toDateString();
        $id = $request->user()->id;

        $output = PomodoroHistory::where('user_id', $id)->where('date', $current_date)->count();

        if ($output == 0) {
            return back()
                ->with('error', 'Data pomodoro hari ini tidak ditemukan.');
        }

        return back()
            ->with('success', 'Data pomodoro berhasil diambil.')
            ->with('sesi_per_hari', $output);
    }
}
