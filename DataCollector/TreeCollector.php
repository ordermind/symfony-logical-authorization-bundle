<?php

namespace Ordermind\LogicalAuthorizationBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;

class TreeCollector extends DataCollector {
  protected $treeBuilder;

  public function __construct(PermissionTreeBuilderInterface $treeBuilder) {
    $this->treeBuilder = $treeBuilder;
  }

  public function getName() {
    return 'logauth.tree_collector';
  }

  public function collect(Request $request, Response $response, \Exception $exception = null) {
    $this->data = $this->treeBuilder->getTree();
  }
}
