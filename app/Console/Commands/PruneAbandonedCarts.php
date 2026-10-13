<?php

namespace App\Console\Commands;

use App\Models\Cart;
use Illuminate\Console\Command;

class PruneAbandonedCarts extends Command
{
    protected $signature = 'carts:prune-abandoned {--days=30}';

    protected $description = 'Delete guest carts that have not been updated within the given number of days';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $deleted = Cart::query()
            ->whereNull('user_id')
            ->where('updated_at', '<', now()->subDays($days))
            ->delete();

        $this->info("Pruned {$deleted} abandoned guest cart(s).");

        return self::SUCCESS;
    }
}
