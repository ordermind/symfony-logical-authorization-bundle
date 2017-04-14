<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ODM\Functional\Services;

class LogicalAuthorizationODMAnnotationTest extends LogicalAuthorizationODMBase
{
  /**
   * This method is run before each public test method
   */
  protected function setUp() {
    $this->load_services = array(
      'testDocumentRoleAuthorRepositoryManager' => 'repository_manager.test_document_roleauthor_annotation',
      'testDocumentHasAccountNoInterfaceRepositoryManager' => 'repository_manager.test_document_hasaccount_annotation',
      'testDocumentNoBypassRepositoryManager' => 'repository_manager.test_document_nobypass_annotation',
    );

    parent::setUp();
  }
}
