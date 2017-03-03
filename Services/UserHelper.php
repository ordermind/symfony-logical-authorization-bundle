<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class UserHelper implements UserHelperInterface {
  protected $tokenStorage;

  public function __construct(TokenStorageInterface $tokenStorage) {
    $this->tokenStorage = $tokenStorage;
  }

  public function getCurrentUser() {
    $token = $this->tokenStorage->getToken();
    if(!is_null($token)) {
      return $token->getUser();
    }
    return null;
  }
}
