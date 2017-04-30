<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ODM\Functional\Services;

class LogicalAuthorizationODMYMLTest extends LogicalAuthorizationODMBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testDocumentRoleAuthorRepositoryDecorator' => 'repository_decorator.test_document_roleauthor_yml',
      'testDocumentHasAccountNoInterfaceRepositoryDecorator' => 'repository_decorator.test_document_hasaccount_yml',
      'testDocumentNoBypassRepositoryDecorator' => 'repository_decorator.test_document_nobypass_yml',
      'testDocumentOverriddenPermissionsRepositoryDecorator' => 'repository_decorator.test_document_overridden_permissions_yml',
      'testDocumentVariousPermissionsRepositoryDecorator' => 'repository_decorator.test_document_various_permissions_yml',
    );

    parent::setUp();
  }
}
