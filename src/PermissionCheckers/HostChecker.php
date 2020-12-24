<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionCheckers;

use InvalidArgumentException;
use Ordermind\LogicalPermissions\PermissionCheckerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Checks host permissions.
 */
class HostChecker implements PermissionCheckerInterface
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
        return 'host';
    }

    /**
     * Checks if the current request is sent to an approved host.
     *
     * @param string $host
     * @param array  $context
     *
     * @return bool TRUE if the host is allowed or FALSE if it is not allowed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkPermission(string $host, $context): bool
    {
        if (!$host) {
            throw new InvalidArgumentException('The host parameter cannot be empty.');
        }

        $currentRequest = $this->requestStack->getCurrentRequest();

        if (!$currentRequest) {
            return false;
        }

        return (bool) preg_match('{' . $host . '}i', $currentRequest->getHost());
    }
}
