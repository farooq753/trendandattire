<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        return $user->id === $order->user_id || $user->hasPermission('orders.view');
    }

    public function manage(User $user, Order $order): bool
    {
        return $user->hasPermission('orders.manage');
    }
}
