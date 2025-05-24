<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    new Dotenv()->bootEnv(dirname(__DIR__).'/.env');
}

// Check if we need to initialize the test database
if (isset($_SERVER['BOOTSTRAP_CLEAR_DATABASE_URL']) && (bool)$_SERVER['BOOTSTRAP_CLEAR_DATABASE_URL'] === true) {

    echo "Dropping test database...\n";
    $process = new Process(['php', dirname(__DIR__).'/bin/console', 'doctrine:database:drop', '--force', '--env=test']);
    $process->run();

    if (!$process->isSuccessful()) {
        echo "Error dropping database: " . $process->getErrorOutput() . "\n";
    }

    echo "Recreating test database...\n";
    $process = new Process(['php', dirname(__DIR__).'/bin/console', 'doctrine:database:create', '--env=test']);
    $process->run();

    if (!$process->isSuccessful()) {
        echo "Error creating database: " . $process->getErrorOutput() . "\n";
    }

    echo "Running migrations...\n";
    $process = new Process(['php', dirname(__DIR__).'/bin/console', 'doctrine:migrations:migrate', '--no-interaction', '--env=test']);
    $process->run();

    if (!$process->isSuccessful()) {
        echo "Error running migrations: " . $process->getErrorOutput() . "\n";
    }

    echo "Test database setup complete.\n";
}
