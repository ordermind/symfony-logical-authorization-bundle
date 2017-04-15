<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

$file = __DIR__.'/../../vendor/autoload.php';
if (!file_exists($file))
{
    $file = __DIR__.'/../../../../../vendor/autoload.php';
    if (!file_exists($file))
        throw new RuntimeException('Install dependencies to run test suite.');
}

$autoload = require $file;

AnnotationRegistry::registerFile(__DIR__.'/../../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');
AnnotationRegistry::registerFile(__DIR__.'/../../Doctrine/Annotation/LogicalAuthorization.php');
AnnotationRegistry::registerLoader(array($autoload, 'loadClass'));

/*--------------CREATE DATABASE--------------*/

// require_once __DIR__.'/bootstrap.php.cache';
require_once __DIR__.'/AppKernel.php';

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArrayInput;

use Doctrine\Bundle\DoctrineBundle\Command\DropDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\CreateDatabaseDoctrineCommand;
use Doctrine\Bundle\DoctrineBundle\Command\Proxy\CreateSchemaDoctrineCommand;

$kernel = new AppKernel('test', true); // create a "test" kernel
$kernel->boot();

$application = new Application($kernel);

// add the database:drop command to the application and run it
$command = new DropDatabaseDoctrineCommand();
$application->add($command);
$input = new ArrayInput(array(
    'command' => 'doctrine:database:drop',
    '--force' => true,
));
$command->run($input, new ConsoleOutput());

// add the database:create command to the application and run it
$command = new CreateDatabaseDoctrineCommand();
$application->add($command);
$input = new ArrayInput(array(
    'command' => 'doctrine:database:create',
));
$command->run($input, new ConsoleOutput());

// let Doctrine create the database schema (i.e. the tables)
$command = new CreateSchemaDoctrineCommand();
$application->add($command);
$input = new ArrayInput(array(
    'command' => 'doctrine:schema:create',
));
$command->run($input, new ConsoleOutput());
