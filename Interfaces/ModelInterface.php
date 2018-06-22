<?php

namespace Ordermind\LogicalAuthorizationBundle\Interfaces;

use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

interface ModelInterface {
  /**
   * Sets the author of the model
   *
   * @param \Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface The author
   */
  public function setAuthor(UserInterface $user);

  /**
   * Gets the author of the model
   *
   * @return \Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface The author
   */
  public function getAuthor();
}
