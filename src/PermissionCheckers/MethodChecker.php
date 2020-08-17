<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers;

use InvalidArgumentException;
use Ordermind\LogicalPermissions\PermissionCheckerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use TypeError;

/**
 * Checks http method permissions.
 */
class MethodChecker implements PermissionCheckerInterface
{
    protected $requestStack;

    /**
     * @internal
     *
     * @param RequestStack $requestStack RequestStack
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritdoc}
     */
    public static function getName(): string
    {
        return 'method';
    }

    /**
     * Checks if the current request uses an allowed method.
     *
     * @param string $method
     * @param array  $context
     *
     * @return bool TRUE if the method is allowed or FALSE if it is not allowed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkPermission($method, $context): bool
    {
        if (!is_string($method)) {
            throw new TypeError('The method parameter must be a string.');
        }
        if (!$method) {
            throw new InvalidArgumentException('The method parameter cannot be empty.');
        }

        $currentRequest = $this->requestStack->getCurrentRequest();

        if (!$currentRequest) {
            return false;
        }

        return strcasecmp($currentRequest->getMethod(), $method) == 0;
    }
}
