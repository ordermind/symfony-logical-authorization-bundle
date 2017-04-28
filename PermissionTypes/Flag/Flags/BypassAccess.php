<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\FlagInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

class BypassAccess implements FlagInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'bypass_access';
  }

  /**
   * Checks if this flag is on or off in the current context.
   *
   * @param array context The context for evaluating the flag. The context must contain a 'user' key which references either a user string (to signify an anonymous user) or an object implementing Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface. You can get the current user by calling getCurrentUser() from the service 'ordermind_logical_authorization.service.user_helper'.
   *
   * @return bool TRUE if access can be bypassed or FALSE if access can't be bypassed.
   */
  public function checkFlag($context) {
    if(!is_array($context)) {
      throw new \InvalidArgumentException('The context parameter must be an array. Current type is ' . gettype($context) . '.');
    }
    if(!isset($context['user'])) {
      throw new \InvalidArgumentException('The context parameter must contain a "user" key to be able to evaluate the ' . $this->getName() . ' flag.');
    }

    $user = $context['user'];
    if(is_string($user)) { //Anonymous user
      return false;
    }
    if(!($user instanceof UserInterface)) {
      throw new \InvalidArgumentException('The user class must implement Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface to be able to evaluate the ' . $this->getName() . ' flag.');
    }

    $access = $user->getBypassAccess();
    if(!is_bool($access)) {
      throw new \UnexpectedValueException('The method getBypassAccess() on the user object must return a boolean. Returned type is ' . gettype($access) . '.');
    }

    return $access;
  }
}
