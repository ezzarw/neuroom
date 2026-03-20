<?php

namespace App\Http\Controllers;

use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SummaryController extends Controller
{
    public function summary(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,ppt,pptx,doc,docx,xls,xlsx,txt,csv,rtf,odt,ods,odp|max:20480',
            'bahasa' => 'required|string', // bahasa harus indonesia kalo gak english
        ]);

        $document = $request->file('document');
        $bahasa = $request->bahasa;
        $bahasa_list = ['indonesia', 'english'];
        if ($bahasa == $bahasa_list[0]) {
            $bahasa = 'pakai bahasa indonesia';
        } elseif ($bahasa == $bahasa_list[1]) {
            $bahasa = 'use english language';
        } else {
            return back()
                ->withInput()
                ->withErrors(['bahasa' => 'Bahasa harus indonesia atau english.']);
        }
        // ada $bahasa dan ada $mode belajar

        // format nama file biar unik
        $dt = new DateTime;
        $formatted_date = str_replace([' ', ':', '.', '-'], ['_', '', '', ''], $dt->format('Y-m-d H:i:s.u'));
        $sanitized_name = uniqid().'_'.$formatted_date.'.'.$document->getClientOriginalExtension();
        $stored_path = $document->storeAs('document_for_summaries', $sanitized_name, 'local');
        if ($stored_path === false) {
            return back()
                ->withInput()
                ->withErrors(['document' => 'Gagal menyimpan dokumen']);
        }

        // bersihin text biar lebih aman
        $instruction = "Buat ringkasan poin-poin penting dari ". strtolower($document->getClientOriginalExtension()) . " tapi ingat, jangan pake seperti 'Dokumen ini... atau " . strtolower($document->getClientOriginalExtension()) . " ini... tapi langsung ke topik' yang aku kirim ini, $bahasa";
        $mode = 'summarize';
        $max_output_tokens = 1200;

        $payload = [
            'task' => $mode,
            'instruction' => $instruction,
            'document' => [
                'original_name' => $document->getClientOriginalName(),
                'stored_name' => $sanitized_name,
                'extension' => strtolower($document->getClientOriginalExtension()),
                'mime_type' => $document->getMimeType(),
                'size' => $document->getSize(),
                'stored_path' => $stored_path,
            ],
            'parameters' => [
                'max_output_tokens' => $max_output_tokens,
            ],
        ];

        $backend_url = trim((string) env('AI_BACKEND_URL'));

        if ($backend_url === '') {
            // Frontend final belum disepakati, jadi backend mengembalikan
            // flash session generik agar nanti bisa dipakai dari halaman mana pun.
            return back()
                ->withInput()
                ->with('success', 'Dokumen berhasil diproses.')
                ->with('summary_result', [
                    'message' => 'AI backend endpoint belum dikonfigurasi',
                    'status' => 'not_configured',
                    'output' => null,
                ]);
        }

        try {
            $response = Http::timeout((int) env('AI_BACKEND_TIMEOUT'))
                ->acceptJson()
                ->attach(
                    'document_file',
                    file_get_contents($document->getRealPath()),
                    $sanitized_name,
                    ['Content-Type' => $document->getMimeType()]
                )
                ->post($backend_url, [
                    'task' => $payload['task'],
                    'instruction' => $payload['instruction'],
                    'document' => json_encode($payload['document']),
                    'parameters' => json_encode($payload['parameters']),
                ]);

            if (! $response->successful()) {
                logger()->warning('AI backend request gagal', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return back()
                    ->withInput()
                    ->withErrors(['summary' => 'AI backend mengembalikan error.']);
            }

            $parsed = $response->json();
            $output = $parsed['output'] ?? null;

            return back()
                ->withInput()
                ->with('success', 'Dokumen berhasil diproses.')
                ->with('summary_result', [
                    'message' => 'AI backend berhasil dipanggil',
                    'status' => 'success',
                    'output' => $output,
                ]);
        } catch (\Throwable $e) {
            logger()->error('AI backend tidak bisa diakses', [
                'message' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['summary' => 'AI backend tidak bisa diakses']);
        }
    }
}
