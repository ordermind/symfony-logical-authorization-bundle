<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Security;

use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;
use Symfony\Component\HttpFoundation\Request;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationRouteInterface;

/**
 * Expression Function Provider for integration with access control
 */
class ExpressionProvider implements ExpressionFunctionProviderInterface
{
    protected $laRoute;

  /**
   * @internal
   *
   * @param \Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationRouteInterface $laRoute LogicalAuthorizationRoute service for checking route access
   */
    public function __construct(LogicalAuthorizationRouteInterface $laRoute)
    {
        $this->laRoute = $laRoute;
    }

  /**
   * {@inheritdoc}
   */
    public function getFunctions()
    {
        return [
            new ExpressionFunction(
                'logauth_route',
                function () {
                    return '$routeName = $request->get(\'_route\'); return $routeName ? $this->get(\'logauth.service.logauth_route\')->checkRouteAccess($routeName) : true;';
                },
                function (array $arguments) {
                    $request = $arguments['request'];
                    $routeName = $request->get('_route');
                    if ($routeName) {
                        return $this->laRoute->checkRouteAccess($routeName);
                    }

                    return true;
                }
            ),
        ];
    }
}
