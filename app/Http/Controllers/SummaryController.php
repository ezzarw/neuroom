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
            'bahasa' => 'required|string|in:indonesia,english',
        ]);

        $document = $request->file('document');
        $bahasa = $request->string('bahasa')->toString();

        $dt = new DateTime;
        $formatted_date = str_replace([' ', ':', '.', '-'], ['_', '', '', ''], $dt->format('Y-m-d H:i:s.u'));
        $sanitized_name = uniqid().'_'.$formatted_date.'.'.$document->getClientOriginalExtension();
        $stored_path = $document->storeAs('document_for_summaries', $sanitized_name, 'local');

        if ($stored_path === false) {
            return $this->apiError('Gagal menyimpan dokumen.', 500);
        }

        $instruction = $bahasa === 'english'
            ? 'Summarize the uploaded document into concise bullet points. Do not start with phrases like "This document". Go straight to the main ideas and use English. Answer with MarkDown format'
            : 'Buat ringkasan dokumen yang diunggah dalam poin-poin singkat. Jangan mulai dengan kalimat seperti "Dokumen ini". Langsung ke inti topik dan gunakan bahasa Indonesia. jawab dengan format markdown';

        $api_key = env('GEMINI_API_KEY');

        if ($api_key === null || $api_key === '') {
            return $this->apiSuccess(
                'Dokumen berhasil diupload, tetapi integrasi AI belum dikonfigurasi.',
                [
                    'summary' => [
                        'status' => 'fallback',
                        'output' => [
                            'Nama file: '.$document->getClientOriginalName(),
                            'Bahasa ringkasan: '.$bahasa,
                            'Ukuran file: '.$document->getSize().' byte',
                        ],
                    ],
                ],
                200,
                [
                    'fallback' => true,
                ]
            );
        }

        $mimeType = $document->getMimeType() ?: 'application/octet-stream';
        $base64Document = base64_encode(file_get_contents($document->getRealPath()));

        $response = Http::timeout(90)->withHeaders([
            'Accept' => 'application/json',
            'x-goog-api-key' => $api_key,
        ])->post('https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent', [
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

        $output = collect(preg_split('/\r?\n/', trim($text)))
            ->map(fn (?string $line) => trim((string) $line))
            ->filter()
            ->values()
            ->all();

        return $this->apiSuccess('Ringkasan berhasil dibuat.', [
            'summary' => [
                'status' => 'success',
                'output' => $output,
                'document' => [
                    'name' => $document->getClientOriginalName(),
                    'path' => $stored_path,
                    'mime_type' => $mimeType,
                ],
            ],
        ]);
    }
}
