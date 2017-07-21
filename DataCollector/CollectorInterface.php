<?php

namespace Ordermind\LogicalAuthorizationBundle\DataCollector;

interface CollectorInterface {
  public function getPermissionTree();
  public function getLog();
  public function addPermissionCheck($access, $type, $name, $user, $permissions, $context, $message = '');
}
