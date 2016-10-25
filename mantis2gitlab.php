#!/usr/bin/env php
<?php
require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;
use M2G\Command\TestCommand;
use M2G\Command\TestMantisCommand;
use M2G\Command\TestGitlabCommand;

$application = new Application();

$application->add(new TestCommand());
$application->add(new TestMantisCommand());
$application->add(new TestGitlabCommand());

$application->run();