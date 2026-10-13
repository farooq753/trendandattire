<?php

namespace App\Console\Commands;

use App\Services\InventoryService;
use Illuminate\Console\Command;

class ReleaseExpiredReservations extends Command
{
    protected $signature = 'inventory:release-expired-reservations';

    protected $description = 'Release ready-made stock reservations that have expired';

    public function handle(InventoryService $inventory): int
    {
        $count = $inventory->releaseExpired();
        $this->info("Released {$count} expired reservation(s).");

        return self::SUCCESS;
    }
}
