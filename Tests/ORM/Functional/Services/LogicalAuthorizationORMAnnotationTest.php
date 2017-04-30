<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Functional\Services;

class LogicalAuthorizationORMAnnotationTest extends LogicalAuthorizationORMBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testEntityRoleAuthorRepositoryDecorator' => 'repository_decorator.test_entity_roleauthor_annotation',
      'testEntityHasAccountNoInterfaceRepositoryDecorator' => 'repository_decorator.test_entity_hasaccount_annotation',
      'testEntityNoBypassRepositoryDecorator' => 'repository_decorator.test_entity_nobypass_annotation',
      'testEntityOverriddenPermissionsRepositoryDecorator' => 'repository_decorator.test_entity_overridden_permissions_annotation',
      'testEntityVariousPermissionsRepositoryDecorator' => 'repository_decorator.test_entity_various_permissions_annotation',
    );

    parent::setUp();
  }
}
