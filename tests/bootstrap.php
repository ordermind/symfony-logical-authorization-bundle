<?php

declare(strict_types=1);

use Doctrine\Common\Annotations\AnnotationRegistry;

$file = __DIR__ . '/../vendor/autoload.php';
if (!file_exists($file)) {
    $file = __DIR__ . '/../../../../vendor/autoload.php';
    if (!file_exists($file)) {
        throw new RuntimeException('Install dependencies to run test suite.');
    }
}

$autoload = require $file;
AnnotationRegistry::registerLoader([$autoload, 'loadClass']);

// --------------BOOT KERNEL--------------

require_once __DIR__ . '/AppKernel.php';

$kernel = new Ordermind\LogicalAuthorizationBundle\Test\AppKernel('test', true); // create a "test" kernel
$kernel->boot();
