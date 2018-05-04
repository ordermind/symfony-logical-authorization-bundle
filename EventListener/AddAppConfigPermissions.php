<?php

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEventInterface;

class AddAppConfigPermissions {
  protected $config;

  public function __construct(array $config) {
    $this->config = $config;
  }

  public function onAddPermissions(AddPermissionsEventInterface $event) {
    if(!empty($this->config['permissions'])) {
      $event->insertTree($this->config['permissions']);
    }
  }
}
