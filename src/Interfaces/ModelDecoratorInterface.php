<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Interfaces;

/**
 * Decorator for models to allow for automatic permission checks during key actions.
 */
interface ModelDecoratorInterface
{
    /**
     * Gets the model for this decorator.
     *
     * @return ModelInterface
     */
    public function getModel(): ModelInterface;
}
