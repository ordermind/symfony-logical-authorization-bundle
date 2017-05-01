<?php

namespace Ordermind\LogicalAuthorizationBundle\Services\Factory;

/**
 * Factory for Ordermind\LogicalAuthorizationBundle\Services\Decorator\RepositoryDecoratorInterface
 */
interface RepositoryDecoratorFactoryInterface extends \Ordermind\DoctrineDecoratorBundle\Services\Factory\RepositoryDecoratorFactoryInterface
{

  /**
   * Sets the helper service
   *
   * @param Ordermind\LogicalAuthorizationBundle\Services\HelperInterface $helper The helper service to use for this factory
   */
    public function setHelper(\Ordermind\LogicalAuthorizationBundle\Services\HelperInterface $helper);

  /**
   * Gets a new repository decorator
   *
   * @param string $class The model class to use for the new repository decorator
   *
   * @return Ordermind\LogicalAuthorizationBundle\Services\Decorator\RepositoryDecoratorInterface A new repository decorator
   */
    public function getRepositoryDecorator($class);
}

