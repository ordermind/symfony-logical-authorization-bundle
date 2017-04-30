<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

interface HelperInterface {

  /**
   * Gets the current user
   *
   * @return mixed If the current user is authenticated the user object is returned. If the current user is anonymous the string "anon." is returned. If no current user can be found, NULL is returned.
   */
  public function getCurrentUser();

  /**
   * @internal Extracts a model from a model decorator if applicable. The purpose is to reduce memory footprint in case of an exception.
   *
   * @param mixed $modelDecorator The model decorator to extract the model from. If the parameter is not a model decorator it will simply be returned as is.
   *
   * @return mixed If a model decorator is provided, the model for the decorator is returned. Otherwise the input parameter will be returned.
   */
  public function getRidOfDecorator($modelDecorator);

  /**
   * @internal Logs an error if a logging service is available. Otherwise it outputs the error as a Ordermind\LogicalAuthorizationBundle\Exceptions\LogicalAuthorizationException.
   *
   * @param string $message The error message
   * @param array $context The context for the error
   */
  public function handleError($message, $context);
}
