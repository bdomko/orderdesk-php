# OrderDesk

A small PHP and SQLite application for tracking customer orders. It provides a simple interface for creating orders, updating their status, and finding records by customer or product.

## Features

- Create orders with a customer name, product, quantity, and unit price.
- Change order status: New, Processing, Shipped, or Cancelled.
- Search by customer or product and filter by status.
- View the total number of orders and the combined value of non-cancelled orders.
- Store records in a local SQLite database.
- Use a responsive interface built with HTML and CSS.

## Technology

- PHP 8.1+ recommended, with PDO and PDO SQLite enabled
- SQLite
- HTML and CSS
- No Composer dependencies or frontend build tools required

This implementation uses SQLite, not MySQL. The database is created automatically on first use.

## Run locally

Download or clone this repository and open a terminal in its root directory.
The `storage` directory must exist and be writable by PHP.

If PHP is available on your system PATH:

```sh
php -S 127.0.0.1:8000 -t public
```

On Windows with XAMPP installed at `C:\xampp`, use Command Prompt:

```bat
C:\xampp\php\php.exe -S 127.0.0.1:8000 -t public
```

Open http://127.0.0.1:8000 in your browser. Keep the terminal open while using the application. Press Ctrl+C to stop the server.

Apache and MySQL do not need to be started in the XAMPP control panel for this setup.
Always use `public` as the document root so database files remain outside the served directory.

If the application reports that the database is unavailable, check that PDO SQLite is enabled in your PHP configuration and that `storage` is writable.

## Project structure

| Path | Purpose |
| --- | --- |
| `public/index.php` | HTTP request handling, forms, filtering, and the HTML interface |
| `public/style.css` | Layout, colours, and responsive styles |
| `src/app.php` | Database connection, table creation, and input/output helpers |
| `storage/.gitkeep` | Keeps the database directory in the repository |
| `tests.php` | Dependency-free helper checks |
| `.gitignore` | Excludes local database files and environment configuration from Git |

The generated database is stored at `storage/orders.sqlite` and should not be committed.

## Implementation details

- PDO prepared statements separate SQL commands from user-supplied values.
- Server-side validation checks required fields, quantities, prices, and status values.
- HTML escaping ensures user-supplied content is displayed as text.
- Session-based CSRF tokens protect form submissions.
- Successful POST requests redirect to a GET request to avoid duplicate submissions when refreshing.
- Prices are stored as integer cents to avoid floating-point errors in monetary calculations.
- Cancelled orders remain in the order count but are excluded from the combined order value.

## Testing

Run the helper checks from the repository root:

```sh
php tests.php
```

With XAMPP on Windows Command Prompt:

```bat
C:\xampp\php\php.exe tests.php
```

The 13 checks cover valid and invalid price inputs, HTML escaping, and malformed form fields.

### Verification completed

- All 13 helper checks passed locally on Windows using PHP 8.0.30.
- The application and its stylesheet loaded successfully.
- Manual order creation was checked with two items priced at EUR 19.99 each, producing a total of EUR 39.98.
- The applicant reported that the cancellation check behaved as expected: the order count remained unchanged and the non-cancelled value decreased to EUR 0.00.

This is limited smoke testing, not a comprehensive automated integration or security test suite. PHP 8.1+ is the recommended baseline; the recorded local smoke test used PHP 8.0.30.

## Limitations

This is a local, single-user learning demo, not a production order-management system.

- No authentication, user roles, or audit trail.
- No payments, taxes, shipping integration, or stock management.
- Use fictional data only and do not expose this version as a public service.
- Status transitions are unrestricted.
- The list shows up to 200 newest matching orders; pagination is not implemented.
- Summary totals cover all orders, regardless of the active filters.
- Case-insensitive search uses SQLite's built-in `lower()`, which does not provide full Unicode case folding.
- Timestamps use UTC; customer and product length limits are checked in bytes.

## Development notes

The initial implementation and documentation were generated with assistance from ChatGPT. The applicant set up the application locally, ran the included helper checks, and performed the manual checks described above. This repository is shared as an AI-assisted learning sample, not as independently authored professional work.
