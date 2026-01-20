<?php

namespace App\Services\Orders;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

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

    public function updateStatus(Order $order, string $status): Order
    {
        if ($order->status === Order::STATUS_CANCELLED && $status !== Order::STATUS_CANCELLED) {
            throw ValidationException::withMessages([
                'status' => 'Cancelled orders cannot be changed.',
            ]);
        }

        if ($order->status === Order::STATUS_DELIVERED && $status !== Order::STATUS_DELIVERED) {
            throw ValidationException::withMessages([
                'status' => 'Delivered orders cannot be changed.',
            ]);
        }

        $order->update(['status' => $status]);

        return $order;
    }
}
