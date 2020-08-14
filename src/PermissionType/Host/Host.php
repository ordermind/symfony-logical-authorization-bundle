<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionType\Host;

use InvalidArgumentException;
use Ordermind\LogicalPermissions\PermissionCheckerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use TypeError;

/**
 * Permission type for checking host.
 */
class Host implements PermissionCheckerInterface
{
    protected $requestStack;

    /**
     * @internal
     *
     * @param RequestStack $requestStack
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
    public function checkPermission($host, $context): bool
    {
        if (!is_string($host)) {
            throw new TypeError('The host parameter must be a string.');
        }
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
