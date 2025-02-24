<?php
define('ROOT_PATH', __DIR__);
define('PYTHON_SCRIPT', ROOT_PATH . '/public/Bot/main.py');
define('PYTHON_BIN', 'python');

$start_time = strtotime(date('Y-m-d') . ' 10:50');
$end_time = strtotime(date('Y-m-d') . ' 11:00');

while (true) {
    $now = time();

    if ($now >= $start_time && $now <= $end_time) {
        $command = PYTHON_BIN . ' ' . escapeshellarg(PYTHON_SCRIPT) . ' "sendByApi"';
        exec($command . ' >> log.txt 2>&1 &');

        file_put_contents('log.txt', date('Y-m-d H:i:s') . " - Executed command: $command\n", FILE_APPEND);

        exit();
    }

    if ($now > $end_time) {
        file_put_contents('log.txt', date('Y-m-d H:i:s') . " - Skipped execution (past 08:30)\n", FILE_APPEND);
        exit();
    }

    sleep(60);
}
