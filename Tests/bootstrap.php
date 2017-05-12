<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file))
{
    $file = __DIR__.'/../../../../vendor/autoload.php';
    if (!file_exists($file))
        throw new RuntimeException('Install dependencies to run test suite.');
}

$autoload = require $file;

/*--------------CREATE DATABASE--------------*/

require_once __DIR__.'/AppKernel.php';

$kernel = new AppKernel('test', true); // create a "test" kernel
$kernel->boot();
