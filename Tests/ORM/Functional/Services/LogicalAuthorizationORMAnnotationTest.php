<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Functional\Services;

class LogicalAuthorizationORMAnnotationTest extends LogicalAuthorizationORMBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testEntityRoleAuthorRepositoryManager' => 'repository_manager.test_entity_roleauthor_annotation',
      'testEntityHasAccountNoInterfaceRepositoryManager' => 'repository_manager.test_entity_hasaccount_annotation',
      'testEntityNoBypassRepositoryManager' => 'repository_manager.test_entity_nobypass_annotation',
      'testEntityOverriddenPermissionsRepositoryManager' => 'repository_manager.test_entity_overridden_permissions_annotation',
      'testEntityVariousPermissionsRepositoryManager' => 'repository_manager.test_entity_various_permissions_annotation',
    );

    parent::setUp();
  }
}
