<?php

namespace Ordermind\LogicalAuthorizationBundle\DataCollector;

interface CollectorInterface {
  public function getPermissionTree();
  public function getLog();
  public function addPermissionCheck($type, $name, $user, $permissions, $context);
}
