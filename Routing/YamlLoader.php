<?php

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\RouteCollection;

use Ordermind\LogicalAuthorizationBundle\Routing\Route;

class YamlLoader extends YamlFileLoader {
  protected function validate($config, $name, $path) {
    unset($config['logauth']);
    parent::validate($config, $name, $path);
  }

  public function load($file, $type = null) {
    return parent::load($file, 'yml');
  }

  public function supports($resource, $type = null) {
    return 'logauth_yml' === $type || 'logauth_yaml' === $type;
  }

  protected function parseRoute(RouteCollection $collection, $name, array $config, $path) {
    $defaults = isset($config['defaults']) ? $config['defaults'] : array();
    $requirements = isset($config['requirements']) ? $config['requirements'] : array();
    $options = isset($config['options']) ? $config['options'] : array();
    $host = isset($config['host']) ? $config['host'] : '';
    $schemes = isset($config['schemes']) ? $config['schemes'] : array();
    $methods = isset($config['methods']) ? $config['methods'] : array();
    $condition = isset($config['condition']) ? $config['condition'] : null;
    $logauth = isset($config['logauth']) ? $config['logauth'] : null;

    $route = new Route($config['path'], $defaults, $requirements, $options, $host, $schemes, $methods, $condition, $logauth);

    $collection->add($name, $route);
  }
}