<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers;

use InvalidArgumentException;
use Ordermind\LogicalPermissions\PermissionCheckerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Checks http method permissions.
 */
class MethodChecker implements PermissionCheckerInterface
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritDoc}
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
    public function checkPermission(string $method, $context): bool
    {
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
