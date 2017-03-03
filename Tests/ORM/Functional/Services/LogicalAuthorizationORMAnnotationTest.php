<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Functional\Services;

class LogicalAuthorizationORMAnnotationTest extends LogicalAuthorizationORMBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    parent::setUp();

    $this->testEntityRepositoryManager = $this->container->get('repository_manager.test_entity_annotation');
    $this->testEntityNoBypassRepositoryManager = $this->container->get('repository_manager.test_entity_nobypass_annotation');
    $this->testUserRepositoryManager = $this->container->get('repository_manager.test_user_annotation');

    $this->deleteAll($this->testEntityRepositoryManager);
    $this->addUsers();
  }
}
