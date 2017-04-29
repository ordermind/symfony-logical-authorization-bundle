<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException;

class ErrorHandler implements ErrorHandlerInterface {
  protected $logger;

  /**
   * @internal
   *
   * @param Psr\Log\LoggerInterface $logger (optional) A service for logging errors
   */
  public function __construct(LoggerInterface $logger = null) {
    $this->logger = $logger;
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
