<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Exception;
use Ordermind\LogicalAuthorizationBundle\ValueObjects\RawPermissionTree;
use Ordermind\LogicalPermissions\AccessChecker\AccessCheckerInterface;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\Serializers\FullPermissionTreeDeserializer;

/**
 * {@inheritDoc}
 */
class LogicalAuthorization implements LogicalAuthorizationInterface
{
    protected FullPermissionTreeDeserializer $deserializer;

    protected AccessCheckerInterface $accessChecker;

    protected HelperInterface $helper;

    public function __construct(
        FullPermissionTreeDeserializer $deserializer,
        AccessCheckerInterface $accessChecker,
        HelperInterface $helper
    ) {
        $this->deserializer = $deserializer;
        $this->accessChecker = $accessChecker;
        $this->helper = $helper;
    }

    /**
     * {@inheritDoc}
     */
    public function checkAccess(
        RawPermissionTree $rawPermissionTree,
        array $context,
        bool $allowBypass = true
    ): bool {
        try {
            return $this->accessChecker->checkAccess(
                $this->deserializer->deserialize($rawPermissionTree->getValue()),
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
                    'permissions' => $rawPermissionTree->getValue(),
                    'context'     => $context,
                ]
            );
        }

        return false;
    }
}
