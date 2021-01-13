<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\Contexts;

use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use TypeError;

class ModelPermissionCheckContext implements ContextHasUserInterface, ContextHasModelInterface
{
    /**
     * @var UserInterface|string
     */
    private $user;

    private ModelInterface $model;

    public function __construct($user, ModelInterface $model)
    {
        if (!is_string($user) && !($user instanceof UserInterface)) {
            throw new TypeError(
                'The user parameter has to be either a string or an object implementing ' . UserInterface::class
            );
        }

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
