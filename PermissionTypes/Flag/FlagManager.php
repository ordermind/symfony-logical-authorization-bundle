<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\PermissionTypeInterface;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Exceptions\FlagNotRegisteredException;

class FlagManager implements PermissionTypeInterface {
  protected $flags = [];

  public function getName() {
    return 'flag';
  }

  public function addFlag(FlagInterface $flag) {
    $name = $flag->getName();
    if(!is_string($name)) {
      throw new \InvalidArgumentException('The name of a flag must be a string.');
    }
    if(!$name) {
      throw new \InvalidArgumentException('The name of a flag cannot be empty.');
    }
    if($this->flagExists($name)) {
      throw new \InvalidArgumentException("The flag \"$name\" already exists! If you want to change the class that handles a flag, you may do so by altering the service definition for that flag.");
    }

    $flags = $this->getFlags();
    $flags[$name] = $flag;
    $this->setFlags($flags);
  }

  public function removeFlag($name) {
    if(!is_string($name)) {
      throw new \InvalidArgumentException('The name parameter must be a string.');
    }
    if(!$name) {
      throw new \InvalidArgumentException('The name parameter cannot be empty.');
    }
    if(!$this->flagExists($name)) {
      throw new FlagNotRegisteredException("The flag \"$name\" has not been registered. Please use the 'ordermind_logical_authorization.tag.permission_type.flag' service tag to add a flag.");
    }

    $flags = $this->getFlags();
    unset($flags[$name]);
    $this->setFlags($flags);
  }

  public function getFlags() {
    return $this->flags;
  }

  protected function setFlags($flags) {
    $this->flags = $flags;
  }

  public function checkPermission($name, $context) {
    if(!$this->flagExists($name)) {
      throw new FlagNotRegisteredException("The flag \"$name\" has not been registered. Please use the 'ordermind_logical_authorization.tag.permission_type.flag' service tag to add a flag.");
    }

    $flags = $this->getFlags();
    return $flags[$name]->checkFlag($context);
  }

  protected function flagExists($name) {
    if(!is_string($name)) {
      throw new \InvalidArgumentException('The name parameter must be a string.');
    }
    if(!$name) {
      throw new \InvalidArgumentException('The name parameter cannot be empty.');
    }

    $flags = $this->getFlags();
    return isset($flags[$name]);
  }
}
