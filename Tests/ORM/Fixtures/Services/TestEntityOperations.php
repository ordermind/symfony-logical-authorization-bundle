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






  public function findTestEntities($bypassAccess = false) {
    if($bypassAccess) {
      $models = $this->repositoryManager->getRepository()->findAll();
      return $this->repositoryManager->wrapModels($models);
    }
    return $this->repositoryManager->findAll();
  }

  public function findTestEntitiesLazyLoad($bypassAccess = false) {
    if($bypassAccess) {
      $collection = $this->repositoryManager->getRepository()->matching(Criteria::create());
    }
    else {
      $collection = $this->repositoryManager->matching(Criteria::create());
    }
    $modelManagers = [];
    foreach($collection as $i => $model) {
      $modelManagers[$i] = $this->repositoryManager->wrapModel($model);
    }
    return $modelManagers;
  }

  public function createTestEntity($user = null) {
    if($user && $user instanceof ModelManagerInterface) {
      $this->repositoryManager->setObjectManager($user->getObjectManager());
      $user = $user->getModel();
      $modelManager = $this->repositoryManager->create();
      $modelManager->setAuthor($user);
    }
    else {
      $modelManager = $this->repositoryManager->create();
    }
    if($modelManager) {
      $modelManager->save();
    }

    return $modelManager;
  }

  public function updateTestEntity(ModelManagerInterface $entity) {
    $modelManager->setField1('test1');
    $modelManager->getObjectManager()->flush();
  }

  public function updateTestEntitySucceed(ModelManagerInterface $entity) {
    $modelManager->setField2('test2');
    $modelManager->getObjectManager()->flush();
  }
}