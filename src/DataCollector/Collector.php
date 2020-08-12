<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\DataCollector;

use Symfony\Component\HttpKernel\DataCollector\DataCollector;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\VarDumper\Cloner\Data;

use Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface;

/**
 * {@inheritdoc}
 */
class Collector extends DataCollector implements CollectorInterface
{
    /**
     * @var Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface
     */
    protected $treeBuilder;

    /**
     * @var Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface
     */
    protected $lpProxy;

    /**
     * @var array
     */
    protected $permissionLog;

    /**
     * @var array
     */
    protected $data;

    /**
     * @internal
     *
     * @param Ordermind\LogicalAuthorizationBundle\Services\PermissionTreeBuilderInterface   $treeBuilder A tree builder for fetching the full permission tree
     * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface $lpProxy     A proxy for checking permissions
     */
    public function __construct(PermissionTreeBuilderInterface $treeBuilder, LogicalPermissionsProxyInterface $lpProxy)
    {
        $this->treeBuilder = $treeBuilder;
        $this->lpProxy = $lpProxy;
        $this->permissionLog = [];
        $this->data = [];
    }

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        return 'logauth.collector';
    }

    /**
     * {@inheritdoc}
     */
    public function collect(Request $request, Response $response, \Throwable $exception = null)
    {
        $log = $this->formatLog($this->permissionLog);
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
        foreach ($this->data['log'] as &$logItem) {
            if (!empty($logItem['item'])) {
                $logItem['item'] = $this->cloneVar($logItem['item']);
            }
            if (!empty($logItem['user']) && $logItem['user'] !== 'anon.') {
                $logItem['user'] = $this->cloneVar($logItem['user']);
            }
            if (!empty($logItem['backtrace'])) {
                $logItem['backtrace'] = $this->cloneVar($logItem['backtrace']);
            }
        }
        unset($logItem);
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
    public function getPermissionTree(): Data
    {
        return $this->data['tree'];
    }

    /**
     * {@inheritdoc}
     */
    public function getLog(): array
    {
        return $this->data['log'];
    }

    /**
     * {@inheritdoc}
     */
    public function addPermissionCheck(bool $access, string $type, $item, $user, $permissions, array $context, string $message = '')
    {
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 11);
        array_shift($backtrace);
        $this->addPermissionLogItem(['access' => $access, 'type' => $type, 'item' => $item, 'user' => $user, 'permissions' => $permissions, 'context' => $context, 'message' => $message, 'backtrace' => $backtrace]);
    }

    /**
     * @internal
     *
     * @param array $logItem
     */
    protected function addPermissionLogItem(array $logItem)
    {
        $this->permissionLog[] = $logItem;
    }

    /**
     * @internal
     *
     * @param array $log
     *
     * @return array
     */
    protected function formatLog(array $log): array
    {
        foreach ($log as &$logItem) {
            if ($logItem['type'] === 'model' || $logItem['type'] === 'field') {
                $logItem['action'] = $logItem['item']['action'];
            }

            if ($logItem['type'] === 'field') {
                $logItem['field'] = $logItem['item']['field'];
            }

            $formattedItem = $this->formatItem($logItem['type'], $logItem['item']);
            unset($logItem['item']);
            $logItem += $formattedItem;

            if (!empty($logItem['message'])) {
                continue;
            }

            if (is_array($logItem['permissions']) && array_key_exists('no_bypass', $logItem['permissions'])) {
                $logItem['permissions']['NO_BYPASS'] = $logItem['permissions']['no_bypass'];
                unset($logItem['permissions']['no_bypass']);
            }

            $typeKeys = array_keys($this->lpProxy->getTypes());

            $logItem['permission_no_bypass_checks'] = array_reverse($this->getPermissionNoBypassChecks($logItem['permissions'], $logItem['context'], $typeKeys));
            if (count($logItem['permission_no_bypass_checks']) == 1 && !empty($logItem['permission_no_bypass_checks'][0]['error'])) {
                $logItem['message'] = $logItem['permission_no_bypass_checks'][0]['error'];
            }

            $logItem['bypassed_access'] = $this->getBypassedAccess($logItem['permissions'], $logItem['context']);

            $purePermissions = $logItem['permissions'];
            unset($purePermissions['NO_BYPASS']);

            $logItem['permission_checks'] = array_reverse($this->getPermissionChecks($purePermissions, $logItem['context'], $typeKeys));
            if (count($logItem['permission_checks']) == 1 && !empty($logItem['permission_checks'][0]['error'])) {
                $logItem['message'] = $logItem['permission_checks'][0]['error'];
            }

            unset($logItem['context']);
        }
        unset($logItem);

        return $log;
    }

    /**
     * @internal
     *
     * @param string       $type
     * @param string|array $item
     *
     * @return array
     */
    protected function formatItem(string $type, $item): array
    {
        $formattedItem = [];

        if ('route' === $type) {
            return [
            'item_name' => $item,
            ];
        }

        $model = $item['model'];
        $formattedItem['item_name'] = $model;
        if (is_object($model)) {
            $formattedItem['item'] = $model;
            $formattedItem['item_name'] = get_class($model);
        }
        if ('field' === $type) {
            $formattedItem['item_name'] .= ":{$item['field']}";
        }

        return $formattedItem;
    }

    /**
     * @internal
     *
     * @param string|array|bool $permissions
     * @param array             $context
     * @param array             $typeKeys
     *
     * @return array
     */
    protected function getPermissionChecks($permissions, array $context, array $typeKeys): array
    {
        // Extra permission check of the whole tree to catch errors
        try {
            $this->lpProxy->checkAccess($permissions, $context, false);
        } catch (\Exception $e) {
            return [[
            'permissions' => $permissions,
            'resolve' => false,
            'error' => $e->getMessage(),
            ], ];
        }

        $checks = [];

        if (is_array($permissions)) {
            foreach ($permissions as $key => $value) {
                $checks = array_merge($checks, $this->getPermissionChecksRecursive([$key => $value], $context, $typeKeys));
            }
            if (count($permissions) > 1) {
                $checks[] = [
                'permissions' => $permissions,
                'resolve' => $this->lpProxy->checkAccess($permissions, $context, false),
                ];
            }
        } else {
            $checks = array_merge($checks, $this->getPermissionChecksRecursive($permissions, $context, $typeKeys));
        }

        return $checks;
    }

    /**
     * @internal
     *
     * @param string|array|bool $permissions
     * @param array             $context
     * @param array             $typeKeys
     * @param string|null       $type
     *
     * @return array
     */
    protected function getPermissionChecksRecursive($permissions, array $context, array $typeKeys, string $type = null): array
    {
        if (!is_array($permissions)) {
            $resolvePermissions = $permissions;
            if ($type) {
                $resolvePermissions = [$type => $permissions];
            }

            return [
                [
                    'permissions' => $permissions,
                    'resolve' => $this->lpProxy->checkAccess($resolvePermissions, $context, false),
                ],
            ];
        }

        reset($permissions);
        $key = key($permissions);
        $value = current($permissions);

        if (is_numeric($key)) {
            return $this->getPermissionChecksRecursive($value, $context, $typeKeys, $type);
        }

        if (in_array($key, $typeKeys, true)) {
            $type = $key;
        }

        if (is_array($value)) {
            $checks = [];
            foreach ($value as $key2 => $value2) {
                $checks = array_merge($checks, $this->getPermissionChecksRecursive([$key2 => $value2], $context, $typeKeys, $type));
            }
            $resolvePermissions = $permissions;
            if ($type && $key !== $type) {
                $resolvePermissions = [$type => $permissions];
            }
            $checks[] = [
            'permissions' => $permissions,
            'resolve' => $this->lpProxy->checkAccess($resolvePermissions, $context, false),
            ];

            return $checks;
        }

        if ($key === $type) {
            return [[
            'permissions' => $permissions,
            'resolve' => $this->lpProxy->checkAccess($permissions, $context, false),
            ], ];
        }

        $checks = [];
        $resolveValue = $value;
        if ($type) {
            $resolveValue = [$type => $resolveValue];
        }
        $checks[] = [
        'permissions' => $value,
        'resolve' => $this->lpProxy->checkAccess($resolveValue, $context, false),
        ];

        $resolvePermissions = $permissions;
        if ($type) {
            $resolvePermissions = [$type => $resolvePermissions];
        }
        $checks[] = [
        'permissions' => $permissions,
        'resolve' => $this->lpProxy->checkAccess($resolvePermissions, $context, false),
        ];

        return $checks;
    }

    /**
     * @internal
     *
     * @param string|array|bool $permissions
     * @param array             $context
     * @param array             $typeKeys
     *
     * @return array
     */
    protected function getPermissionNoBypassChecks($permissions, array $context, array $typeKeys): array
    {
        if (is_array($permissions) && array_key_exists('NO_BYPASS', $permissions)) {
            return $this->getPermissionChecks($permissions['NO_BYPASS'], $context, $typeKeys);
        }

        return [];
    }

    /**
     * @internal
     *
     * @param string|array|bool $permissions
     * @param array             $context
     *
     * @return bool
     */
    protected function getBypassedAccess($permissions, array $context): bool
    {
        $newPermissions = [false];
        if (is_array($permissions) && array_key_exists('NO_BYPASS', $permissions)) {
            $newPermissions['NO_BYPASS'] = $permissions['NO_BYPASS'];
        }

        try {
            return $this->lpProxy->checkAccess($newPermissions, $context);
        } catch (\Exception $e) {
        }

        return false;
    }
}
