<?php

namespace Ordermind\LogicalAuthorizationBundle\Services;

use Ordermind\LogicalPermissions\Exceptions\PermissionTypeNotRegisteredException;
use Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsManagerInterface;
use Ordermind\LogicalAuthorizationBundle\Services\HelperInterface;

class LogicalAuthorization implements LogicalAuthorizationInterface {

  protected $lpManager;
  protected $helper;

  /**
   * @internal
   *
   * @param Ordermind\LogicalAuthorizationBundle\Services\LogicalPermissionsManagerInterface $lpManager The logical permissions manager to use
   * @param Ordermind\LogicalAuthorizationBundle\Services\HelperInterface $helper LogicalAuthorization helper service
   */
  public function __construct(LogicalPermissionsManagerInterface $lpManager, HelperInterface $helper) {
    $this->lpManager = $lpManager;
    if(!$this->lpManager->getBypassCallback()) {
      $this->lpManager->setBypassCallback(function($context) {
        return $this->lpManager->checkAccess(['flag' => 'bypass_access'], $context, false);
      });
    }
    $this->helper = $helper;
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
      $this->helper->handleError("An exception was caught while checking access: \"$newMessage\" at " . $e->getFile() . " line " . $e->getLine(), array('exception' => $class, 'permissions' => $permissions, 'context' => $context));
    }
    catch (\Exception $e) {
      $class = get_class($e);
      $message = $e->getMessage();
      $this->helper->handleError("An exception was caught while checking access: \"$message\" at " . $e->getFile() . " line " . $e->getLine(), array('exception' => $class, 'permissions' => $permissions, 'context' => $context));
    }
    return false;
  }
}
