<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalPermissions\AccessChecker;
use Ordermind\LogicalPermissions\BypassAccessCheckerInterface;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalPermissions\PermissionTypeInterface;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface;

/**
 * {@inheritdoc}
 */
class LogicalPermissionsProxy implements LogicalPermissionsProxyInterface
{
    protected $accessChecker;
    protected $permissionTypeCollection;

  /**
   * @internal
   */
    public function __construct()
    {
        $this->accessChecker = new AccessChecker();
        $this->permissionTypeCollection = $this->accessChecker->getPermissionTypeCollection();
    }

  /**
   * {@inheritdoc}
   */
    public function addType(PermissionTypeInterface $type)
    {
        try {
            $this->permissionTypeCollection->add($type);
        } catch (PermissionTypeAlreadyExistsException $e) {
            $class = get_class($e);
            $message = $e->getMessage();
            $message .= ' If you want to change the class that handles a permission type, you may do so by overriding the service definition for that permission type.';
            throw new $class($message);
        }
    }

  /**
   * {@inheritdoc}
   */
    public function removeType(string $name)
    {
        $this->permissionTypeCollection->remove($name);
    }

  /**
   * {@inheritdoc}
   */
    public function typeExists(string $name): bool
    {
        return $this->permissionTypeCollection->has($name);
    }

  /**
   * {@inheritdoc}
   */
    public function getTypes(): array
    {
        return $this->permissionTypeCollection->toArray();
    }

  /**
   * {@inheritdoc}
   */
    public function setBypassAccessChecker(BypassAccessCheckerInterface $bypassAccessChecker)
    {
        $this->accessChecker->setBypassAccessChecker($bypassAccessChecker);
    }

  /**
   * {@inheritdoc}
   */
    public function getBypassAccessChecker(): ?BypassAccessCheckerInterface
    {
        return $this->accessChecker->getBypassAccessChecker();
    }

  /**
   * {@inheritdoc}
   */
    public function getValidPermissionKeys(): array
    {
        return $this->accessChecker->getValidPermissionKeys();
    }

  /**
   * {@inheritdoc}
   */
    public function checkAccess($permissions, array $context, bool $allowBypass = true): bool
    {
        try {
            return $this->accessChecker->checkAccess($permissions, $context, $allowBypass);
        } catch (PermissionTypeNotRegisteredException $e) {
            $class = get_class($e);
            $message = $e->getMessage();
            $message .= ' Please use the \'logauth.tag.permission_type\' service tag to register a permission type.';
            throw new $class($message);
        }
    }
}
