<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag;

use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags\FlagInterface;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Exceptions\FlagNotRegisteredException;

class FlagManager implements FlagManagerInterface {

  protected $flags = [];

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'flag';
  }

  /**
   * {@inheritdoc}
   */
  public function addFlag(FlagInterface $flag) {
    $name = $flag->getName();
    if(!is_string($name)) {
      throw new \InvalidArgumentException('The name of a flag must be a string.');
    }
    if(!$name) {
      throw new \InvalidArgumentException('The name of a flag cannot be empty.');
    }
    if($this->flagExists($name)) {
      throw new \InvalidArgumentException("The flag \"$name\" already exists! If you want to change the class that handles a flag, you may do so by overriding the service definition for that flag.");
    }

    $flags = $this->getFlags();
    $flags[$name] = $flag;
    $this->setFlags($flags);
  }

  /**
   * {@inheritdoc}
   */
  public function removeFlag($name) {
    if(!is_string($name)) {
      throw new \InvalidArgumentException('The name parameter must be a string.');
    }
    if(!$name) {
      throw new \InvalidArgumentException('The name parameter cannot be empty.');
    }
    if(!$this->flagExists($name)) {
      throw new FlagNotRegisteredException("The flag \"$name\" has not been registered. Please use the 'logauth.tag.permission_type.flag' service tag to register a flag.");
    }

    $flags = $this->getFlags();
    unset($flags[$name]);
    $this->setFlags($flags);
  }

  /**
   * {@inheritdoc}
   */
  public function getFlags() {
    return $this->flags;
  }

  protected function setFlags($flags) {
    $this->flags = $flags;
  }

  /**
   * Checks if a flag is switched on in a given context
   *
   * @param string $name The name of the flag to evaluate
   * @param array $context The context for evaluating the flag. For more specific information, check the documentation for the flag you want to evaluate.
   *
   * @return bool TRUE if the flag is switched on or FALSE if the flag is switched off
   */
  public function checkPermission($name, $context) {
    if(!is_string($name)) {
      throw new \InvalidArgumentException('The name parameter must be a string.');
    }
    if(!$name) {
      throw new \InvalidArgumentException('The name parameter cannot be empty.');
    }
    if(!$this->flagExists($name)) {
      throw new FlagNotRegisteredException("The flag \"$name\" has not been registered. Please use the 'logauth.tag.permission_type.flag' service tag to register a flag.");
    }

    $flags = $this->getFlags();
    return $flags[$name]->checkFlag($context);
  }

  protected function flagExists($name) {
    $flags = $this->getFlags();
    return isset($flags[$name]);
  }
}
