<?php

namespace Ordermind\LogicalAuthorizationBundle\Routing;

use Symfony\Component\Routing\Loader\YamlFileLoader;

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
}