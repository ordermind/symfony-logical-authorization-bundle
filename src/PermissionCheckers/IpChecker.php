<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers;

use InvalidArgumentException;
use Ordermind\LogicalPermissions\PermissionCheckerInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Checks ip address permissions.
 */
class IpChecker implements PermissionCheckerInterface
{
    protected RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'ip';
    }

    /**
     * Checks if the current request comes from an approved ip address.
     *
     * @param object $context
     *
     * @return bool TRUE if the ip address is allowed or FALSE if it is not allowed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkPermission(string $ipAddress, $context): bool
    {
        if (!$ipAddress) {
            throw new InvalidArgumentException('The ipAddress parameter cannot be empty.');
        }

        $currentRequest = $this->requestStack->getCurrentRequest();

        if (!$currentRequest) {
            return false;
        }

        return IpUtils::checkIp($currentRequest->getClientIp(), $ipAddress);
    }
}
