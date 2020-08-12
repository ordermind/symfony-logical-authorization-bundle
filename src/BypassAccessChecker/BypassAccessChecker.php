<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\BypassAccessChecker;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface;
use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;

/**
 * Default bypass access checker.
 */
class BypassAccessChecker implements BypassAccessCheckerInterface
{
    /**
     * @var Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface
     */
    protected $lpProxy;

    /**
     * @internal
     *
     * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface $lpProxy
     */
    public function __construct(LogicalPermissionsProxyInterface $lpProxy)
    {
        $this->lpProxy = $lpProxy;
    }

    /**
     * {@inheritdoc}
     */
    public function checkBypassAccess($context)
    {
        return $this->lpProxy->checkAccess(['flag' => 'user_can_bypass_access'], $context, false);
    }
}
