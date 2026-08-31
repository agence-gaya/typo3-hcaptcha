<?php

declare(strict_types=1);

require_once __DIR__ . '/../.build/vendor/autoload.php';

use Symfony\Component\Console\Application;
use Symfony\Component\Yaml\Command\LintCommand;

$application = new Application('yaml/lint');
$application
    ->addCommand(new LintCommand())
    ->getApplication()
    ->setDefaultCommand('lint:yaml', true)
    ->run();
