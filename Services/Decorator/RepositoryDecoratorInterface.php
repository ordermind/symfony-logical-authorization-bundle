<?php

namespace Ordermind\LogicalAuthorizationBundle\Services\Decorator;

use Ordermind\DoctrineDecoratorBundle\Services\Decorator\RepositoryDecoratorInterface as RepositoryDecoratorInterfaceBase;

interface RepositoryDecoratorInterface extends RepositoryDecoratorInterfaceBase {

  /**
   * Creates a new model decorator
   *
   * Any parameters that are provided to this method will be passed on to the model constructor.
   * If the model implements Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface and the current user implements Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface, it will automatically set the model's author to the current user.
   * If the current user is not authorized to create the target model, it will not be created and NULL will be returned. Otherwise the created model decorator will be returned.
   *
   * @return Ordermind\LogicalAuthorizationBundle\Services\Decorator\ModelDecoratorInterface|NULL
   */
  public function create();
}
