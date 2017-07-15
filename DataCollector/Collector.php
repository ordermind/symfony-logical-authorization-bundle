<?php

namespace Ordermind\LogicalAuthorizationBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;

class Collector extends DataCollector implements CollectorInterface, LateDataCollectorInterface {
  protected $treeBuilder;

  public function __construct(PermissionTreeBuilderInterface $treeBuilder) {
    $this->treeBuilder = $treeBuilder;
  }

  public function getName() {
    return 'logauth.collector';
  }

  public function collect(Request $request, Response $response, \Exception $exception = null) {
    $this->data = array(
      'tree' => $this->treeBuilder->getTree(),
    );
  }

  public function lateCollect()
  {
    $this->data = $this->cloneVar($this->data);
  }

  public function getPermissionTree() {
    return $this->data['tree'];
  }

  public function addPermissionCheckAttempt($type, $item, $user) {

  }

  public function addPermissionCheck($type, $item, $user, $permissions) {

  }
}
