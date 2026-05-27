<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreSummaryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'document' => [
                'required',
                // Validasi tipe file asli secara ketat via magic bytes
                File::types(['pdf', 'txt', 'png', 'jpg', 'jpeg', 'webp', 'heic', 'heif'])
                    ->max('50mb'), // Batas atas global untuk PDF
                    
                // Custom closure rule untuk pengecekan ketat anti-injeksi
                function ($attribute, $value, $fail) {
                    $file = $this->file($attribute);
                    if (!$file) return;

                    $mime = $file->getMimeType();
                    $sizeInMb = $file->getSize() / 1024 / 1024;

                    // Jika input ternyata adalah file gambar
                    if (str_starts_with($mime, 'image/')) {
                        // Batasi gambar maksimal 7MB sesuai limit Gemini
                        if ($sizeInMb > 7) {
                            $fail('Ukuran file gambar tidak boleh lebih dari 7MB.');
                        }
                        
                        // Deteksi payload PHP/Script yang menyamar di dalam gambar
                        if (@getimagesize($file->getRealPath()) === false && !in_array($mime, ['image/heic', 'image/heif'])) {
                            $fail('Struktur file gambar tidak valid (terindikasi berbahaya).');
                        }
                    }
                },
            ],
            'bahasa' => 'required|string|in:indonesia,english',
        ];
    }
}
