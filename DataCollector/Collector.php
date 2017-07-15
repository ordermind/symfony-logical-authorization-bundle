<?php

namespace Ordermind\LogicalAuthorizationBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;

class Collector extends DataCollector implements CollectorInterface, LateDataCollectorInterface {
  protected $treeBuilder;
  protected $permission_log;

  public function __construct(PermissionTreeBuilderInterface $treeBuilder) {
    $this->treeBuilder = $treeBuilder;
    $this->permission_log = [];
  }

  public function getName() {
    return 'logauth.collector';
  }

  public function collect(Request $request, Response $response, \Exception $exception = null) {
    foreach($this->permission_log as &$log_item) {
      if($log_item['type'] === 'model' || $log_item['type'] === 'field') {
        $log_item['action'] = $log_item['item']['action'];
      }

      $formatted_item = $this->formatItem($log_item['type'], $log_item['item']);
      unset($log_item['item']);
      $log_item += $formatted_item;

      if($log_item['log_type'] === 'check') {
        $log_item['permissions'] = $this->formatPermissions($log_item['permissions'], $log_item['context']);
        unset($log_item['context']);
      }
    }
    unset($log_item);
    $this->data = [
      'tree' => $this->treeBuilder->getTree(),
      'log' => $this->permission_log,
    ];
  }

  public function lateCollect()
  {
    $this->data = $this->cloneVar($this->data);
  }

  public function getPermissionTree() {
    return $this->data['tree'];
  }

  public function getLog() {
    return $this->data['log'];
  }

  public function addPermissionCheckAttempt($type, $item, $user) {
    $this->addPermissionLogItem(['log_type' => 'attempt', 'type' => $type, 'item' => $item, 'user' => $user]);
  }

  public function addPermissionCheck($type, $item, $user, $permissions, $context) {
    $this->addPermissionLogItem(['log_type' => 'check', 'type' => $type, 'item' => $item, 'user' => $user, 'permissions' => $permissions, 'context' => $context]);
  }

  protected function addPermissionLogItem($log_item) {
    $this->permission_log[] = $log_item;
  }

  protected function formatItem($type, $item) {
    $formatted_item = [];

    if($type === 'route') {
      return [
        'item_name' => $item,
      ];
    }

    $model = $item['model'];
    $formatted_item['item_name'] = $model;
    if(is_object($model)) {
      $formatted_item['item'] = $model;
      $formatted_item['item_name'] = get_class($model);
    }
    if($type === 'field') {
      $formatted_item['item_name'] .= ":{$item['field']}";
    }

    return $formatted_item;
  }

  protected function formatPermissions($permissions, $context) {
    return $permissions;
  }
}
