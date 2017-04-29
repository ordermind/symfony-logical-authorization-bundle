<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Psr\Log\LoggerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Ordermind\LogicalAuthorizationBundle\Services\ModelManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException;

class Helper implements HelperInterface {

  protected $tokenStorage;
  protected $logger;

  /**
   * @internal
   *
   * @param Psr\Log\LoggerInterface $logger (optional) A service for logging errors
   */
  public function __construct(TokenStorageInterface $tokenStorage, LoggerInterface $logger = null) {
    $this->tokenStorage = $tokenStorage;
    $this->logger = $logger;
  }

  public function getCurrentUser() {
    $token = $this->tokenStorage->getToken();
    if(!is_null($token)) {
      return $token->getUser();
    }
    return null;
  }

   /**
   * {@inheritdoc}
   */
  public function getRidOfManager($modelManager) {
    if(!is_object($modelManager)) return $modelManager;
    if(!($modelManager instanceof ModelManagerInterface)) return $modelManager;
    return $modelManager->getModel();
  }

  /**
   * {@inheritdoc}
   */
  public function handleError($message, $context) {
    if(!is_array($context)) {
      throw new \InvalidArgumentException('The context parameter must be an array. Current type is ' . gettype($context) . '.');
    }

    if(!is_null($this->logger)) {
      $this->logger->error($message, $context);
    }
    else {
      foreach($context as $key => $value) {
        $message .= ", $key: " . print_r($value, true);
      }
      throw new LogicalAuthorizationException($message);
    }
  }
}
