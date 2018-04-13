<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags;

class UserHasAccount implements FlagInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'user_has_account';
  }

  /**
   * Checks if a user has an account in a given context.
   *
   * @param array $context The context for evaluating the flag. The context must contain a 'user' key so that the user can be evaluated. You can get the current user by calling getCurrentUser() from the service 'logauth.service.helper'.
   *
   * @return bool TRUE if the user is not a string and FALSE if the user is a string and thereby anonymous
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
    return true;
  }
}