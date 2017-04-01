<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Fixtures\Services;

use Doctrine\Common\Collections\Criteria;

use Ordermind\DoctrineManagerBundle\Services\Manager\RepositoryManagerInterface;
use Ordermind\DoctrineManagerBundle\Services\Manager\ModelManagerInterface;

class TestEntityOperations {
  private $repositoryManager;

  public function setRepositoryManager(RepositoryManagerInterface $repositoryManager) {
    $this->repositoryManager = $repositoryManager;
  }

  public function getUnknownResult($bypassAccess = false) {
    if($bypassAccess) {
      $models = $this->repositoryManager->getRepository()->customMethod();
      return $this->repositoryManager->wrapModels($models);
    }
    return $this->repositoryManager->customMethod();
  }

  public function getSingleModelResult($id, $bypassAccess = false) {
    if($bypassAccess) {
      $model = $this->repositoryManager->getRepository()->find($id);
      return $this->repositoryManager->wrapModel($model);
    }
    return $this->repositoryManager->find($id);
  }

  public function getMultipleModelResult($bypassAccess = false) {
    if($bypassAccess) {
      $models = $this->repositoryManager->getRepository()->findAll();
      return $this->repositoryManager->wrapModels($models);
    }
    return $this->repositoryManager->findAll();
  }

  public function getLazyLoadedModelResult($bypassAccess = false) {
    if($bypassAccess) {
      return $this->repositoryManager->getRepository()->matching(Criteria::create());
    }
    return $this->repositoryManager->matching(Criteria::create());
  }

  public function createTestModel($user = null, $bypassAccess = false) {
    if($user && $user instanceof ModelManagerInterface) {
      $this->repositoryManager->setObjectManager($user->getObjectManager());
    }

    if($bypassAccess) {
      $class = $this->repositoryManager->getClassName();
      $model = new $class();
      $modelManager = $this->repositoryManager->wrapModel($model);
    }
    else {
      $modelManager = $this->repositoryManager->create();
    }

    if($modelManager) {
      if($user && $user instanceof ModelManagerInterface) {
        $user = $user->getModel();
        $modelManager->setAuthor($user);
      }
      $modelManager->save();
    }

    return $modelManager;
  }

  public function callMethodGetter(ModelManagerInterface $modelManager, $bypassAccess = false) {
    if($bypassAccess) {
      return $modelManager->getModel()->getField1();
    }
    return $modelManager->getField1();
  }

  public function callMethodSetter(ModelManagerInterface $modelManager, $bypassAccess = false) {
    if($bypassAccess) {
      $modelManager->getModel()->setField1('test');
    }
    else {
      $modelManager->setField1('test');
    }
    return $modelManager;
  }
}