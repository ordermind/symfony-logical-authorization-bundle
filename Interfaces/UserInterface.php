<?php

namespace Ordermind\LogicalAuthorizationBundle\Interfaces;

use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;

interface UserInterface extends SecurityUserInterface {
  public function getId();
  public function setBypassAccess($bypassAccess);
  public function getBypassAccess();
}
