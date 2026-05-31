<?php

$baseCommands = [
    'php artisan serve --host=localhost',
    'php artisan queue:listen --tries=1 --timeout=0',
    'npm run dev',
];

$commands = $baseCommands;

if (function_exists('pcntl_fork')) {
    $commands[] = 'php artisan pail --timeout=0';
}

$colors = '#93c5fd,#c4b5fd,#fb7185,#fdba74';
$names = function_exists('pcntl_fork')
    ? '--names=server,queue,vite,logs'
    : '--names=server,queue,vite';

$cmd = sprintf(
    'npx concurrently -c "%s" %s %s --kill-others',
    $colors,
    implode(' ', array_map(static fn ($command) => '"' . $command . '"', $commands)),
    $names
);

passthru($cmd, $exitCode);

exit($exitCode);
