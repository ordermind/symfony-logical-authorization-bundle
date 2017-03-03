<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Functional\Services;

class LogicalAuthorizationORMAnnotationTest extends LogicalAuthorizationORMBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    parent::setUp();

    $this->testEntityRoleAuthorRepositoryManager = $this->container->get('repository_manager.test_entity_roleauthor_annotation');
    $this->testEntityHasAccountNoInterfaceRepositoryManager = $this->container->get('repository_manager.test_entity_hasaccount_annotation');
    $this->testEntityNoBypassRepositoryManager = $this->container->get('repository_manager.test_entity_nobypass_annotation');
    $this->testUserRepositoryManager = $this->container->get('repository_manager.test_user_annotation');

    $this->deleteAll(array(
      $this->testEntityRoleAuthorRepositoryManager,
      $this->testEntityHasAccountNoInterfaceRepositoryManager,
      $this->testEntityNoBypassRepositoryManager,
    ));
    $this->addUsers();
  }
}
