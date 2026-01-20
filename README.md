# Order Fulfillment Dashboard (Laravel + Vue)

Mini order fulfillment system built for the technical test:
- Laravel REST API for listing and updating orders.
- Vue dashboard component to view orders, filter by status, paginate, and update order status
- Delivered and Cancelled order status are immutable.

## Setup & Run

1. Clone the repository
2. composer install
3. npm install
4. Set up your `.env` file and configure your database
5. Run `php artisan migrate --seed`
6. php artisan serve
7. npm run dev

## Assumptions
- Same-origin API calls (no auth).
- Status list is duplicated in Vue for simplicity; ideally comes from backend.
- Seeders create customers + orders.
