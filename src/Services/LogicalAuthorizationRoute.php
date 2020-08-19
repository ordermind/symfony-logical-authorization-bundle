<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\DataCollector\CollectorInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelDecoratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * {@inheritdoc}
 */
class LogicalAuthorizationRoute implements LogicalAuthorizationRouteInterface
{
    /**
     * @var LogicalAuthorizationInterface
     */
    protected $logicalAuthorization;

    /**
     * @var PermissionTreeBuilderInterface
     */
    protected $treeBuilder;

    /**
     * @var SRouterInterface
     */
    protected $router;

    /**
     * @var HelperInterface
     */
    protected $helper;

    /**
     * @var CollectorInterface
     */
    protected $debugCollector;

    /**
     * @internal
     *
     * @param LogicalAuthorizationInterface  $logicalAuthorization
     * @param PermissionTreeBuilderInterface $treeBuilder
     * @param RouterInterface                $router
     * @param HelperInterface                $helper
     * @param CollectorInterface             $debugCollector
     */
    public function __construct(
        LogicalAuthorizationInterface $logicalAuthorization,
        PermissionTreeBuilderInterface $treeBuilder,
        RouterInterface $router,
        HelperInterface $helper,
        CollectorInterface $debugCollector = null
    ) {
        $this->logicalAuthorization = $logicalAuthorization;
        $this->treeBuilder = $treeBuilder;
        $this->router = $router;
        $this->helper = $helper;
        $this->debugCollector = $debugCollector;
    }

    /**
     * {@inheritdoc}
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
                if (!$this->logicalAuthorization->checkAccess($permissions, ['user' => $user])) {
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
     * {@inheritdoc}
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
                if (!is_null($this->debugCollector)) {
                    $this->debugCollector->addPermissionCheck(
                        true,
                        'route',
                        $routeName,
                        $user,
                        [],
                        [],
                        'No user was available during this permission check (not even an anonymous user). This usually '
                            . 'happens during unit testing. Access was therefore automatically granted.'
                    );
                }

                return true;
            }
        }

        if (!$routeName) {
            $this->helper->handleError(
                'Error checking route access: the routeName parameter cannot be empty.',
                ['route' => $routeName, 'user' => $user]
            );
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    false,
                    'route',
                    $routeName,
                    $user,
                    [],
                    [],
                    'There was an error checking the route access and access was therefore automatically denied. '
                        . 'Please refer to the error log for more information.'
                );
            }

            return false;
        }
        if (!is_string($user) && !is_object($user)) {
            $this->helper->handleError(
                'Error checking route access: the user parameter must be either a string or an object.',
                ['route' => $routeName, 'user' => $user]
            );
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    false,
                    'route',
                    $routeName,
                    $user,
                    [],
                    [],
                    'There was an error checking the route access and access was therefore automatically denied. '
                        . 'Please refer to the error log for more information.'
                );
            }

            return false;
        }

        $route = $this->router->getRouteCollection()->get($routeName);
        if (is_null($route)) {
            $this->helper->handleError(
                'Error checking route access: the route could not be found.',
                ['route' => $routeName, 'user' => $user]
            );
            if (!is_null($this->debugCollector)) {
                $this->debugCollector->addPermissionCheck(
                    false,
                    'route',
                    $routeName,
                    $user,
                    [],
                    [],
                    'There was an error checking the route access and access was therefore automatically denied. '
                        . 'Please refer to the error log for more information.'
                );
            }

            return false;
        }

        $permissions = $this->getRoutePermissions($routeName);
        $context = ['route' => $routeName, 'user' => $user];
        $access = $this->logicalAuthorization->checkAccess($permissions, $context);

        if (!is_null($this->debugCollector)) {
            $this->debugCollector->addPermissionCheck($access, 'route', $routeName, $user, $permissions, $context);
        }

        return $access;
    }

    /**
     * @internal
     *
     * @param string $routeName
     *
     * @return array|string|bool
     */
    protected function getRoutePermissions(string $routeName)
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
                        return $permissions;
                    }
                }
            }
        }

        return [];
    }
}
