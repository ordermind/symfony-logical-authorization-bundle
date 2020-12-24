<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Interfaces;

use Symfony\Component\Security\Core\User\UserInterface as SecurityUserInterface;

/**
 * Implement this interface in your user class to make it compatible with this bundle.
 */
interface UserInterface extends SecurityUserInterface
{
    /**
     * Gets the unique identifier value of this user.
     *
     * @return int|string
     */
    public function getIdentifier();

    /**
     * Sets the bypass access flag for this user.
     *
     * @param bool $bypassAccess TRUE if the user should be able to bypass access checks, or FALSE if not
     */
    public function setBypassAccess(bool $bypassAccess);

    /**
     * Gets the value of the bypass access flag for this user.
     */
    public function getBypassAccess(): bool;
}
