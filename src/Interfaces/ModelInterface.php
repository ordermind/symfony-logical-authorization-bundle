<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Interfaces;

use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

/**
 * Implement this interface in your model classes to make them compatible with all of the permission types of this bundle.
 */
interface ModelInterface
{
  /**
   * Sets the author of the model
   *
   * @param \Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface $user
   */
    public function setAuthor(UserInterface $user);

  /**
   * Gets the author of the model
   *
   * @return \Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface The author
   */
    public function getAuthor(): ?UserInterface;
}
