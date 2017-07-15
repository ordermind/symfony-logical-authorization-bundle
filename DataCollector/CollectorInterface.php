<?php

namespace Ordermind\LogicalAuthorizationBundle\DataCollector;

interface CollectorInterface {
  public function addPermissionCheckAttempt($type, $item, $user);
  public function addPermissionCheck($type, $name, $user, $permissions, $context);
}
