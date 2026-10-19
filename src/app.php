<?php
declare(strict_types=1);

// Integer cents avoid floating-point rounding errors in money calculations.
function cents(string $value): int {
    if (!preg_match('/\A(0|[1-9][0-9]{0,5})(?:\.([0-9]{1,2}))?\z/', $value, $m)) {
        throw new InvalidArgumentException('Enter a price from 0 to 999999.99, using a decimal point.');
    }
    return (int)$m[1] * 100 + (int)str_pad($m[2] ?? '', 2, '0');
}

function e(string $value): string {
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function field(array $input, string $key): string {
    return isset($input[$key]) && is_string($input[$key]) ? trim($input[$key]) : '';
}

function database(): PDO {
    $db = new PDO('sqlite:' . dirname(__DIR__) . '/storage/orders.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec('PRAGMA busy_timeout = 5000');
    $db->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        customer TEXT NOT NULL,
        product TEXT NOT NULL,
        quantity INTEGER NOT NULL CHECK(quantity BETWEEN 1 AND 1000),
        unit_cents INTEGER NOT NULL CHECK(unit_cents >= 0),
        status TEXT NOT NULL DEFAULT 'New' CHECK(status IN ('New','Processing','Shipped','Cancelled')),
        created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
    )");
    return $db;
}
