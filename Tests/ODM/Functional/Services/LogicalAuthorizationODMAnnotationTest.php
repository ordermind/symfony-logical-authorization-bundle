<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ODM\Functional\Services;

class LogicalAuthorizationODMAnnotationTest extends LogicalAuthorizationODMBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testDocumentRoleAuthorRepositoryDecorator' => 'repository_decorator.test_document_roleauthor_annotation',
      'testDocumentHasAccountNoInterfaceRepositoryDecorator' => 'repository_decorator.test_document_hasaccount_annotation',
      'testDocumentNoBypassRepositoryDecorator' => 'repository_decorator.test_document_nobypass_annotation',
      'testDocumentOverriddenPermissionsRepositoryDecorator' => 'repository_decorator.test_document_overridden_permissions_annotation',
      'testDocumentVariousPermissionsRepositoryDecorator' => 'repository_decorator.test_document_various_permissions_annotation',
    );

    parent::setUp();
  }
}
