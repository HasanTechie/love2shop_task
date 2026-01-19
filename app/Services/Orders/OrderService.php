<?php

namespace App\Services\Orders;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderService
{
    public function paginate(array $filters): LengthAwarePaginator
    {
        $perPage = $filters['per_page'] ?? 20;

        $q = Order::query()
            ->with('customer')
            ->latest()
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('status', $status));

        return $q->paginate($perPage)->withQueryString();
    }
}
