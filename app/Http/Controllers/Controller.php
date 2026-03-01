<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function resolveGoBinary(string $binaryName): string
    {
        $extension = DIRECTORY_SEPARATOR === '\\' ? '.exe' : '';
        $platformPath = base_path("go/bin/{$binaryName}{$extension}");
        if (file_exists($platformPath)) {
            return $platformPath;
        }

        // Fallback untuk environment yang belum punya suffix executable sesuai OS.
        return base_path("go/bin/{$binaryName}");
    }
}
