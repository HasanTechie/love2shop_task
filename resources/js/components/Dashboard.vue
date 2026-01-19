<template>
    <div class="max-w-6xl mx-auto p-6">
        <h1 class="text-2xl font-semibold mb-4">Orders Dashboard</h1>

        <div class="mb-4 flex gap-4">
            <label class="mr-2">Status:</label>
            <select v-model="status" @change="fetchOrders">
                <option value="">All</option>
                <option value="pending">Pending</option>
                <option value="processing">Processing</option>
                <option value="shipped">Shipped</option>
                <option value="delivered">Delivered</option>
                <option value="cancelled">Cancelled</option>
            </select>
        </div>

        <div v-if="orders.length === 0" class="p-4 text-gray-600">
            No orders found.
        </div>

        <div v-else class="overflow-x-auto bg-white rounded-lg shadow border">
            <table class="min-w-full text-sm text-left">
                <thead class="bg-gray-100 border-b">
                <tr>
                    <th class="px-4 py-3">ID</th>
                    <th class="px-4 py-3">Customer</th>
                    <th class="px-4 py-3">Item</th>
                    <th class="px-4 py-3">Qty</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Created</th>
                    <th class="px-4 py-3">Updated</th>
                </tr>
                </thead>

                <tbody>
                <tr v-for="order in orders" :key="order.id" class="border-b">
                    <td class="px-4 py-3">{{ order.id }}</td>

                    <td class="px-4 py-3">
                        <div>{{ order.customer?.name }}</div>
                        <div class="text-xs">{{ order.customer?.email }}</div>
                    </td>

                    <td class="px-4 py-3">{{ order.item }}</td>
                    <td class="px-4 py-3">{{ order.quantity }}</td>
                    <td class="px-4 py-3">{{ order.status }}</td>
                    <td class="px-4 py-3">{{ formatDate(order.created_at) }}</td>
                    <td class="px-4 py-3">{{ formatDate(order.updated_at) }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from "vue";

const orders = ref([]);
const status = ref("");

function formatDate(value) {
    if (!value) return "—";
    return new Date(value).toLocaleString();
}

async function fetchOrders() {
    try {
        const params = new URLSearchParams();

        if (status.value) params.set("status", status.value);

        const url = `/api/orders?${params.toString()}`;

        const res = await fetch(url, { headers: { Accept: "application/json" } });
        const json = await res.json();

        orders.value = json.data ?? [];
    } catch (e) {
        // console.error(e);
        orders.value = [];
    }
}

onMounted(fetchOrders);
</script>
