<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\Contexts;

use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

class ModelClassPermissionCheckContext implements ContextHasUserInterface
{
    /**
     * @var UserInterface|string
     */
    private $user;

    /**
     * @param UserInterface|string $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * {@inheritDoc}
     */
    public function getUser()
    {
        return $this->user;
    }
}
