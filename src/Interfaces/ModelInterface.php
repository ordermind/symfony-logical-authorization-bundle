<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Interfaces;

/**
 * Implement this interface in your model classes to make them compatible with all of the permission types of this
 * bundle.
 */
interface ModelInterface
{
    /**
     * Sets the author of the model.
     *
     * @param UserInterface $user
     */
    public function setAuthor(UserInterface $user);

    /**
     * Gets the author of the model.
     *
     * @return UserInterface
     */
    public function getAuthor(): ?UserInterface;
}
