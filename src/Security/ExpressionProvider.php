<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Security;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationRouteInterface;
use Symfony\Component\ExpressionLanguage\ExpressionFunction;
use Symfony\Component\ExpressionLanguage\ExpressionFunctionProviderInterface;

/**
 * Expression Function Provider for integration with access control.
 */
class ExpressionProvider implements ExpressionFunctionProviderInterface
{
    /**
     * @var LogicalAuthorizationRouteInterface
     */
    protected $laRoute;

    public function __construct(LogicalAuthorizationRouteInterface $laRoute)
    {
        $this->laRoute = $laRoute;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new ExpressionFunction(
                'logauth_route',
                function () {
                    return
                        '$routeName = $request->get(\'_route\'); return $routeName ? '
                        . '$this->get(\'logauth.service.logauth_route\')->checkRouteAccess($routeName) : true;';
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
