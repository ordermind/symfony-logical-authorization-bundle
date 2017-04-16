<?php

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Ordermind\LogicalAuthorizationBundle\Event\AddPermissionsEvent;

class AddAppConfigPermissions {
  protected $config;

  public function __construct(array $config) {
    $this->config = $config;
  }

  public function onAddPermissions(AddPermissionsEvent $event) {
    if(!empty($this->config['permissions'])) {
      $event->setTree($event->mergePermissions([$event->getTree(), $this->config['permissions']]));
    }
  }
}
