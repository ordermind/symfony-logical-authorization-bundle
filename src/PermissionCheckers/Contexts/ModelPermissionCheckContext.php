<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\Contexts;

use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

class ModelPermissionCheckContext implements ContextHasUserInterface, ContextHasModelInterface
{
    /**
     * @var UserInterface|string
     */
    private $user;

    private ModelInterface $model;

    /**
     * @param UserInterface|string $user
     */
    public function __construct($user, ModelInterface $model)
    {
        $this->user = $user;
        $this->model = $model;
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
    public function getModel(): ModelInterface
    {
        return $this->model;
    }
}
