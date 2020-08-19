<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Exception;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\LogicalPermissionsFacade;

/**
 * {@inheritDoc}
 */
class LogicalAuthorization implements LogicalAuthorizationInterface
{
    /**
     * @var LogicalPermissionsFacade
     */
    protected $lpFacade;

    /**
     * @var HelperInterface
     */
    protected $helper;

    public function __construct(
        LogicalPermissionsFacade $lpFacade,
        HelperInterface $helper
    ) {
        $this->lpFacade = $lpFacade;
        $this->helper = $helper;
    }

    /**
     * {@inheritDoc}
     */
    public function checkAccess(
        $permissions,
        array $context,
        bool $allowBypass = true
    ): bool {
        try {
            return $this->lpFacade->checkAccess(
                $permissions,
                $context,
                $allowBypass
            );
        } catch (Exception $e) {
            $class = get_class($e);
            $message = $e->getMessage();
            if ($class === PermissionTypeNotRegisteredException::class) {
                $message .= ' Please use the "logauth.tag.permission_checker" service tag to register a permission '
                    . 'checker';
            }

            $this->helper->handleError(
                sprintf(
                    'An exception was caught while checking access: "%s" at %s line %s',
                    $message,
                    $e->getFile(),
                    $e->getLine()
                ),
                [
                    'exception'   => $class,
                    'permissions' => $permissions,
                    'context'     => $context,
                ]
            );
        }

        return false;
    }
}
