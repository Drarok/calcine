<?php declare(strict_types=1);

namespace Calcine\CLI;

use Bramus\Ansi\Ansi;

class AnsiWrapper
{
    private array $styles = [];

    public function __construct(
        private Ansi $ansi
    ) {
    }

    public function pushStyle(array|string $style): self
    {
        $style = is_array($style) ? $style : [$style];
        $this->styles[] = $style;
        $this->ansi->sgr($style);
        return $this;
    }

    public function popStyle(): self
    {
        // Discard current style.
        array_pop($this->styles);

        // We have to reset, else bold etc won't be removed.
        $this->ansi->nostyle();

        // Apply previous style where available.
        if ($this->styles) {
            $idx = count($this->styles) - 1;
            $this->ansi->sgr($this->styles[$idx]);
        }

        return $this;
    }

    public function text(string $text, mixed $style = null): self
    {
        if ($style === null) {
            $this->ansi->text($text);
            return $this;
        }

        $this->pushStyle($style);
        $this->ansi->text($text);
        $this->popStyle();

        return $this;
    }

    public function lf(): self
    {
        $this->ansi->lf();
        return $this;
    }
}
