<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalPermissions\LogicalPermissions;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeAlreadyExistsException;
use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\PermissionTypeInterface;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsProxyInterface;

/**
 * {@inheritdoc}
 */
class LogicalPermissionsProxy implements LogicalPermissionsProxyInterface
{
    protected $lp;

  /**
   * @internal
   */
    public function __construct()
    {
        $this->lp = new LogicalPermissions();
    }

  /**
   * {@inheritdoc}
   */
    public function addType(PermissionTypeInterface $type)
    {
        try {
            $this->lp->addType($type->getName(), [$type, 'checkPermission']);
        } catch (PermissionTypeAlreadyExistsException $e) {
            $class = get_class($e);
            $message = $e->getMessage();
            $exploded = explode('If you want', $message);
            $newMessage = $exploded[0].'If you want to change the class that handles a permission type, you may do so by overriding the service definition for that permission type.';
            throw new $class($newMessage);
        }
    }

  /**
   * {@inheritdoc}
   */
    public function removeType(string $name)
    {
        $this->lp->removeType($name);
    }

  /**
   * {@inheritdoc}
   */
    public function typeExists(string $name): bool
    {
        return $this->lp->typeExists($name);
    }

  /**
   * {@inheritdoc}
   */
    public function getTypes(): array
    {
        return $this->lp->getTypes();
    }

  /**
   * {@inheritdoc}
   */
    public function setBypassCallback(callable $callback)
    {
        $this->lp->setBypassCallback($callback);
    }

  /**
   * {@inheritdoc}
   */
    public function getBypassCallback(): ?callable
    {
        return $this->lp->getBypassCallback();
    }

  /**
   * {@inheritdoc}
   */
    public function getValidPermissionKeys(): array
    {
        return $this->lp->getValidPermissionKeys();
    }

  /**
   * {@inheritdoc}
   */
    public function checkAccess($permissions, array $context, bool $allowBypass = true): bool
    {
        try {
            return $this->lp->checkAccess($permissions, $context, $allowBypass);
        } catch (PermissionTypeNotRegisteredException $e) {
            $class = get_class($e);
            $message = $e->getMessage();
            $exploded = explode('Please use', $message);
            $newMessage = $exploded[0].'Please use the \'logauth.tag.permission_type\' service tag to register a permission type.';
            throw new $class($newMessage);
        }
    }
}
