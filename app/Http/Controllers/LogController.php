<?php

namespace App\Http\Controllers;

class LogController extends Controller
{
    private const MAX_LINES = 1000;

    private function getLogPath(): string
    {
        return storage_path('logs/laravel.log');
    }

    public function index()
    {
        $path = $this->getLogPath();
        if (!file_exists($path)) {
            return response()->json(['error' => 'Log file not found', 'path' => $path], 404);
        }

        // Ambil hingga 1000 baris terakhir
        $lines = $this->tailFile($path, self::MAX_LINES);

        // Pola Monolog Laravel: [YYYY-MM-DD HH:MM:SS] channel.LEVEL: message
        $pattern = '/^\s*\[(?<time>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\s+(?<channel>.+?)\.(?<type>[A-Za-z]+):\s*(?<message>.*)$/';

        $logs = [];
        $currentLog = null;
        $skipStack = false;

        foreach ($lines as $entry) {
            $entry = rtrim($entry, "\r\n");
            if ($entry === '') continue;

            if (preg_match($pattern, $entry, $m)) {
                if (!empty($currentLog)) {
                    $currentLog['message'] = $this->cutStacktrace($currentLog['message']);
                    $logs[] = $currentLog;
                }
                $skipStack = false;
                $currentLog = [
                    'time'    => $m['time'],
                    'type'    => strtoupper($m['type']),
                    'message' => $m['message'],
                    // 'channel' => $m['channel'], // aktifkan bila perlu
                ];
            } else {
                if (!empty($currentLog)) {
                    if ($this->isStacktraceStart($entry)) {
                        $skipStack = true;
                        continue;
                    }
                    if ($skipStack || $this->isStacktraceLine($entry)) continue;
                    $currentLog['message'] .= "\n" . $entry;
                }
            }
        }

        if (!empty($currentLog)) {
            $currentLog['message'] = $this->cutStacktrace($currentLog['message']);
            $logs[] = $currentLog;
        }

        // Terbaru → terlama
        $logs = array_reverse($logs);

        // Hanya field yang dibutuhkan
        return response()->json(array_map(static function ($e) {
            return ['time' => $e['time'], 'type' => $e['type'], 'message' => $e['message']];
        }, $logs));
    }

    private function tailFile(string $filepath, int $linesCount = self::MAX_LINES, int $bufferSize = 8192): array
    {
        $f = @fopen($filepath, 'rb');
        if ($f === false) return [];

        $stat = fstat($f);
        $fileSize = $stat['size'] ?? 0;
        if ($fileSize === 0) {
            fclose($f);
            return [];
        }

        $data = '';
        $linesFound = 0;
        $position = $fileSize;

        while ($position > 0 && $linesFound <= $linesCount) {
            $readSize = ($position - $bufferSize) >= 0 ? $bufferSize : $position;
            $position -= $readSize;
            fseek($f, $position);
            $chunk = fread($f, $readSize);
            $data = $chunk . $data;
            $linesFound += substr_count($chunk, "\n");
        }
        fclose($f);

        $allLines = preg_split("/\r\n|\n|\r/", $data) ?: [];
        if (count($allLines) > $linesCount) {
            $allLines = array_slice($allLines, -$linesCount);
        }
        return $allLines;
    }

    private function cutStacktrace(string $text): string
    {
        $patterns = [
            '/\n\[(?i:stacktrace)\].*/s',
            '/\nStack trace:.*/is',
        ];
        return preg_replace($patterns, '', $text) ?? $text;
    }

    private function isStacktraceStart(string $line): bool
    {
        $trim = trim($line);
        return preg_match('/^\[(?i:stacktrace)\]$/', $trim) === 1
            || preg_match('/^Stack trace:/i', $trim) === 1;
    }

    private function isStacktraceLine(string $line): bool
    {
        $trim = ltrim($line);
        if ($trim === '') return false;
        return preg_match('/^#\d+\s/', $trim) === 1
            || preg_match('/^(Next|Previous)\s+/i', $trim) === 1;
    }
}
