<?php

namespace Ordermind\LogicalAuthorizationBundle\Interfaces;

use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;

interface UserInterface extends SecurityUserInterface {
  /**
   * Gets the id of this user
   *
   * @return mixed id
   */
  public function getId();

  /**
   * Sets the bypass access flag for this user
   *
   * @param bool $bypassAccess TRUE if the user should be able to bypass access checks, or FALSE if not.
   */
  public function setBypassAccess($bypassAccess);

  /**
   * Gets the bypass access flag for this user
   *
   * @return bool The value of the bypass access flag
   */
  public function getBypassAccess();
}
