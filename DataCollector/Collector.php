<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface;

class Collector extends DataCollector implements CollectorInterface {
  protected $treeBuilder;
  protected $lpProxy;
  protected $permission_log;
  protected $data;

  /**
   * @internal
   *
   * @param Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface $treeBuilder A tree builder for fetching the full permission tree
   * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface $lpProxy A proxy for checking permissions
   */
  public function __construct(PermissionTreeBuilderInterface $treeBuilder, LogicalPermissionsProxyInterface $lpProxy) {
    $this->treeBuilder = $treeBuilder;
    $this->lpProxy = $lpProxy;
    $this->permission_log = [];
    $this->data = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getName(): string {
    return 'logauth.collector';
  }

  /**
   * {@inheritdoc}
   */
  public function collect(Request $request, Response $response, \Exception $exception = null) {
    $log = $this->formatLog($this->permission_log);
    $this->data = [
      'tree' => $this->treeBuilder->getTree(),
      'log' => $log,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function lateCollect()
  {
    $this->data['tree'] = $this->cloneVar($this->data['tree']);
    foreach($this->data['log'] as &$log_item) {
      if(!empty($log_item['item'])) {
        $log_item['item'] = $this->cloneVar($log_item['item']);
      }
      if(!empty($log_item['user']) && $log_item['user'] !== 'anon.') {
        $log_item['user'] = $this->cloneVar($log_item['user']);
      }
      if(!empty($log_item['backtrace'])) {
        $log_item['backtrace'] = $this->cloneVar($log_item['backtrace']);
      }
    }
    unset($log_item);
  }

  /**
   * {@inheritdoc}
   */
  public function reset()
  {
    $this->data = [];
  }

  /**
   * {@inheritdoc}
   */
  public function getPermissionTree(): array {
    return $this->data['tree'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLog(): array {
    return $this->data['log'];
  }

  /**
   * {@inheritdoc}
   */
  public function addPermissionCheck(bool $access, string $type, $item, $user, $permissions, array $context, string $message = '') {
    $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 11);
    array_shift($backtrace);
    $this->addPermissionLogItem(['access' => $access, 'type' => $type, 'item' => $item, 'user' => $user, 'permissions' => $permissions, 'context' => $context, 'message' => $message, 'backtrace' => $backtrace]);
  }

  protected function addPermissionLogItem(array $log_item) {
    $this->permission_log[] = $log_item;
  }

  protected function formatLog(array $log): array {
    foreach($log as &$log_item) {
      if($log_item['type'] === 'model' || $log_item['type'] === 'field') {
        $log_item['action'] = $log_item['item']['action'];
      }

      if($log_item['type'] === 'field') {
        $log_item['field'] = $log_item['item']['field'];
      }

      $formatted_item = $this->formatItem($log_item['type'], $log_item['item']);
      unset($log_item['item']);
      $log_item += $formatted_item;

      if(!empty($log_item['message'])) continue;

      if(is_array($log_item['permissions']) && array_key_exists('no_bypass', $log_item['permissions'])) {
        $log_item['permissions']['NO_BYPASS'] = $log_item['permissions']['no_bypass'];
        unset($log_item['permissions']['no_bypass']);
      }

      $type_keys = array_keys($this->lpProxy->getTypes());

      $log_item['permission_no_bypass_checks'] = array_reverse($this->getPermissionNoBypassChecks($log_item['permissions'], $log_item['context'], $type_keys));
      if(count($log_item['permission_no_bypass_checks']) == 1 && !empty($log_item['permission_no_bypass_checks'][0]['error'])) {
        $log_item['message'] = $log_item['permission_no_bypass_checks'][0]['error'];
      }

      $log_item['bypassed_access'] = $this->getBypassedAccess($log_item['permissions'], $log_item['context']);

      $pure_permissions = $log_item['permissions'];
      unset($pure_permissions['NO_BYPASS']);

      $log_item['permission_checks'] = array_reverse($this->getPermissionChecks($pure_permissions, $log_item['context'], $type_keys));
      if(count($log_item['permission_checks']) == 1 && !empty($log_item['permission_checks'][0]['error'])) {
        $log_item['message'] = $log_item['permission_checks'][0]['error'];
      }

      unset($log_item['context']);
    }
    unset($log_item);

    return $log;
  }

  protected function formatItem(string $type, $item): array {
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

  protected function getPermissionChecks($permissions, array $context, array $type_keys): array {
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

    $getPermissionChecksRecursive = function($permissions, array $context, array $type_keys, string $type = null) use(&$getPermissionChecksRecursive): array {
      if(!is_array($permissions)) {
        $resolve_permissions = $permissions;
        if($type) {
          $resolve_permissions = [$type => $permissions];
        }
        return [[
          'permissions' => $permissions,
          'resolve' => $this->lpProxy->checkAccess($resolve_permissions, $context, false),
        ]];
      }

      reset($permissions);
      $key = key($permissions);
      $value = current($permissions);

      if(is_numeric($key)) {
        return $getPermissionChecksRecursive($value, $context, $type_keys, $type);
      }
      else {
        if(in_array($key, $type_keys, true)) {
          $type = $key;
        }
      }

      if(is_array($value)) {
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

      if($key === $type) {
        return [[
          'permissions' => $permissions,
          'resolve' => $this->lpProxy->checkAccess($permissions, $context, false),
        ]];
      }

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

    return $checks;
  }

  protected function getPermissionNoBypassChecks($permissions, array $context, array $type_keys): array {
    if(is_array($permissions) && array_key_exists('NO_BYPASS', $permissions)) {
      return $this->getPermissionChecks($permissions['NO_BYPASS'], $context, $type_keys);
    }

    return [];
  }

  protected function getBypassedAccess($permissions, array $context): bool {
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
