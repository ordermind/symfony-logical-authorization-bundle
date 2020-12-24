<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Twig;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationRouteInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * {@inheritDoc}
 */
class LogicalAuthorizationExtension extends AbstractExtension
{
    protected LogicalAuthorizationRouteInterface $laRoute;

    protected LogicalAuthorizationModelInterface $laModel;

    public function __construct(
        LogicalAuthorizationRouteInterface $laRoute,
        LogicalAuthorizationModelInterface $laModel
    ) {
        $this->laRoute = $laRoute;
        $this->laModel = $laModel;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('logauth_check_route_access', [$this, 'checkRouteAccess']),
            new TwigFunction('logauth_check_model_access', [$this, 'checkModelAccess']),
            new TwigFunction('logauth_check_field_access', [$this, 'checkFieldAccess']),
        ];
    }

    /**
     * Twig extension callback for checking route access.
     *
     * If something goes wrong an error will be logged and the method will return FALSE. If no permissions are defined
     * for the provided route it will return TRUE.
     *
     * @param string        $routeName
     * @param object|string $user      (optional)  Either a user object or a string to signify an anonymous user. If no
     *                                 user is supplied, the current user will be used.
     *
     * @return bool TRUE if access is granted or FALSE if access is denied
     */
    public function checkRouteAccess(string $routeName, $user = null): bool
    {
        return $this->laRoute->checkRouteAccess($routeName, $user);
    }

    /**
     * Twig extension callback for checking model access.
     *
     * If something goes wrong an error will be logged and the method will return FALSE. If no permissions are defined
     * for this action on the provided model it will return TRUE.
     *
     * @param object|string $model  a model object or class string
     * @param string        $action examples of model actions are "create", "read", "update" and "delete"
     * @param object|string $user   (optional) Either a user object or a string to signify an anonymous user. If no
     *                              user is supplied, the current user will be used.
     *
     * @return bool TRUE if access is granted or FALSE if access is denied
     */
    public function checkModelAccess($model, string $action, $user = null): bool
    {
        return $this->laModel->checkModelAccess($model, $action, $user);
    }

    /**
     * Twig extension callback for checking field access.
     *
     * If something goes wrong an error will be logged and the method will return FALSE. If no permissions are defined
     * for this action on the provided field and model it will return TRUE.
     *
     * @param object|string $model     a model object or class string
     * @param string        $fieldName the name of the field
     * @param string        $action    examples of field actions are "get" and "set"
     * @param object|string $user      (optional) Either a user object or a string to signify an anonymous user. If no
     *                                 user is supplied, the current user will be used.
     *
     * @return bool TRUE if access is granted or FALSE if access is denied
     */
    public function checkFieldAccess($model, string $fieldName, string $action, $user = null): bool
    {
        return $this->laModel->checkFieldAccess($model, $fieldName, $action, $user);
    }
}
