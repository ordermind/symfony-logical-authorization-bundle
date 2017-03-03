<?php

namespace Ordermind\LogicalAuthorizationBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use Doctrine\Common\Collections\Collection;

use Ordermind\DoctrineManagerBundle\Event\RepositoryManagerEvents\AbstractResultEventInterface;
use Ordermind\DoctrineManagerBundle\Event\RepositoryManagerEvents\UnknownResultEventInterface;
use Ordermind\DoctrineManagerBundle\Event\RepositoryManagerEvents\SingleModelResultEventInterface;
use Ordermind\DoctrineManagerBundle\Event\RepositoryManagerEvents\MultipleModelResultEventInterface;
use Ordermind\DoctrineManagerBundle\Event\RepositoryManagerEvents\LazyModelCollectionResultEventInterface;
use Ordermind\DoctrineManagerBundle\Event\RepositoryManagerEvents\BeforeCreateEventInterface;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationModelInterface;

class RepositoryManagerSubscriber implements EventSubscriberInterface {
  protected $laModel;
  protected $config;

  public function __construct(LogicalAuthorizationModelInterface $laModel, array $config) {
    $this->laModel = $laModel;
    $this->config = $config;
  }

  public static function getSubscribedEvents() {
    return array(
      'ordermind_doctrine_manager.event.repository_manager.unknown_result' => array(
        array('onUnknownResult'),
      ),
      'ordermind_doctrine_manager.event.repository_manager.single_model_result' => array(
        array('onSingleModelResult'),
      ),
      'ordermind_doctrine_manager.event.repository_manager.multiple_model_result' => array(
        array('onMultipleModelResult'),
      ),
      'ordermind_doctrine_manager.event.repository_manager.before_create' => array(
        array('onBeforeCreate'),
      ),
      'ordermind_doctrine_manager.event.repository_manager.lazy_model_collection_result' => array(
        array('onLazyModelCollectionResult'),
      ),
    );
  }

  public function onUnknownResult(UnknownResultEventInterface $event) {
    $this->onResult($event);
  }
  public function onSingleModelResult(SingleModelResultEventInterface $event) {
    $this->onResult($event);
  }
  public function onMultipleModelResult(MultipleModelResultEventInterface $event) {
    $this->onResult($event);
  }
  public function onBeforeCreate(BeforeCreateEventInterface $event) {
    $class = $event->getModelClass();
    if(!$this->laModel->checkModelAccess($class, 'create')) {
      $event->setAbort(true);
    }
  }
  public function onLazyModelCollectionResult(LazyModelCollectionResultEventInterface $event) {
    if(empty($this->config['check_lazy_loaded_models'])) return;

    $this->onResult($event);
  }

  protected function onResult(AbstractResultEventInterface $event) {
    $repository = $event->getRepository();
    $result = $event->getResult();
    $class = $repository->getClassName();
    if(is_array($result)) {
      $filtered_result = $this->filterModels($result, $class);
    }
    elseif($result instanceof Collection) {
      $filtered_result = $this->filterModelCollection($result, $class);
    }
    else {
      $filtered_result = $this->filterModelByPermissions($result, $class);
    }

    $event->setResult($filtered_result);
  }
  protected function filterModels($models, $class) {
    foreach($models as $i => $model) {
      $models[$i] = $this->filterModelByPermissions($model, $class);
    }
    $models = array_filter($models);
    return $models;
  }
  protected function filterModelCollection($collection, $class) {
    foreach($collection as $i => $model) {
      if(is_null($this->filterModelByPermissions($model, $class))) {
        $collection->remove($i);
      };
    }
    return $collection;
  }
  protected function filterModelByPermissions($model, $class) {
    if(!is_object($model) || get_class($model) !== $class) return $model;

    if(!$this->laModel->checkModelAccess($model, 'read')) {
      return null;
    }

    return $model;
  }
}
