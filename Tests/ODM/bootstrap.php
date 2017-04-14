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

AnnotationRegistry::registerFile(__DIR__.'/../../vendor/doctrine/mongodb-odm/lib/Doctrine/ODM/MongoDB/Mapping/Annotations/DoctrineAnnotations.php');
AnnotationRegistry::registerFile(__DIR__.'/../../Doctrine/Annotation/LogicalAuthorization.php');
AnnotationRegistry::registerLoader(array($autoload, 'loadClass'));

/*--------------CREATE DATABASE--------------*/

// require_once __DIR__.'/bootstrap.php.cache';
require_once __DIR__.'/AppKernel.php';

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\ArrayInput;

use Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\DropCommand as ODMDropCommand;
use Doctrine\ODM\MongoDB\Tools\Console\Command\Schema\CreateCommand as ODMCreateCommand;

$kernel = new AppKernel('test', true); // create a "test" kernel
$kernel->boot();

$application = new Application($kernel);

$dm = $kernel->getContainer()->get('doctrine.odm.mongodb.document_manager');
$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
  'dm' => new \Doctrine\ODM\MongoDB\Tools\Console\Helper\DocumentManagerHelper($dm),
));
$application->setHelperSet($helperSet);

// add the mongodb:schema:create command to the application and run it
$command = new ODMDropCommand();
$application->add($command);
$input = new ArrayInput(array(
    'command' => 'doctrine:mongodb:schema:drop',
));
$command->run($input, new ConsoleOutput());

// add the mongodb:schema:create command to the application and run it
$command = new ODMCreateCommand();
$application->add($command);
$input = new ArrayInput(array(
    'command' => 'doctrine:mongodb:schema:create',
));
$command->run($input, new ConsoleOutput());
