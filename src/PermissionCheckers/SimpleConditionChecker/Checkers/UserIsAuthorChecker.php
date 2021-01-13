<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\Checkers;

use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\Contexts\ContextHasUserAndModelInterface;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\SimpleConditionCheckerInterface;

/**
 * Checks whether a user if the author of a model.
 */
class UserIsAuthorChecker implements SimpleConditionCheckerInterface
{
    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'user_is_author';
    }

    /**
     * Checks if the author of a model is the same as the user in a given context.
     *
     * @param array $context The context for evaluating the condition. The context must contain a 'user' key and a
     *
     * @return bool TRUE if the user is the author of the model and FALSE if it isn't. There is no support for
     *              anonymous authors so if the user is anonymous it will always return FALSE.
     */
    public function checkCondition(ContextHasUserAndModelInterface $context): bool
    {
        $user = $context->getUser();
        if (is_string($user)) { // Anonymous user
            return false;
        }

        $model = $context->getModel();
        $author = $model->getAuthor();

        // If there is no author it probably means that the entity is not yet persisted. In that case it's
        // reasonable to assume that the current user is the author.
        // If the lack of author is due to some other reason it's also reasonable to fall back to granting
        // permission because the reason for this condition is to protect models that do have an author against
        // other users.
        if (!$author) {
            return true;
        }

        return $user->getIdentifier() === $author->getIdentifier();
    }
}
