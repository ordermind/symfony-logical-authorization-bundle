<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Psr\Log\LoggerInterface;

use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\DoctrineManagerBundle\Services\Manager\ModelManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\ErrorHandlerInterface;

class LogicalAuthorization implements LogicalAuthorizationInterface {

  protected $lpManager;
  protected $errorHandler;

  /**
   * @internal
   *
   * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsManagerInterface $lpManager The logical permissions manager to use
   * @param Ordermind\LogicalAuthorizationBundle\Services\ErrorHandlerInterface $errorHandler A service for handling errors
   */
  public function __construct(LogicalPermissionsManagerInterface $lpManager, ErrorHandlerInterface $errorHandler) {
    $this->lpManager = $lpManager;
    if(!$this->lpManager->getBypassCallback()) {
      $this->lpManager->setBypassCallback(function($context) {
        return $this->lpManager->checkAccess(['flag' => 'bypass_access'], $context, false);
      });
    }
    $this->errorHandler = $errorHandler;
  }

  /**
   * {@inheritdoc}
   */
  public function checkAccess($permissions, $context, $allow_bypass = true) {
    try {
      return $this->lpManager->checkAccess($permissions, $context, $allow_bypass);
    }
    catch (PermissionTypeNotRegisteredException $e) {
      $class = get_class($e);
      $message = $e->getMessage();
      $arrmessage = explode('Please use', $message);
      $newMessage = $arrmessage[0] . 'Please use the \'ordermind_logical_authorization.tag.permission_type\' service tag to register a permission type.';
      $this->errorHandler->handleError("An exception was caught while checking access: \"$newMessage\" at " . $e->getFile() . " line " . $e->getLine(), array('exception' => $class, 'permissions' => $permissions, 'context' => $context));
    }
    catch (\Exception $e) {
      $class = get_class($e);
      $message = $e->getMessage();
      $this->errorHandler->handleError("An exception was caught while checking access: \"$message\" at " . $e->getFile() . " line " . $e->getLine(), array('exception' => $class, 'permissions' => $permissions, 'context' => $context));
    }
    return false;
  }

  /**
   * {@inheritdoc}
   */
  public function getRidOfManager($modelManager) {
    if(!is_object($modelManager)) return $modelManager;
    if(!($modelManager instanceof ModelManagerInterface)) return $modelManager;
    return $modelManager->getModel();
  }

}
