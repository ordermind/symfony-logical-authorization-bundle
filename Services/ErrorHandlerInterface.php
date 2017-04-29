<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface ErrorHandlerInterface {

  /**
   * @internal Logs an error if a logging service is available. Otherwise it outputs the error as a Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException.
   *
   * @param string $message The error message
   * @param array $context The context for the error
   */
  public function handleError($message, $context);
}
