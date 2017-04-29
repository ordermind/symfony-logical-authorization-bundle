<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags;

use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;

class IsAuthor implements FlagInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'is_author';
  }

  /**
   * Checks if the author of a model is the same as the user in the current context.
   *
   * @param array $context The context for evaluating the flag. The context must contain a 'user' key and a 'model' key. The user needs to implement Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface and the model needs to implement Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface. You can get the current user by calling getCurrentUser() from the service 'ordermind_logical_authorization.service.helper'.
   *
   * @return bool TRUE if the user is the author of the model and FALSE if it isn't. There is no support for anonymous authors so if the user is anonymous it will always return FALSE.
   */
  public function checkFlag($context) {
    if(!is_array($context)) {
      throw new \InvalidArgumentException('The context parameter must be an array. Current type is ' . gettype($context) . '.');
    }
    if(!isset($context['user'])) {
      throw new \InvalidArgumentException('The context parameter must contain a "user" key to be able to evaluate the ' . $this->getName() . ' flag.');
    }

    $user = $context['user'];
    if(is_string($user)) { //Anonymous user
      return false;
    }

    if(!($user instanceof UserInterface)) {
      throw new \InvalidArgumentException('The user class must implement Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface to be able to evaluate the ' . $this->getName() . ' flag.');
    }
    if(!isset($context['model'])) {
      throw new \InvalidArgumentException('Missing key "model" in context parameter. We cannot evaluate the ' . $this->getName() . ' flag without a model.');
    }

    $model = $context['model'];
    if(!($model instanceof ModelInterface)) {
      if(is_string($model) && class_exists($model)) {
        throw new \InvalidArgumentException('A class string was passed instead of a model for evaluating the ' . $this->getName() . ' flag. Are you trying to use the ' . $this->getName() . ' flag in a "create" permission? That is not possible because a model can\'t have an author until after it is created.');
      }
      throw new \InvalidArgumentException('The model class must implement Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface to be able to evaluate the ' . $this->getName() . ' flag.');
    }

    $author = $model->getAuthor();
    if(!$author) {
      return false;
    }
    if(!($author instanceof UserInterface)) {
      throw new \InvalidArgumentException('The author class must implement Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface to be able to evaluate the ' . $this->getName() . ' flag.');
    }

    return $user->getId() === $author->getId();
  }
}
