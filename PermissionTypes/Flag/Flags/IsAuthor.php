<?php

namespace Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\Flags;

use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;
use Ordermind\LogicalAuthorizationBundle\PermissionTypes\Flag\FlagInterface;

class IsAuthor implements FlagInterface {
  public function getName() {
    return 'is_author';
  }
  public function checkFlag($context) {
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
