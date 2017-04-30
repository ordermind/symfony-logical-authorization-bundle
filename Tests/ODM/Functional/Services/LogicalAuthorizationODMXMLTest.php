<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ODM\Functional\Services;

class LogicalAuthorizationODMXMLTest extends LogicalAuthorizationODMBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testDocumentRoleAuthorRepositoryDecorator' => 'repository_decorator.test_document_roleauthor_xml',
      'testDocumentHasAccountNoInterfaceRepositoryDecorator' => 'repository_decorator.test_document_hasaccount_xml',
      'testDocumentNoBypassRepositoryDecorator' => 'repository_decorator.test_document_nobypass_xml',
      'testDocumentOverriddenPermissionsRepositoryDecorator' => 'repository_decorator.test_document_overridden_permissions_xml',
      'testDocumentVariousPermissionsRepositoryDecorator' => 'repository_decorator.test_document_various_permissions_xml',
    );

    parent::setUp();
  }
}
