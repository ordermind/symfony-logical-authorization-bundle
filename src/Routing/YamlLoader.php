<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Ordermind\LogicalPermissions\PermissionTree\RawPermissionTree;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;
use TypeError;

/**
 * {@inheritDoc}
 */
class YamlLoader extends YamlFileLoader
{
    /**
     * {@inheritDoc}
     */
    public function load($file, ?string $type = null): RouteCollection
    {
        if (!is_string($file)) {
            throw new TypeError('The file parameter must be a string.');
        }

        return parent::load($file, 'yaml');
    }

    /**
     * {@inheritDoc}
     */
    public function supports($resource, ?string $type = null): bool
    {
        if (!is_string($resource)) {
            return false;
        }

        if ('logauth_yml' !== $type && 'logauth_yaml' !== $type) {
            return false;
        }

        return in_array(pathinfo($resource, PATHINFO_EXTENSION), ['yml', 'yaml'], true);
    }

    /**
     * {@inheritDoc}
     */
    protected function validate($config, string $name, string $path)
    {
        if (!is_array($config)) {
            throw new TypeError('The config parameter must be an array');
        }

        unset($config['permissions']);
        parent::validate($config, $name, $path);
    }

    /**
     * {@inheritDoc}
     */
    protected function parseRoute(RouteCollection $collection, string $name, array $config, string $path)
    {
        $defaults = isset($config['defaults']) ? $config['defaults'] : [];
        $requirements = isset($config['requirements']) ? $config['requirements'] : [];
        $options = isset($config['options']) ? $config['options'] : [];
        $host = isset($config['host']) ? $config['host'] : null;
        $schemes = isset($config['schemes']) ? $config['schemes'] : [];
        $methods = isset($config['methods']) ? $config['methods'] : [];
        $condition = isset($config['condition']) ? $config['condition'] : null;
        $permissions = isset($config['permissions']) ? $config['permissions'] : null;

        $route = new Route(
            $config['path'],
            $defaults,
            $requirements,
            $options,
            $host,
            $schemes,
            $methods,
            $condition,
            !is_null($permissions) ? new RawPermissionTree($permissions) : null
        );

        $collection->add($name, $route);
    }
}
