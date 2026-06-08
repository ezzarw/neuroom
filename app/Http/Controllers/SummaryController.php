<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSummaryRequest;
use App\Models\Note;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redis;

class SummaryController extends Controller
{
    public function store(StoreSummaryRequest $request)
    {

        $document = $request->file('document');
        $bahasa = $request->string('bahasa')->toString();

        $instruction = $bahasa === 'english'
            ? 'Summarize the uploaded document into concise bullet points. Do not start with phrases like "This document". Go straight to the main ideas and use English. Answer with MarkDown format'
            : 'Buat ringkasan dokumen yang diunggah dalam poin-poin singkat. Jangan mulai dengan kalimat seperti "Dokumen ini". Langsung ke inti topik dan gunakan bahasa Indonesia. jawab dengan format markdown';

        $api_key = env('GEMINI_API_KEY');
        $model = env('GEMINI_MODEL');

        $mimeType = $document->getMimeType() ?: 'application/octet-stream';
        $base64Document = base64_encode(file_get_contents($document->getRealPath()));

        $response = Http::timeout(90)->withHeaders([
            'Accept' => 'application/json',
            'x-goog-api-key' => $api_key,
        ])->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        ['text' => $instruction],
                        [
                            'inline_data' => [
                                'mime_type' => $mimeType,
                                'data' => $base64Document,
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        if (! $response->successful()) {
            logger()->error('gemini summary gagal', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return $this->apiError('Gagal membuat ringkasan dari layanan AI.', 502);
        }

        $data = $response->json();
        $text = data_get($data, 'candidates.0.content.parts.0.text');

        if (! is_string($text) || trim($text) === '') {
            logger()->warning('respons gemini tidak berisi text', ['data' => $data]);

            return $this->apiError('Respons AI tidak valid.', 502);
        }

        $output = $data['candidates'][0]['content']['parts'][0]['text'];
        $user_id = Auth::id();
        Redis::set("user:$user_id:summary", $output);
        
        ActivityLogger::log($user_id, 'generate_summary', "User membuat ringkasan materi: {$document->getClientOriginalName()}");

        return $this->apiSuccess('Ringkasan berhasil dibuat.', [
            'status' => 'success',
            'output' => $output,
            'document' => [
                'name' => $document->getClientOriginalName(),
                'mime_type' => $mimeType,
            ],
        ], 201);
    }

    public function addToNotes()
    {
        $user_id = Auth::id();
        $summary_result = Redis::get("user:$user_id:summary");

        if (empty($summary_result) || $summary_result == false) {
            return $this->apiError('Hasil rangkuman tidak ditemukan, mungkin sudah terunggah ke catatan, atau belum membuat rangkuman', 404);
        } 

        $payload = [
            'user_id' => Auth::id(),
            'title' => 'Catatan dari Rangkuman',
            'content' => $summary_result
        ];
        $note = Note::query()->create($payload);
        $payload = [
            'id' => $note->id,
            'user_id' => $note->user_id,
            'title' => $note->title,
            'content' => $note->content,
            'created_at' => $note->created_at,
            'updated_at' => $note->updated_at

        ];
        // delete hasil rangkuman terbaru (soalnya udah terupload agar data tidak double)
        Redis::del("user:$user_id:summary");

        ActivityLogger::log($user_id, 'save_summary_to_note', "User menyimpan ringkasan materi ke catatan");

        return $this->apiSuccess('Rangkuman berhasil diupload ke catatan.', 
            $payload
        , 201);
    }
}
