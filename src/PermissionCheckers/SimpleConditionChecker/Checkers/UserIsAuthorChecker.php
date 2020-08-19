<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\Checkers;

use InvalidArgumentException;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use Ordermind\LogicalAuthorizationBundle\PermissionCheckers\SimpleConditionChecker\SimpleConditionCheckerInterface;

/**
 * Checks whether a user if the author of a model.
 */
class UserIsAuthorChecker implements SimpleConditionCheckerInterface
{
    /**
     * {@inheritDoc}
     */
    public static function getName(): string
    {
        return 'user_is_author';
    }

    /**
     * Checks if the author of a model is the same as the user in a given context.
     *
     * @param array $context The context for evaluating the condition. The context must contain a 'user' key and a
     *                       'model' key. The user needs to implement
     *                       Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface and the model needs to
     *                       implement Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface. You can get the
     *                       current user by calling getCurrentUser() from the service 'logauth.service.helper'.
     *
     * @return bool TRUE if the user is the author of the model and FALSE if it isn't. There is no support for
     *              anonymous authors so if the user is anonymous it will always return FALSE.
     */
    public function checkCondition(array $context): bool
    {
        if (!isset($context['user'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'The context parameter must contain a "user" key to be able to evaluate the %s condition.',
                    $this->getName()
                )
            );
        }

        $user = $context['user'];
        if (is_string($user)) { //Anonymous user
            return false;
        }

        if (!($user instanceof UserInterface)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The user class must implement %s to be able to evaluate the %s condition.',
                    UserInterface::class,
                    $this->getName()
                )
            );
        }

        if (!isset($context['model'])) {
            throw new InvalidArgumentException(
                sprintf(
                    'The context parameter must contain a "model" key to be able to evaluate the %s condition.',
                    $this->getName()
                )
            );
        }

        $model = $context['model'];

        if (is_string($model) && class_exists($model)) {
            // A class string was passed which means that we don't have an actual object to evaluate. We interpret this
            // like the model does not have an author which means that we return false.
            return false;
        }

        if (!$model instanceof ModelInterface) {
            throw new InvalidArgumentException(
                sprintf(
                    'The model class must implement %s to be able to evaluate the %s condition.',
                    ModelInterface::class,
                    $this->getName()
                )
            );
        }

        $author = $model->getAuthor();

        // If there is no author it probably means that the entity is not yet persisted. In that case it's
        // reasonable to assume that the current user is the author.
        // If the lack of author is due to some other reason it's also reasonable to fall back to granting
        // permission because the reason for this condition is to protect models that do have an author against
        // other users.
        if (!$author) {
            return true;
        }

        if (!($author instanceof UserInterface)) {
            throw new InvalidArgumentException(
                sprintf(
                    'The author of the model must implement %s to be able to evaluate the %s condition.',
                    UserInterface::class,
                    $this->getName()
                )
            );
        }

        return $user->getIdentifier() === $author->getIdentifier();
    }
}
