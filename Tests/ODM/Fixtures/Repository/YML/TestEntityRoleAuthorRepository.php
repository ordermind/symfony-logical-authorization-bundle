<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ODM\Fixtures\Repository\YML;

/**
 * TestDocumentRoleAuthorRepository
 *
 * This class was generated by the Doctrine ODM. Add your own custom
 * repository methods below.
 */
class TestDocumentRoleAuthorRepository extends \Doctrine\ODM\DocumentRepository
{
  public function customMethod() {
    return $this->findAll();
  }
}
