<?php
declare(strict_types=1);

namespace App\Util;

class Config
{
    private function __construct(private int $width, private int $height, private int $fps)
    {
    }

    public static function by(int $width, int $height, int $fps): self
    {
        return new self($width, $height, $fps);
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    public function getHeight(): int
    {
        return $this->height;
    }

    public function getFps(): int
    {
        return $this->fps;
    }

    public function setWidth(int $width): void
    {
        $this->width = $width;
    }

    public function setHeight(int $height): void
    {
        $this->height = $height;
    }

    public function setFps(int $fps): void
    {
        $this->fps = $fps;
    }


    public function getFpsAsTrigger(): string
    {
        if ($this->fps < 1) {
            return 'load';
        }

        $seconds = 1 / $this->fps;
        return 'every ' . $seconds . 's';
    }
}
