<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Misc\Functional\Services;

class LogicalAuthorizationMiscXMLTest extends LogicalAuthorizationMiscBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testEntityOverriddenPermissionsRepositoryManager' => 'repository_manager.test_entity_overridden_permissions_xml',
    );

    parent::setUp();
  }
}
