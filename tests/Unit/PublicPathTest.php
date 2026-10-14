<?php

namespace Tests\Unit;

use App\Support\PublicPath;
use PHPUnit\Framework\TestCase;

class PublicPathTest extends TestCase
{
    public function test_xampp_subdirectory_is_kept_on_image_urls(): void
    {
        $this->assertSame(
            '/trendandattire/public',
            PublicPath::baseFromRoots('C:\\xampp\\htdocs', 'C:\\xampp\\htdocs\\trendandattire\\public'),
        );
        $this->assertSame(
            '/public',
            PublicPath::baseFromRoots('/var/www/quicksoftsolution.com', '/var/www/quicksoftsolution.com/public'),
        );
        $this->assertSame('', PublicPath::baseFromRoots('/workspace/public', '/workspace/public'));
        $this->assertSame(
            '/trendandattire/public',
            PublicPath::baseFromRoots('c:/xampp/htdocs/', 'C:\\xampp\\htdocs\\trendandattire\\public\\'),
        );
    }
}
