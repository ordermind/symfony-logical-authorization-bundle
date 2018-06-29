<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Symfony\Component\Yaml\Yaml;

/**
 * {@inheritdoc}
 */
class DumpPermissionTreeCommand extends ContainerAwareCommand
{
   /**
    * {@inheritdoc}
    */
    protected function configure()
    {
        $this->setName('logauth:dump-permission-tree');
        $this->setDescription('Logical Authorization: Outputs the whole permission tree.');
        $this->addOption(
            'format',
            null,
            InputOption::VALUE_OPTIONAL,
            'Select the output format. Available formats: yml, json',
            'yml'
        );
    }

   /**
    * {@inheritdoc}
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $tree = $container->get('logauth.service.permission_tree_builder')->getTree();
        $format = $input->getOption('format');

        if ('yml' === $format) {
            $output->write(Yaml::dump($tree, 20));
        } elseif ('json' === $format) {
            $output->write(json_encode($tree));
        } else {
            $output->writeln('Error outputting permission tree: Unrecognized format. Available formats: yml, json');
        }
    }
}
