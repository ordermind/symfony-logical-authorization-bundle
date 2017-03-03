<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\FlagInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

class BypassAccess implements FlagInterface {
  public function getName() {
    return 'bypass_access';
  }
  public function checkFlag($context) {
    $user = $context['user'];
    if(is_string($user)) { //Anonymous user
      return false;
    }
    if(!($user instanceof UserInterface)) {
      throw new \InvalidArgumentException('The user class must implement Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface to be able to evaluate the ' . $this->getName() . ' flag.');
    }
    return $user->getBypassAccess();
  }
}
