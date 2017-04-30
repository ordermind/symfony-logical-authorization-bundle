<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Shared\Fixtures\Services;

use Doctrine\Common\Collections\Criteria;

use Ordermind\LogicalAuthorizationBundle\Services\RepositoryDecoratorInterface;
use Ordermind\LogicalAuthorizationBundle\Services\ModelDecoratorInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

class TestModelOperations {
  private $repositoryDecorator;

  public function setRepositoryDecorator(RepositoryDecoratorInterface $repositoryDecorator) {
    $this->repositoryDecorator = $repositoryDecorator;
  }

  public function getUnknownResult($bypassAccess = false) {
    if($bypassAccess) {
      $models = $this->repositoryDecorator->getRepository()->customMethod();
      return $this->repositoryDecorator->wrapModels($models);
    }
    return $this->repositoryDecorator->customMethod();
  }

  public function getSingleModelResult($id, $bypassAccess = false) {
    if($bypassAccess) {
      $model = $this->repositoryDecorator->getRepository()->find($id);
      return $this->repositoryDecorator->wrapModel($model);
    }
    return $this->repositoryDecorator->find($id);
  }

  public function getMultipleModelResult($bypassAccess = false) {
    if($bypassAccess) {
      $models = $this->repositoryDecorator->getRepository()->findAll();
      return $this->repositoryDecorator->wrapModels($models);
    }
    return $this->repositoryDecorator->findAll();
  }

  public function getLazyLoadedModelResult($bypassAccess = false) {
    if($bypassAccess) {
      return $this->repositoryDecorator->getRepository()->matching(Criteria::create());
    }
    return $this->repositoryDecorator->matching(Criteria::create());
  }

  public function createTestModel($user = null, $bypassAccess = false) {
    if($user && $user instanceof ModelDecoratorInterface) {
      $this->repositoryDecorator->setObjectManager($user->getObjectManager());
      $user = $user->getModel();
    }

    if($bypassAccess) {
      $class = $this->repositoryDecorator->getClassName();
      $model = new $class();
      $modelDecorator = $this->repositoryDecorator->wrapModel($model);
    }
    else {
      $modelDecorator = $this->repositoryDecorator->create();
    }

    if($modelDecorator) {
      if($bypassAccess) {
        $model = $modelDecorator->getModel();
        if($user instanceof UserInterface) {
          $model->setAuthor($user);
        }
        $om = $modelDecorator->getObjectManager();
        $om->persist($model);
        $om->flush();
      }
      else {
        if($user instanceof UserInterface) {
          $modelDecorator->setAuthor($user);
        }
        $modelDecorator->save();
      }
    }

    return $modelDecorator;
  }

  public function callMethodGetter(ModelDecoratorInterface $modelDecorator, $bypassAccess = false) {
    if($bypassAccess) {
      return $modelDecorator->getModel()->getField1();
    }
    return $modelDecorator->getField1();
  }

  public function callMethodSetter(ModelDecoratorInterface $modelDecorator, $bypassAccess = false) {
    if($bypassAccess) {
      $modelDecorator->getModel()->setField1('test');
    }
    else {
      $modelDecorator->setField1('test');
    }
    return $modelDecorator;
  }
}