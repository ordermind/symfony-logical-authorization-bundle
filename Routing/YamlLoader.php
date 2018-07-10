<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;

use Ordermind\LogicalAuthorizationBundle\Routing\Route;

/**
 * {@inheritdoc}
 */
class YamlLoader extends YamlFileLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($file, $type = null): RouteCollection
    {
        return parent::load($file, 'yaml');
    }

    /**
     * {@inheritdoc}
     */
    public function supports($resource, $type = null): bool
    {
        return is_string($resource) && in_array(pathinfo($resource, PATHINFO_EXTENSION), array('yml', 'yaml'), true) && ('logauth_yml' === $type || 'logauth_yaml' === $type);
    }

    /**
     * {@inheritdoc}
     */
    protected function validate($config, $name, $path)
    {
        unset($config['permissions']);
        parent::validate($config, $name, $path);
    }

    /**
     * {@inheritdoc}
     */
    protected function parseRoute(RouteCollection $collection, $name, array $config, $path)
    {
        $defaults = isset($config['defaults']) ? $config['defaults'] : array();
        $requirements = isset($config['requirements']) ? $config['requirements'] : array();
        $options = isset($config['options']) ? $config['options'] : array();
        $host = isset($config['host']) ? $config['host'] : '';
        $schemes = isset($config['schemes']) ? $config['schemes'] : array();
        $methods = isset($config['methods']) ? $config['methods'] : array();
        $condition = isset($config['condition']) ? $config['condition'] : null;
        $permissions = isset($config['permissions']) ? $config['permissions'] : null;

        $route = new Route($config['path'], $defaults, $requirements, $options, $host, $schemes, $methods, $condition, $permissions);

        $collection->add($name, $route);
    }
}
