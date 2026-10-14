<?php

namespace App\Support;

class PublicPath
{
    public static function url(?string $path): string
    {
        $path = trim((string) $path);

        if ($path === '') {
            return asset('images/placeholders/scrub.svg');
        }

        if (preg_match('#^(https?:)?//#', $path) === 1) {
            return $path;
        }

        return asset(ltrim($path, '/'));
    }
}
