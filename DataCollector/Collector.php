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
    $log = $this->formatLog($this->permission_log);
    $this->data = [
      'tree' => $this->treeBuilder->getTree(),
      'log' => $log,
    ];
  }

  public function lateCollect()
  {
    $this->data['tree'] = $this->cloneVar($this->data['tree']);
    foreach($this->data['log'] as &$log_item) {
      if($log_item['permission_checks']) {
        array_shift($log_item['permission_checks']);
      }
    }
    unset($log_item);
  }

  public function getPermissionTree() {
    return $this->data['tree'];
  }

  public function getLog() {
    return $this->data['log'];
  }

  public function addPermissionCheck($type, $item, $user, $permissions, $context) {
    $this->addPermissionLogItem(['type' => $type, 'item' => $item, 'user' => $user, 'permissions' => $permissions, 'context' => $context]);
  }

  protected function addPermissionLogItem($log_item) {
    $this->permission_log[] = $log_item;
  }

  protected function formatLog($log) {
    foreach($log as &$log_item) {
      if($log_item['type'] === 'model' || $log_item['type'] === 'field') {
        $log_item['action'] = $log_item['item']['action'];
      }

      $formatted_item = $this->formatItem($log_item['type'], $log_item['item']);
      unset($log_item['item']);
      $log_item += $formatted_item;

      if(is_array($log_item['permissions']) && array_key_exists('no_bypass', $log_item['permissions'])) {
        $log_item['permissions']['NO_BYPASS'] = $log_item['permissions']['no_bypass'];
        unset($log_item['permissions']['no_bypass']);
      }
      $type_keys = array_keys($this->lpProxy->getTypes());
      $log_item['permission_no_bypass_checks'] = array_reverse($this->getPermissionNoBypassChecks($log_item['permissions'], $log_item['context'], $type_keys));
      if(count($log_item['permission_no_bypass_checks']) == 1 && !empty($log_item['permission_no_bypass_checks'][0]['error'])) {
        $log_item['permission_no_bypass_check_error'] = $log_item['permission_no_bypass_checks'][0]['error'];
      }
      $log_item['bypassed_access'] = $this->getBypassedAccess($log_item['permissions'], $log_item['context']);
      unset($log_item['permissions']['NO_BYPASS']);
      $log_item['permission_checks'] = array_reverse($this->getPermissionChecks($log_item['permissions'], $log_item['context'], $type_keys));
      if(count($log_item['permission_checks']) == 1 && !empty($log_item['permission_checks'][0]['error'])) {
        $log_item['permission_check_error'] = $log_item['permission_checks'][0]['error'];
      }

      $first_permission_check = reset($log_item['permission_checks']);
      $log_item['access'] = $first_permission_check['resolve'];

      unset($log_item['context']);
    }
    unset($log_item);

    return $log;
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
    // Extra permission check of the whole tree to catch errors
    try {
      $this->lpProxy->checkAccess($permissions, $context, false);
    }
    catch(\Exception $e) {
      return [[
        'permissions' => $permissions,
        'resolve' => false,
        'error' => $e->getMessage(),
      ]];
    }

    $getPermissionChecksRecursive = function($permissions, $context, $type_keys, $type = null) use(&$getPermissionChecksRecursive) {
//       echo "\n\nnew round\n";
//       echo "permissions: " . print_r($permissions, true) . "\n";
//       echo "type: $type\n";

      if(!is_array($permissions)) {
//         echo "permissions is not an array\n";
        $resolve_permissions = $permissions;
        if($type) {
          $resolve_permissions = [$type => $permissions];
        }
        return [[
          'permissions' => $permissions,
          'resolve' => $this->lpProxy->checkAccess($resolve_permissions, $context, false),
        ]];
      }

//       echo "permissions is an array\n";

      reset($permissions);
      $key = key($permissions);
      $value = current($permissions);

//       echo "key: $key\n";

      if(is_numeric($key)) {
        return $getPermissionChecksRecursive($value, $context, $type_keys, $type);
      }
      else {
        if(in_array($key, $type_keys, true)) {
          $type = $key;
        }
      }

      if(is_array($value)) {
//         echo "value is an array\n";
        $checks = [];
        foreach($value as $key2 => $value2) {
          $checks = array_merge($checks, $getPermissionChecksRecursive([$key2 => $value2], $context, $type_keys, $type));
        }
        $resolve_permissions = $permissions;
        if($type && $key !== $type) {
          $resolve_permissions = [$type => $permissions];
        }
        $checks[] = [
          'permissions' => $permissions,
          'resolve' => $this->lpProxy->checkAccess($resolve_permissions, $context, false),
        ];
        return $checks;
      }

//       echo "value is not an array\n";

      if($key === $type) {
//         echo "key is same as type\n";
        return [[
          'permissions' => $permissions,
          'resolve' => $this->lpProxy->checkAccess($permissions, $context, false),
        ]];
      }

//       echo "key is not same as type\n";

      $checks = [];
      $resolve_value = $value;
      if($type) {
        $resolve_value = [$type => $resolve_value];
      }
      $checks[] = [
        'permissions' => $value,
        'resolve' => $this->lpProxy->checkAccess($resolve_value, $context, false),
      ];

      $resolve_permissions = $permissions;
      if($type) {
        $resolve_permissions = [$type => $resolve_permissions];
      }
      $checks[] = [
        'permissions' => $permissions,
        'resolve' => $this->lpProxy->checkAccess($resolve_permissions, $context, false),
      ];

      return $checks;
    };

//     echo "\npermissions\n";
//     print_r($permissions);

    $checks = [];

    if(is_array($permissions)) {
      foreach($permissions as $key => $value) {
        $checks = array_merge($checks, $getPermissionChecksRecursive([$key => $value], $context, $type_keys));
      }
      if(count($permissions) > 1) {
        $checks[] = [
          'permissions' => $permissions,
          'resolve' => $this->lpProxy->checkAccess($permissions, $context, false),
        ];
      }
    }
    else {
      $checks = array_merge($checks, $getPermissionChecksRecursive($permissions, $context, $type_keys));
    }

//     echo "\nchecks\n";
//     print_r($checks);

    return $checks;
  }

  protected function getPermissionNoBypassChecks($permissions, $context, $type_keys) {
    if(is_array($permissions) && array_key_exists('NO_BYPASS', $permissions)) {
      return $this->getPermissionChecks($permissions['NO_BYPASS'], $context, $type_keys);
    }

    return [];
  }

  protected function getBypassedAccess($permissions, $context) {
    $new_permissions = [false];
    if(is_array($permissions) && array_key_exists('NO_BYPASS', $permissions)) {
      $new_permissions['NO_BYPASS'] = $permissions['NO_BYPASS'];
    }

    try {
      return $this->lpProxy->checkAccess($new_permissions, $context);
    }
    catch (\Exception $e) {}

    return false;
  }
}
