<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\Contexts;

use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use TypeError;

class ModelClassPermissionCheckContext implements ContextHasUserInterface, ContextHasModelClassInterface
{
    /**
     * @var UserInterface|string
     */
    private $user;

    private string $modelClass;

    public function __construct($user, string $modelClass)
    {
        if (!is_string($user) && !($user instanceof UserInterface)) {
            throw new TypeError(
                'The user parameter has to be either a string or an object implementing ' . UserInterface::class
            );
        }

        $this->user = $user;
        $this->modelClass = $modelClass;
    }

    /**
     * {@inheritDoc}
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * {@inheritDoc}
     */
    public function getModelClass(): string
    {
        return $this->modelClass;
    }
}
