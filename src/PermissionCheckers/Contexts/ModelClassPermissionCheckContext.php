<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\Contexts;

use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

class ModelClassPermissionCheckContext implements ContextHasUserInterface, ContextHasModelClassInterface
{
    /**
     * @var UserInterface|string
     */
    private $user;

    private string $modelClass;

    /**
     * @param UserInterface|string $user
     */
    public function __construct($user, string $modelClass)
    {
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
