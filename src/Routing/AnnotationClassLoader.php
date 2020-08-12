<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Ordermind\LogicalAuthorizationBundle\Annotation\Routing\Permissions;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;
use Symfony\Component\Routing\Route as RouteBase;

/**
 * {@inheritdoc}
 */
class AnnotationClassLoader extends AnnotatedRouteControllerLoader
{
    /**
     * {@inheritdoc}
     */
    protected function configureRoute(RouteBase $route, \ReflectionClass $class, \ReflectionMethod $method, $annot)
    {
        parent::configureRoute($route, $class, $method, $annot);
        foreach ($this->reader->getMethodAnnotations($method) as $configuration) {
            if ($configuration instanceof Permissions) {
                $route->setPermissions($configuration->getPermissions());
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function createRoute(
        $path,
        $defaults,
        $requirements,
        $options,
        $host,
        $schemes,
        $methods,
        $condition
    ): Route {
        return new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
    }
}
