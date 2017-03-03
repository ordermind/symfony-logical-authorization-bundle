<?php

namespace Ordermind\LogicalAuthorizationBundle\Interfaces;

use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

interface ModelInterface {
  public function setAuthor(UserInterface $user);
  public function getAuthor();
}
