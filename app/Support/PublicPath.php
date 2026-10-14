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

    public static function baseFromRoots(string $documentRoot, string $publicPath): string
    {
        $documentRoot = rtrim(str_replace('\\', '/', $documentRoot), '/');
        $publicPath = rtrim(str_replace('\\', '/', $publicPath), '/');

        if ($documentRoot === '' || $publicPath === '') {
            return '';
        }

        if (! str_starts_with(strtolower($publicPath), strtolower($documentRoot))) {
            return '';
        }

        $base = substr($publicPath, strlen($documentRoot));

        return ($base === '' || $base === '/') ? '' : rtrim($base, '/');
    }
}
