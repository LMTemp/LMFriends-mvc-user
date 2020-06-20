<?php

declare(strict_types=1);

chdir(__DIR__);

$loader = null;
if (file_exists(__DIR__.'/../vendor/autoload.php')) {
    $loader = include __DIR__.'/../vendor/autoload.php';
} elseif (file_exists('../../../autoload.php')) {
    $loader = include __DIR__.'/../../../autoload.php';
} else {
    throw new RuntimeException('vendor/autoload.php could not be found. Did you run `php composer.phar install`?');
}

$loader->add('LaminasFriendsTest\Mvc\User', __DIR__);

if (!$config = @include __DIR__.'/configuration.php') {
    $config = require __DIR__.'/configuration.php.dist';
}
