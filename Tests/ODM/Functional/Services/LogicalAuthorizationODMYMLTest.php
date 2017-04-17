<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ODM\Functional\Services;

class LogicalAuthorizationODMYMLTest extends LogicalAuthorizationODMBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testDocumentRoleAuthorRepositoryManager' => 'repository_manager.test_document_roleauthor_yml',
      'testDocumentHasAccountNoInterfaceRepositoryManager' => 'repository_manager.test_document_hasaccount_yml',
      'testDocumentNoBypassRepositoryManager' => 'repository_manager.test_document_nobypass_yml',
      'testDocumentOverriddenPermissionsRepositoryManager' => 'repository_manager.test_document_overridden_permissions_yml',
      'testDocumentVariousPermissionsRepositoryManager' => 'repository_manager.test_document_various_permissions_yml',
    );

    parent::setUp();
  }
}
