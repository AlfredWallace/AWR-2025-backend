<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Process\Process;

require dirname(__DIR__).'/vendor/autoload.php';

if (method_exists(Dotenv::class, 'bootEnv')) {
    new Dotenv()->bootEnv(dirname(__DIR__).'/.env');
}

// Check if we need to initialize the test database
if (isset($_SERVER['BOOTSTRAP_CLEAR_DATABASE_URL']) && $_SERVER['BOOTSTRAP_CLEAR_DATABASE_URL'] === 'true') {
    echo "Setting up test database...\n";

    // Drop the test database if it exists
    $process = new Process(['php', dirname(__DIR__).'/bin/console', 'doctrine:database:drop', '--force', '--env=test']);
    $process->run();

    // Create the test database
    $process = new Process(['php', dirname(__DIR__).'/bin/console', 'doctrine:database:create', '--env=test']);
    $process->run();

    // Run migrations to set up the schema
    $process = new Process(['php', dirname(__DIR__).'/bin/console', 'doctrine:migrations:migrate', '--no-interaction', '--env=test']);
    $process->run();

    echo "Test database setup complete.\n";
}
