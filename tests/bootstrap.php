<?php

declare(strict_types=1);

require_once __DIR__ . '/AppKernel.php';

$kernel = new Ordermind\LogicalAuthorizationBundle\Test\AppKernel('test', true); // create a "test" kernel
$kernel->boot();
