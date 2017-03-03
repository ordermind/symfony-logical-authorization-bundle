<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Role;

use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\PermissionTypeInterface;

class Role implements PermissionTypeInterface {
  public function getName() {
    return 'role';
  }
  public function checkPermission($role, $context) {
    $user = $context['user'];
    if(is_string($user)) { //Anonymous user
      return false;
    }
    if(!($user instanceof SecurityUserInterface)) {
      throw new \InvalidArgumentException('The user class must implement Symfony\Component\Security\Core\User\UserInterface to be able to evaluate the user role.');
    }
    $roles = $user->getRoles();
    foreach($roles as $thisRole) {
      $strRole = '';
      if(is_string($thisRole)) {
        $strRole = $thisRole;
      }
      else {
        $strRole = $thisRole->getRole();
      }
      if($role === $strRole) {
        return true;
      }
    }
    return false;
  }
}
