<?php

namespace Calcine\Commands;

use DateTime;

use Bramus\Ansi\Ansi;
use Bramus\Ansi\ControlSequences\EscapeSequences\Enums\SGR;

use Calcine\Path;
use Calcine\Template\Engine\EngineFactory;

class NewPostCommand extends BaseCommand
{
    public function execute(array $args, Ansi $ansi)
    {
        $filename = !empty($args[0]) ? $args[0] : '';
        if (!$filename) {
            throw new \Exception('You must specify a filename when creating a new post');
        }

        $filename = preg_replace('/[^a-z0-9-]/i', '-', $filename);
        $filename = preg_replace('/-+/', '-', $filename);
        $filename = strtolower($filename);

        $datetime = new DateTime();
        $date = $datetime->format('Y-m-d');
        $now = $datetime->format('Y-m-d H:i:s');

        $template = <<<EOF
; Comments are not output to the generated site, you can use them
; to leave notes to yourself, list sources, anything. They just need
; to start with a semi-colon.

Title:
Tags: 
Slug: $filename
Date: $now

Body:

Enter your blog post text here
EOF;

        $postsPath = $this->config->get('posts.path');

        $dir = Path::join(CALCINE_ROOT, $postsPath);
        if (!is_dir($dir)) {
            if (!mkdir($dir, 0755, true)) {
                throw new \Exception('Cannot create ' . $dir);
            }
        }

        $ext = EngineFactory::createInstance($this->config->get('posts.format'))->getExtension();
        $postName = sprintf('%s-%s.%s', $date, $filename, $ext);
        $postPath = Path::join(CALCINE_ROOT, $postsPath, $postName);

        if (file_exists($postPath)) {
            throw new \Exception(sprintf('File \'%s\' already exists', Path::join($postsPath, $postName)));
        }

        file_put_contents($postPath, $template);
        $ansi->color([SGR::COLOR_FG_GREEN]);
        $ansi->text('Created ' . Path::join($postsPath, $postName))->lf();
    }
}
