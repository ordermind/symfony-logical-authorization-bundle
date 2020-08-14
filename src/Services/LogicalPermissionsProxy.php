<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\LogicalPermissionsFacade;
use Ordermind\LogicalPermissions\PermissionTypeInterface;

/**
 * {@inheritdoc}
 */
class LogicalPermissionsProxy implements LogicalPermissionsProxyInterface
{
    /**
     * @var LogicalPermissionsFacade
     */
    protected $lpFacade;

    protected $permissionTypes;

    /**
     * @internal
     */
    public function __construct()
    {
        $this->lpFacade = new LogicalPermissionsFacade();
        $this->permissionTypes = $this->lpFacade->getPermissionTypeCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function addType(PermissionTypeInterface $type)
    {
        try {
            $this->permissionTypes->add($type);
        } catch (PermissionTypeAlreadyExistsException $e) {
            $class = get_class($e);
            $message = $e->getMessage();
            $message .=
                ' If you want to change the class that handles a permission type, you may do so by overriding '
                . 'the service definition for that permission type.';
            throw new $class($message);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeType(string $name)
    {
        $this->permissionTypes->remove($name);
    }

    /**
     * {@inheritdoc}
     */
    public function typeExists(string $name): bool
    {
        return $this->permissionTypes->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getTypes(): array
    {
        return $this->permissionTypes->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function setBypassAccessChecker(BypassAccessCheckerInterface $bypassAccessChecker)
    {
        $this->lpFacade->setBypassAccessChecker($bypassAccessChecker);
    }

    /**
     * {@inheritdoc}
     */
    public function getBypassAccessChecker(): ?BypassAccessCheckerInterface
    {
        return $this->lpFacade->getBypassAccessChecker();
    }

    /**
     * {@inheritdoc}
     */
    public function getValidPermissionKeys(): array
    {
        return $this->lpFacade->getValidPermissionKeys();
    }

    /**
     * {@inheritdoc}
     */
    public function checkAccess($permissions, array $context, bool $allowBypass = true): bool
    {
        try {
            return $this->lpFacade->checkAccess($permissions, $context, $allowBypass);
        } catch (PermissionTypeNotRegisteredException $e) {
            $class = get_class($e);
            $message = $e->getMessage();
            $message .= ' Please use the \'logauth.tag.permission_type\' service tag to register a permission type.';
            throw new $class($message);
        }
    }
}
