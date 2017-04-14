<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Functional\Services;

class LogicalAuthorizationORMYMLTest extends LogicalAuthorizationORMBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testEntityRoleAuthorRepositoryManager' => 'repository_manager.test_entity_roleauthor_yml',
      'testEntityHasAccountNoInterfaceRepositoryManager' => 'repository_manager.test_entity_hasaccount_yml',
      'testEntityNoBypassRepositoryManager' => 'repository_manager.test_entity_nobypass_yml',
    );

    parent::setUp();
  }
}
