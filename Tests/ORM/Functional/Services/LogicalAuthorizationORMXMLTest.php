<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Functional\Services;

class LogicalAuthorizationORMXMLTest extends LogicalAuthorizationORMBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testEntityRoleAuthorRepositoryManager' => 'repository_manager.test_entity_roleauthor_xml',
      'testEntityHasAccountNoInterfaceRepositoryManager' => 'repository_manager.test_entity_hasaccount_xml',
      'testEntityNoBypassRepositoryManager' => 'repository_manager.test_entity_nobypass_xml',
    );

    parent::setUp();
  }
}
