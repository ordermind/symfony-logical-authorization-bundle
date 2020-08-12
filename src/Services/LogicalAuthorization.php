<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;

/**
 * {@inheritdoc}
 */
class LogicalAuthorization implements LogicalAuthorizationInterface
{
    /**
     * @var LogicalPermissionsProxyInterface
     */
    protected $lpProxy;

    /**
     * @var Ordermind\LogicalAuthorizationBundle\Services\HelperInterface
     */
    protected $helper;

    /**
     * @internal
     *
     * @param LogicalPermissionsProxyInterface $lpProxy
     * @param HelperInterface                  $helper
     * @param BypassAccessCheckerInterface     $bypassAccessChecker
     */
    public function __construct(
        LogicalPermissionsProxyInterface $lpProxy,
        HelperInterface $helper,
        BypassAccessCheckerInterface $bypassAccessChecker
    ) {
        $this->lpProxy = $lpProxy;
        if (!$this->lpProxy->getBypassAccessChecker()) {
            $this->lpProxy->setBypassAccessChecker($bypassAccessChecker);
        }
        $this->helper = $helper;
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess(
        $permissions,
        array $context,
        bool $allowBypass = true
    ): bool {
        try {
            return $this->lpProxy->checkAccess(
                $permissions,
                $context,
                $allowBypass
            );
        } catch (\Exception $e) {
            $class = get_class($e);
            $message = $e->getMessage();
            $this->helper->handleError(
                "An exception was caught while checking access: \"$message\" "
                . 'at ' . $e->getFile()
                . ' line '
                . $e->getLine(),
                [
                    'exception' => $class,
                    'permissions' => $permissions,
                    'context' => $context,
                ]
            );
        }

        return false;
    }
}
