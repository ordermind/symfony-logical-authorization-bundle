<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Twig;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationRouteInterface;

class LogicalAuthorizationExtension extends \Twig_Extension
{
    protected $laRoute;
    protected $laModel;

    public function __construct(LogicalAuthorizationRouteInterface $laRoute, LogicalAuthorizationModelInterface $laModel)
    {
        $this->laRoute = $laRoute;
        $this->laModel = $laModel;
    }

  /**
   * {@inheritdoc}
   */
    public function getFunctions(): array
    {
        return array(
        new \Twig_SimpleFunction('logauth_check_route_access', array($this, 'checkRouteAccess')),
        new \Twig_SimpleFunction('logauth_check_model_access', array($this, 'checkModelAccess')),
        new \Twig_SimpleFunction('logauth_check_field_access', array($this, 'checkFieldAccess')),
        );
    }

  /**
   * Twig extension callback for checking route access
   *
   * If something goes wrong an error will be logged and the method will return FALSE. If no permissions are defined for the provided route it will return TRUE.
   *
   * @param string        $route_name The name of the route
   * @param object|string $user       (optional)  Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
   *
   * @return bool TRUE if access is granted or FALSE if access is denied.
   */
    public function checkRouteAccess(string $route_name, $user = null): bool
    {
        return $this->laRoute->checkRouteAccess($route_name, $user);
    }

  /**
   * Twig extension callback for checking model access
   *
   * If something goes wrong an error will be logged and the method will return FALSE. If no permissions are defined for this action on the provided model it will return TRUE.
   *
   * @param object|string $model  A model object or class string.
   * @param string        $action Examples of model actions are "create", "read", "update" and "delete".
   * @param object|string $user   (optional) Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
   *
   * @return bool TRUE if access is granted or FALSE if access is denied.
   */
    public function checkModelAccess($model, string $action, $user = null): bool
    {
        return $this->laModel->checkModelAccess($model, $action, $user);
    }

  /**
   * Twig extension callback for checking field access
   *
   * If something goes wrong an error will be logged and the method will return FALSE. If no permissions are defined for this action on the provided field and model it will return TRUE.
   *
   * @param object|string $model      A model object or class string.
   * @param string        $field_name The name of the field.
   * @param string        $action     Examples of field actions are "get" and "set".
   * @param object|string $user       (optional) Either a user object or a string to signify an anonymous user. If no user is supplied, the current user will be used.
   *
   * @return bool TRUE if access is granted or FALSE if access is denied.
   */
    public function checkFieldAccess($model, string $field_name, string $action, $user = null): bool
    {
        return $this->laModel->checkFieldAccess($model, $field_name, $action, $user);
    }
}
