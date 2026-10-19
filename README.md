# OrderDesk

A small AI-assisted PHP learning project for tracking customer orders.
Created as a new learning sample, not a record of previous employment or independently authored work.

## Features

- Create orders with customer, product, quantity and unit price.
- Update order status: New, Processing, Shipped, Cancelled.
- Search customer/product and filter by status.
- Display total order count and non-cancelled order value.
- Persist data with PDO SQLite; no external packages or build step.
- Server-side validation, prepared statements, HTML escaping and CSRF tokens.
- Store prices as integer cents rather than floating-point numbers.

## Run locally

Requires 64-bit PHP 8.1+ with PDO and PDO SQLite enabled. No Composer needed.
From the extracted `orderdesk` directory:

```sh
php -m
php -S 127.0.0.1:8000 -t public
```

Open http://127.0.0.1:8000 . The SQLite database is created automatically in
`storage/`, which must be writable by PHP. Stop with Ctrl+C.
Do not open `index.php` directly in your browser: it needs the PHP server.
Use the `public` directory as the document root so the database is not served.

## Checks

```sh
php -l src/app.php
php -l public/index.php
php tests.php
```

The included checks cover price parsing, invalid prices, HTML escaping and
malformed form fields. They do not cover all HTTP/database behavior.
PHP was unavailable in the generation environment, so runtime execution and
these PHP checks have NOT been performed. Run them locally before submission.

Manual checks:

1. Add a fictional order: Example Customer / Keyboard / 2 / 19.99.
   Expect total EUR 39.98 and order count 1 on a fresh database.
2. Refresh after saving: no duplicate should be created.
3. Set status to Cancelled: count stays 1, non-cancelled value becomes 0.00.
4. Search for Keyboard; then search for a missing product. Check both states.
5. Enter a negative price or zero quantity: validation must reject it.
6. Enter `<script>alert(1)</script>` as a customer: it must display as text.
7. Tamper with the form CSRF token: expect HTTP 403 and no change.
8. Restart the server: saved data should remain.

## Structure

```
public/index.php   Request handling and HTML view
public/style.css   Responsive UI
src/app.php        Database connection, schema and helpers
storage/          Local SQLite data (excluded from version control)
tests.php         Small dependency-free helper tests
GUIDE-SK.md       Slovak walkthrough and learning exercises
```

## Scope and limitations

Local single-user learning demo, NOT a production order system. No login,
permissions, audit trail, tax, shipping or payments. Use fictional data only.
Status changes can move in either direction; transitions are not enforced.
Only the newest 200 filtered orders are displayed; there is no pagination.
SQLite lower() search is primarily ASCII case-insensitive, not full Unicode.
Name length is validated in bytes on the server. Timestamps use UTC.
This project uses SQLite for easy setup, not MySQL or Laravel.

## AI assistance and application use

Initial implementation and documentation were generated with ChatGPT.
Before sharing as a code sample, run it, review it, and make changes you
understand. Describe your actual contributions truthfully; this sample does
not establish a year of PHP experience. Do not claim the code was written
independently. Follow any employer rules on AI-assisted submissions.
No claim of review, testing or modifications by the applicant is made here.

Upload the source files (not the database) to a public GitHub repository to
get a code-review link. The app does not need public hosting for code review.
