<?php

declare(strict_types=1);

namespace Calcine\Services\ContentAdaptors;

class FileContentAdaptor implements ContentAdaptorInterface
{
    public function __construct(private string $rootPath)
    {
    }

    public function loadContent(): array
    {
        $files = $this->loadFiles();
        return iterator_to_array($files);
    }

    private function loadFiles(): \Generator
    {
        $dir = new \DirectoryIterator($this->rootPath);

        foreach ($dir as $fileInfo) {
            if ($fileInfo->isDot() || $fileInfo->isDir()) {
                continue;
            }

            if ($fileInfo->getExtension() !== 'markdown') {
                continue;
            }

            yield $this->loadFile($fileInfo->getPathname());
        }
    }

    private function loadFile(string $path): array
    {
        $data = [];

        if (! ($file = fopen($path, 'r'))) {
            throw new ContentAdaptorException("Cannot open $path");
        }

        $basepath = basename($path);

        while (! feof($file)) {
            $line = fgets($file);

            if ($line === false) {
                continue;
            }

            $line = trim($line);

            // Skip blank or comment lines.
            if (! $line || $line[0] == ';') {
                continue;
            }

            // Parse/validate this header line.
            if (! preg_match('/^([a-zA-Z]+): *(.*)$/', $line, $matches)) {
                throw new ContentAdaptorException("Invalid header line in $basepath: '$line'.");
            }

            $name = strtolower($matches[1]);
            $value = trim($matches[2]);

            if ($name === 'body') {
                break;
            } elseif ($name === 'tags') {
                $value = array_map('trim', explode(',', $value));
            }

            $data[$name] = $value;
        }

        $body = '';
        while (! feof($file)) {
            $body .= fread($file, 1024);
        }
        $data['body'] = trim($body);

        return $data;
    }
}
