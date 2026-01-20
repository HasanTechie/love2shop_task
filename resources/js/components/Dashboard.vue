<template>
    <div class="max-w-6xl mx-auto p-6">
        <h1 class="text-2xl mb-4">Orders Dashboard</h1>

        <div class="mb-4 flex flex-wrap gap-4 items-end">
            <div>
                <label class="mr-2">Status:</label>
                <select v-model="statusFilter" class="border rounded px-2 py-1">
                    <option value="">All</option>
                    <option v-for="status in STATUSES" :key="status" :value="status">
                        {{ status }}
                    </option>
                </select>
            </div>

            <div class="ml-auto flex items-center gap-2">
                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="loading || page <= 1"
                    @click="page--"
                >
                    Prev
                </button>

                <div class="text-sm">
                    Page <strong>{{ page }}</strong>
                    <span v-if="lastPage">/ {{ lastPage }}</span>
                </div>

                <button
                    class="px-3 py-1 border rounded disabled:opacity-50"
                    :disabled="loading || (lastPage && page >= lastPage)"
                    @click="page++"
                >
                    Next
                </button>
            </div>
        </div>

        <div v-if="error" class="mb-4 p-3 border rounded text-sm border-red-300 bg-red-50 text-red-700">
            {{ error }}
        </div>

        <div v-if="loading" class="p-4 text-gray-600">Loading…</div>

        <div v-else-if="orders.length === 0" class="p-4 text-gray-600">
            No orders found.
        </div>

        <div v-else class="overflow-x-auto bg-white border rounded">
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

                    <td class="px-4 py-3">
                        <select
                            class="border rounded px-2 py-1"
                            :disabled="isUpdating(order.id)"
                            :value="order.status"
                            @change="onStatusChange(order, $event)"
                        >
                            <option v-for="status in STATUSES" :key="status" :value="status">
                                {{ status }}
                            </option>
                        </select>

                        <div v-if="isUpdating(order.id)" class="text-xs text-gray-500 mt-1">
                            Updating…
                        </div>
                    </td>

                    <td class="px-4 py-3">{{ formatDate(order.created_at) }}</td>
                    <td class="px-4 py-3">{{ formatDate(order.updated_at) }}</td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from "vue";

// Statuses can be imported from Models\Order or database to keep single source of truth.
const STATUSES = ["pending", "processing", "shipped", "delivered", "cancelled"];

const orders = ref([]);
const meta = ref(null);

const loading = ref(false);
const error = ref("");

const page = ref(1);
const statusFilter = ref("");

const updating = reactive({});
let abort = null;

const lastPage = computed(() => meta.value?.last_page ?? null);

const isUpdating = (id) => !!updating[id];
const formatDate = (value) => (value ? new Date(value).toLocaleString() : "—");

function buildOrdersUrl() {
    const params = new URLSearchParams();
    if (statusFilter.value) params.set("status", statusFilter.value);
    params.set("page", String(page.value));
    return `/api/orders?${params.toString()}`;
}

async function readJson(res) {
    try {
        return await res.json();
    } catch {
        return null;
    }
}

async function fetchOrders() {
    if (abort) abort.abort();
    abort = new AbortController();

    loading.value = true;
    error.value = "";

    try {
        const res = await fetch(buildOrdersUrl(), {
            headers: { Accept: "application/json" },
            signal: abort.signal,
        });

        if (!res.ok) {
            error.value = `Failed to load orders (${res.status})`;
            orders.value = [];
            meta.value = null;
            return;
        }

        const json = await readJson(res);
        orders.value = json?.data ?? [];
        meta.value = json?.meta ?? null;

        // Clamp page to valid range
        if (lastPage.value && page.value > lastPage.value) page.value = lastPage.value;
        if (page.value < 1) page.value = 1;
    } catch (e) {
        if (e?.name === "AbortError") return;
        error.value = "Network error while loading orders. Please try again.";
        orders.value = [];
        meta.value = null;
    } finally {
        if (!abort?.signal?.aborted) loading.value = false;
    }
}

async function updateStatus(order, newStatus) {
    const prev = order.status;
    if (newStatus === prev) return;

    order.status = newStatus;
    updating[order.id] = true;
    error.value = "";

    try {
        const res = await fetch(`/api/orders/${order.id}`, {
            method: "PATCH",
            headers: { Accept: "application/json", "Content-Type": "application/json" },
            body: JSON.stringify({ status: newStatus }),
        });

        const payload = await readJson(res);

        if (!res.ok) {
            order.status = prev;
            error.value =
                payload?.message ||
                payload?.errors?.status?.[0] ||
                `Failed to update (${res.status})`;
            return;
        }

        const updated = payload?.data;
        if (updated) Object.assign(order, updated);
    } catch {
        order.status = prev;
        error.value = "Network error while updating order. Please try again.";
    } finally {
        updating[order.id] = false;
    }
}

function onStatusChange(order, event) {
    updateStatus(order, event.target.value);
}

watch(statusFilter, () => {
    page.value = 1;
    fetchOrders();
});

watch(page, () => {
    fetchOrders();
});

onMounted(fetchOrders);
</script>
