<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use function Ordermind\LogicalAuthorizationBundle\Helpers\iterable_to_array;

use Ordermind\LogicalAuthorizationBundle\PermissionProviders\PermissionProviderInterface;
use Ordermind\LogicalPermissions\PermissionCheckerLocatorInterface;

/**
 * Collects the permissions from registered permission providers.
 */
class PermissionCollector
{
    /**
     * @var iterable<PermissionProviderInterface>
     */
    private iterable $permissionProviders;

    private PermissionCheckerLocatorInterface $locator;

    /**
     * @internal
     */
    public function __construct(
        iterable $permissionProviders,
        PermissionCheckerLocatorInterface $locator
    ) {
        $this->permissionProviders = $permissionProviders;
        $this->locator = $locator;
    }

    /**
     * Merges the permission trees of all registered permission providers and returns them.
     */
    public function getPermissionTree(): array
    {
        $validPermissionKeys = $this->locator->getValidPermissionTreeKeys();

        return $this->mergeTrees(
            array_map(function (PermissionProviderInterface $permissionProvider): array {
                return $permissionProvider->getPermissionTree();
            }, iterable_to_array($this->permissionProviders)),
            $validPermissionKeys
        );
    }

    /**
     * @internal
     */
    protected function mergeTrees(array $trees, array $validPermissionKeys): array
    {
        if (count($trees) == 0) {
            return [];
        }

        $tree1 = array_shift($trees);
        while (count($trees)) {
            $tree2 = array_shift($trees);
            foreach ($tree2 as $key => $value) {
                if (in_array($key, $validPermissionKeys)) {
                    $tree1 = $tree2;
                    break;
                }
                if (isset($tree1[$key]) && is_array($value)) {
                    $tree1[$key] = $this->mergeTrees([$tree1[$key], $tree2[$key]], $validPermissionKeys);
                    continue;
                }
                $tree1[$key] = $value;
            }
        }

        return $tree1;
    }
}
