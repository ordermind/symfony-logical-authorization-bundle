<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelDecoratorInterface;
use Ordermind\LogicalAuthorizationBundle\ValueObjects\RawPermissionTree;
use Symfony\Component\Routing\RouterInterface;

/**
 * {@inheritDoc}
 */
class LogicalAuthorizationRoute implements LogicalAuthorizationRouteInterface
{
    protected LogicalAuthorizationInterface $logicalAuthorization;

    protected PermissionTreeBuilderInterface $treeBuilder;

    protected RouterInterface $router;

    protected HelperInterface $helper;

    public function __construct(
        LogicalAuthorizationInterface $logicalAuthorization,
        PermissionTreeBuilderInterface $treeBuilder,
        RouterInterface $router,
        HelperInterface $helper
    ) {
        $this->logicalAuthorization = $logicalAuthorization;
        $this->treeBuilder = $treeBuilder;
        $this->router = $router;
        $this->helper = $helper;
    }

    /**
     * {@inheritDoc}
     *
     * @return array
     */
    public function getAvailableRoutes($user = null): array
    {
        if ($user instanceof ModelDecoratorInterface) {
            $user = $user->getModel();
        }
        if (is_null($user)) {
            $user = $this->helper->getCurrentUser();
        }

        $routes = [];
        foreach ($this->router->getRouteCollection()->getIterator() as $routeName => $route) {
            if (!$this->checkRouteAccess($routeName, $user)) {
                continue;
            }

            if (!isset($routes['routes'])) {
                $routes['routes'] = [];
            }
            $routes['routes'][$route->getPath()] = $route->getPath();
        }

        $tree = $this->treeBuilder->getTree();
        if (!empty($tree['route_patterns'])) {
            foreach ($tree['route_patterns'] as $pattern => $permissions) {
                if (!$this->logicalAuthorization->checkAccess(new RawPermissionTree($permissions), ['user' => $user])) {
                    continue;
                }

                if (!isset($routes['route_patterns'])) {
                    $routes['route_patterns'] = [];
                }
                $routes['route_patterns'][$pattern] = $pattern;
            }
        }

        return $routes;
    }

    /**
     * {@inheritDoc}
     *
     * @return bool
     */
    public function checkRouteAccess(string $routeName, $user = null): bool
    {
        if ($user instanceof ModelDecoratorInterface) {
            $user = $user->getModel();
        }
        if (is_null($user)) {
            $user = $this->helper->getCurrentUser();
            if (is_null($user)) {
                $this->helper->logPermissionCheckForDebug(
                    true,
                    'route',
                    $routeName,
                    $user,
                    new RawPermissionTree([]),
                    [],
                    'No user was available during this permission check (not even an anonymous user). This usually '
                        . 'happens during unit testing. Access was therefore automatically granted.'
                );

                return true;
            }
        }

        if (!$routeName) {
            $this->helper->handleError(
                'Error checking route access: the routeName parameter cannot be empty.',
                ['route' => $routeName, 'user' => $user]
            );
            $this->helper->logPermissionCheckForDebug(
                false,
                'route',
                $routeName,
                $user,
                new RawPermissionTree([]),
                [],
                'There was an error checking the route access and access was therefore automatically denied. '
                    . 'Please refer to the error log for more information.'
            );

            return false;
        }
        if (!is_string($user) && !is_object($user)) {
            $this->helper->handleError(
                'Error checking route access: the user parameter must be either a string or an object.',
                ['route' => $routeName, 'user' => $user]
            );

            $this->helper->logPermissionCheckForDebug(
                false,
                'route',
                $routeName,
                $user,
                new RawPermissionTree([]),
                [],
                'There was an error checking the route access and access was therefore automatically denied. '
                    . 'Please refer to the error log for more information.'
            );

            return false;
        }

        $route = $this->router->getRouteCollection()->get($routeName);
        if (is_null($route)) {
            $this->helper->handleError(
                'Error checking route access: the route could not be found.',
                ['route' => $routeName, 'user' => $user]
            );

            $this->helper->logPermissionCheckForDebug(
                false,
                'route',
                $routeName,
                $user,
                new RawPermissionTree([]),
                [],
                'There was an error checking the route access and access was therefore automatically denied. '
                    . 'Please refer to the error log for more information.'
            );

            return false;
        }

        $rawPermissionTree = $this->getRoutePermissions($routeName);
        $context = ['route' => $routeName, 'user' => $user];
        $access = $this->logicalAuthorization->checkAccess($rawPermissionTree, $context);

        $this->helper->logPermissionCheckForDebug(
            $access,
            'route',
            $routeName,
            $user,
            $rawPermissionTree,
            $context
        );

        return $access;
    }

    /**
     * @internal
     */
    protected function getRoutePermissions(string $routeName): RawPermissionTree
    {
        //If permissions are defined for an individual route, pattern permissions are completely ignored for that route.
        $tree = $this->treeBuilder->getTree();

        //Check individual route permissions
        if (!empty($tree['routes']) && array_key_exists($routeName, $tree['routes'])) {
            return $tree['routes'][$routeName];
        }

        //Check pattern permissions
        if (!empty($tree['route_patterns'])) {
            $route = $this->router->getRouteCollection()->get($routeName);
            if ($route) {
                $routePath = $route->getPath();
                foreach ($tree['route_patterns'] as $pattern => $permissions) {
                    if (preg_match("@$pattern@", $routePath)) {
                        return new RawPermissionTree($permissions);
                    }
                }
            }
        }

        return new RawPermissionTree([]);
    }
}
