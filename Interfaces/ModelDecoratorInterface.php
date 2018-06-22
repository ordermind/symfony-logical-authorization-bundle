<?php

namespace Ordermind\LogicalAuthorizationBundle\Interfaces;

interface ModelDecoratorInterface {
  /**
   * Gets the model for this decorator
   *
   * @return \Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface The model
   */
  public function getModel();
}
