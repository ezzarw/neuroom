<?php

namespace App\Http\Controllers;

use Symfony\Component\Process\Process;

abstract class Controller
{
    protected function resolveGoBinary(string $binaryName): string
    {
        $extension = DIRECTORY_SEPARATOR === '\\' ? '.exe' : '';
        $platformPath = base_path("go/bin/{$binaryName}{$extension}");
        if (file_exists($platformPath)) {
            return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $platformPath);
        }

        return str_replace(['/', '\\'], DIRECTORY_SEPARATOR, base_path("go/bin/{$binaryName}"));
    }

    protected function goProcess(array $command): Process
    {
        return new Process($command, base_path(), [
            'DB_HOST' => (string) config('database.connections.mysql.host', '127.0.0.1'),
            'DB_PORT' => (string) config('database.connections.mysql.port', '3306'),
            'DB_DATABASE' => (string) config('database.connections.mysql.database', 'neuroom_db'),
            'DB_USERNAME' => (string) config('database.connections.mysql.username', 'root'),
            'DB_PASSWORD' => (string) config('database.connections.mysql.password', ''),
        ]);
    }
}
