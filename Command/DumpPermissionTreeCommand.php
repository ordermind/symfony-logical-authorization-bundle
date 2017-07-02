<?php

namespace Ordermind\LogicalAuthorizationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpPermissionTreeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('ordermind-logical-authorization:dump-permission-tree');
        $this->setDescription('Outputs the whole permission tree.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();

        $tree = $container->get('ordermind_logical_authorization.service.permission_tree_builder')->getTree();
        $output->write(json_encode($tree, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}
