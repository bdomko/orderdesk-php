<?php
declare(strict_types=1);
require dirname(__DIR__) . '/src/app.php';
session_start(['cookie_httponly' => true, 'cookie_samesite' => 'Strict', 'use_strict_mode' => true]);
header('X-Content-Type-Options: nosniff');
header("Content-Security-Policy: default-src 'self'; style-src 'self'; form-action 'self'; frame-ancestors 'none'; base-uri 'none'");
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));
$statuses = ['New', 'Processing', 'Shipped', 'Cancelled'];
$error = '';
try {
    $db = database();
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['csrf'], field($_POST, 'csrf'))) {
            http_response_code(403);
            exit('Invalid form token. Refresh the page and try again.');
        }
        try {
            $action = field($_POST, 'action');
            if ($action === 'create') {
                $customer = field($_POST, 'customer');
                $product = field($_POST, 'product');
                if ($customer === '' || $product === '' || strlen($customer) > 120 || strlen($product) > 120) {
                    throw new InvalidArgumentException('Customer and product are required (maximum 120 bytes each).');
                }
                $quantity = filter_var(field($_POST, 'quantity'), FILTER_VALIDATE_INT,
                    ['options' => ['min_range' => 1, 'max_range' => 1000]]);
                if ($quantity === false) throw new InvalidArgumentException('Quantity must be 1 to 1000.');
                $price = cents(field($_POST, 'price'));
                $stmt = $db->prepare('INSERT INTO orders (customer,product,quantity,unit_cents) VALUES (?,?,?,?)');
                $stmt->execute([$customer, $product, $quantity, $price]);
            } elseif ($action === 'status') {
                $status = field($_POST, 'status');
                $id = filter_var(field($_POST, 'id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if (!$id || !in_array($status, $statuses, true)) throw new InvalidArgumentException('Invalid order or status.');
                $stmt = $db->prepare('UPDATE orders SET status = ? WHERE id = ?');
                $stmt->execute([$status, $id]);
                if ($stmt->rowCount() === 0) throw new InvalidArgumentException('Order not found.');
            } else {
                throw new InvalidArgumentException('Unknown action.');
            }
            $_SESSION['notice'] = 'Order saved.';
            header('Location: /', true, 303); // Post/Redirect/Get prevents duplicate form submissions on refresh.
            exit;
        } catch (InvalidArgumentException $ex) {
            http_response_code(422);
            $error = $ex->getMessage();
        }
    }
    $q = substr(field($_GET, 'q'), 0, 120);
    $filter = field($_GET, 'status');
    if (!in_array($filter, $statuses, true)) $filter = '';
    // instr() performs literal substring matching: % and _ are not search wildcards.
    $stmt = $db->prepare("SELECT * FROM orders WHERE
        (instr(lower(customer), lower(?)) > 0 OR instr(lower(product), lower(?)) > 0)
        AND (? = '' OR status = ?) ORDER BY id DESC LIMIT 200");
    $stmt->execute([$q, $q, $filter, $filter]);
    $orders = $stmt->fetchAll();
    $stats = $db->query("SELECT COUNT(*) AS count,
        COALESCE(SUM(CASE WHEN status != 'Cancelled' THEN quantity * unit_cents ELSE 0 END),0) AS value
        FROM orders")->fetch();
} catch (PDOException $ex) {
    error_log($ex->getMessage());
    http_response_code(500);
    exit('Database unavailable. Check PDO SQLite and storage directory permissions.');
}
$notice = $_SESSION['notice'] ?? '';
unset($_SESSION['notice']);
?>
<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>OrderDesk · PHP learning project</title><link rel="stylesheet" href="/style.css"></head>
<body><main>
<header><div class="eyebrow">PHP + SQLITE / LEARNING PROJECT</div><h1>OrderDesk<span>.</span></h1>
<p>A small, practical workspace for tracking customer orders.</p></header>
<div class="stats"><section><small>Total orders</small><strong><?= (int)$stats['count'] ?></strong></section>
<section><small>Order value excluding cancelled · EUR</small><strong><?= number_format((int)$stats['value'] / 100, 2) ?></strong></section></div>
<?php if ($error): ?><p role="alert" class="error"><?= e($error) ?></p><?php endif ?>
<?php if ($notice): ?><p role="status" class="notice"><?= e($notice) ?></p><?php endif ?>
<section class="panel"><h2>New order</h2><form method="post" class="create">
<input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>"><input type="hidden" name="action" value="create">
<label>Customer<input name="customer" maxlength="120" required value="<?= $error ? e(field($_POST, 'customer')) : '' ?>" placeholder="Example customer"></label>
<label>Product<input name="product" maxlength="120" required value="<?= $error ? e(field($_POST, 'product')) : '' ?>" placeholder="Product name"></label>
<label>Quantity<input name="quantity" type="number" min="1" max="1000" required value="<?= $error ? e(field($_POST, 'quantity')) : '1' ?>"></label>
<label>Unit price · EUR<input name="price" type="number" min="0" max="999999.99" step="0.01" required value="<?= $error ? e(field($_POST, 'price')) : '' ?>" placeholder="29.90"></label>
<button>Add order</button></form></section>
<section class="panel"><h2>Orders</h2><form method="get" class="filters">
<label>Search<input name="q" value="<?= e($q) ?>" placeholder="Customer or product"></label>
<label>Status<select name="status"><option value="">All statuses</option><?php foreach ($statuses as $status): ?>
<option <?= $filter === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach ?></select></label>
<button>Filter</button><a href="/">Reset</a></form>
<div class="table-wrap"><table><thead><tr><th>Order</th><th>Customer / product</th><th>Qty</th><th>Total EUR</th><th>Status</th></tr></thead><tbody>
<?php foreach ($orders as $order): ?><tr><td>#<?= (int)$order['id'] ?><small><?= e($order['created_at']) ?> UTC</small></td>
<td><?= e($order['customer']) ?><small><?= e($order['product']) ?></small></td><td><?= (int)$order['quantity'] ?></td>
<td><?= number_format($order['quantity'] * $order['unit_cents'] / 100, 2) ?></td>
<td><form method="post" class="status-form"><input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
<input type="hidden" name="action" value="status"><input type="hidden" name="id" value="<?= (int)$order['id'] ?>">
<select name="status" aria-label="Status for order <?= (int)$order['id'] ?>"><?php foreach ($statuses as $status): ?>
<option <?= $order['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach ?></select><button>Save</button></form></td></tr><?php endforeach ?>
<?php if (!$orders): ?><tr><td colspan="5" class="empty">No orders found. Add your first order or change the filters.</td></tr><?php endif ?>
</tbody></table></div><p class="hint">Showing up to 200 newest matching orders. Totals above cover all orders.</p></section>
<footer>AI-assisted learning demo · Use fictional data only · Local use, no authentication</footer>
</main></body></html>
