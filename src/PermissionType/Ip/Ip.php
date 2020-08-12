<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\PermissionType\Ip;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\IpUtils;

use Ordermind\LogicalPermissions\PermissionTypeInterface;

/**
 * Permission type for checking ip address
 */
class Ip implements PermissionTypeInterface
{
    /**
     * @var Symfony\Component\HttpFoundation\RequestStack
     */
    protected $requestStack;

    /**
     * @internal
     *
     * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack RequestStack service for fetching the current request
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
     * Checks if the current request comes from an approved ip address
     *
     * @param string $ip      The ip to evaluate
     * @param array  $context The context for evaluating the ip
     *
     * @return bool TRUE if the ip is allowed or FALSE if it is not allowed
     */
    public function checkPermission($ip, $context)
    {
        if (!is_string($ip)) {
            throw new \TypeError('The ip parameter must be a string.');
        }
        if (!$ip) {
            throw new \InvalidArgumentException('The ip parameter cannot be empty.');
        }

        $currentRequest = $this->requestStack->getCurrentRequest();

        if (!$currentRequest) {
            return false;
        }

        return IpUtils::checkIp($currentRequest->getClientIp(), $ip);
    }
}
