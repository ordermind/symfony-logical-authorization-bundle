<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Functional\Services;

class LogicalAuthorizationORMYMLTest extends LogicalAuthorizationORMBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testEntityRoleAuthorRepositoryDecorator' => 'repository_decorator.test_entity_roleauthor_yml',
      'testEntityHasAccountNoInterfaceRepositoryDecorator' => 'repository_decorator.test_entity_hasaccount_yml',
      'testEntityNoBypassRepositoryDecorator' => 'repository_decorator.test_entity_nobypass_yml',
      'testEntityOverriddenPermissionsRepositoryDecorator' => 'repository_decorator.test_entity_overridden_permissions_yml',
      'testEntityVariousPermissionsRepositoryDecorator' => 'repository_decorator.test_entity_various_permissions_yml',
    );

    parent::setUp();
  }
}
