<?php
declare(strict_types=1);
require __DIR__ . '/src/app.php';
$checks = 0;
function check(bool $result, string $message): void {
    global $checks;
    if (!$result) throw new RuntimeException($message);
    $checks++;
}
foreach (['0'=>0, '12.3'=>1230, '19.99'=>1999, '999999.99'=>99999999] as $input=>$expected) {
    check(cents((string)$input) === $expected, 'Money conversion failed');
}
foreach (['-1','1.234','1e3','12,50','','1000000','01'] as $input) {
    $rejected = false;
    try { cents($input); } catch (InvalidArgumentException $e) { $rejected = true; }
    check($rejected, 'Invalid price accepted: ' . $input);
}
check(e('<script>"&') === '&lt;script&gt;&quot;&amp;', 'HTML escaping failed');
check(field(['name'=>['unexpected']], 'name') === '', 'Array input not handled');
echo "Passed {$checks} checks.\n";
