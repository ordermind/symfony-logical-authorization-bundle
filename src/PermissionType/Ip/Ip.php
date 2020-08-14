<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionType\Ip;

use InvalidArgumentException;
use Ordermind\LogicalPermissions\PermissionTypeInterface;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\HttpFoundation\RequestStack;
use TypeError;

/**
 * Permission type for checking ipaddress.
 */
class Ip implements PermissionTypeInterface
{
    /**
     * @var RequestStack
     */
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
        return 'ip';
    }

    /**
     * Checks if the current request comes from an approved ip address.
     *
     * @param string $ipAddress
     * @param array  $context
     *
     * @return bool TRUE if the ip address is allowed or FALSE if it is not allowed
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function checkPermission($ipAddress, $context): bool
    {
        if (!is_string($ipAddress)) {
            throw new TypeError('The ipAddress parameter must be a string.');
        }
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
