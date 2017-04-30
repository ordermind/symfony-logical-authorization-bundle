<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Functional\Services;

class LogicalAuthorizationORMXMLTest extends LogicalAuthorizationORMBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testEntityRoleAuthorRepositoryDecorator' => 'repository_decorator.test_entity_roleauthor_xml',
      'testEntityHasAccountNoInterfaceRepositoryDecorator' => 'repository_decorator.test_entity_hasaccount_xml',
      'testEntityNoBypassRepositoryDecorator' => 'repository_decorator.test_entity_nobypass_xml',
      'testEntityOverriddenPermissionsRepositoryDecorator' => 'repository_decorator.test_entity_overridden_permissions_xml',
      'testEntityVariousPermissionsRepositoryDecorator' => 'repository_decorator.test_entity_various_permissions_xml',
    );

    parent::setUp();
  }
}
