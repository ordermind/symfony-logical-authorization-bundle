<?php

namespace Ordermind\LogicalAuthorizationBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;

use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface;

class Collector extends DataCollector implements CollectorInterface, LateDataCollectorInterface {
  protected $treeBuilder;
  protected $lpProxy;
  protected $permission_log;

  public function __construct(PermissionTreeBuilderInterface $treeBuilder, LogicalPermissionsProxyInterface $lpProxy) {
    $this->treeBuilder = $treeBuilder;
    $this->lpProxy = $lpProxy;
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
        $type_keys = array_keys($this->lpProxy->getTypes());
        $log_item['permission_no_bypass_checks'] = $this->getPermissionNoBypassChecks($log_item['permissions'], $log_item['context'], $type_keys);
        $log_item['bypassed_access'] = $this->getBypassedAccess($log_item['permissions'], $log_item['context']);
        unset($log_item['permissions']['no_bypass']);
        unset($log_item['permissions']['NO_BYPASS']);
        $log_item['permission_checks'] = $this->getPermissionChecks($log_item['permissions'], $log_item['context'], $type_keys);
        $log_item['permissions'] = json_encode($log_item['permissions']);
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

  protected function getPermissionChecks($permissions, $context, $type_keys) {
    $getPermissionChecksRecursive = function($permissions, $context, $type_keys, $type = null) use(&$getPermissionChecksRecursive) {
      $checks = [];

      if(is_array($permissions)) {
        foreach($permissions as $key => $subpermissions) {
          $this_type = $type;
          if(!$this_type && in_array($key, $type_keys, true)) {
            $this_type = $key;
          }
          $checks = array_merge($checks, $getPermissionChecksRecursive($subpermissions, $context, $type_keys, $this_type));
          if(!is_numeric($key)) {
            if($this_type && $this_type !== $key) {
              $checks[] = [
                'permissions' => json_encode([$key => $subpermissions]),
                'access' => $this->lpProxy->checkAccess([$this_type => [$key => $subpermissions]], $context, false),
              ];
            }
            if(!$this_type) {
              $checks[] = [
                'permissions' => json_encode([$key => $subpermissions]),
                'access' => $this->lpProxy->checkAccess([$key => $subpermissions], $context, false),
              ];
            }
          }
        }
      }
      else {
        if($type) {
          $checks[] = [
            'permissions' => json_encode($permissions),
            'access' => $this->lpProxy->checkAccess([$type => $permissions], $context, false),
          ];
        }
        else {
          $checks[] = [
            'permissions' => json_encode($permissions),
            'access' => $this->lpProxy->checkAccess($permissions, $context, false),
          ];
        }
      }

      return $checks;
    };

    $checks = $getPermissionChecksRecursive($permissions, $context, $type_keys);
    $checks[] = [
      'permissions' => json_encode($permissions),
      'access' => $this->lpProxy->checkAccess($permissions, $context, false),
    ];

    return $checks;
  }

  protected function getPermissionNoBypassChecks($permissions, $context, $type_keys) {
    echo "\n Don't forget to implement Collector::getPermissionNoBypassChecks()\n";
    return [];
  }

  protected function getBypassedAccess($permissions, $context) {
    echo "\n Don't forget to implement Collector::getBypassedAccess()\n";
    return false;
  }
}
