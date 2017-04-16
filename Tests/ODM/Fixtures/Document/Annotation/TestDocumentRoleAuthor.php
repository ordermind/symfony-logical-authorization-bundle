<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ODM\Fixtures\Document\Annotation;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Ordermind\LogicalAuthorizationBundle\Annotation\Doctrine\LogicalAuthorizationPermissions;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;

/**
 * TestDocumentRoleAuthor
 *
 * @ODM\Document(repositoryClass="Ordermind\LogicalAuthorizationBundle\Tests\ODM\Fixtures\Repository\Annotation\TestDocumentRoleAuthorRepository", collection="testdocuments_roleauthor_annotation")
 * @LogicalAuthorizationPermissions({
 *   "create": {
 *     "role": "ROLE_ADMIN"
 *   },
 *   "read": {
 *     "OR": {
 *       "role": "ROLE_ADMIN",
 *       "flag": "is_author"
 *     }
 *   },
 *   "update": {
 *     "OR": {
 *       "role": "ROLE_ADMIN",
 *       "flag": "is_author"
 *     }
 *   },
 *   "delete": {
 *     "OR": {
 *       "role": "ROLE_ADMIN",
 *       "flag": "is_author"
 *     }
 *   }
 * })
 */
class TestDocumentRoleAuthor implements ModelInterface
{
    /**
     * @var int
     *
     * @ODM\Field(name="id", type="integer")
     * @ODM\Id
     */
    private $id;

    /**
     * @var string
     *
     * @ODM\Field(name="field1", type="string")
     * @LogicalAuthorizationPermissions({
     *   "get": {
     *     "role": "ROLE_ADMIN",
     *     "flag": "is_author"
     *   },
     *   "set": {
     *     "role": "ROLE_ADMIN",
     *     "flag": "is_author"
     *   }
     * })
     */
    private $field1 = '';

    /**
     * @var string
     *
     * @ODM\Field(name="field2", type="string")
     */
    private $field2 = '';

    /**
     * @var string
     *
     * @ODM\Field(name="field3", type="string")
     */
    private $field3 = '';

    /**
     * @var \Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface
     * @ODM\ReferenceOne(targetDocument="Ordermind\LogicalAuthorizationBundle\Tests\ODM\Fixtures\Document\User\TestUser")
     */
    protected $author;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set field1
     *
     * @param string $field1
     *
     * @return TestDocumentRoleAuthor
     */
    public function setField1($field1)
    {
        $this->field1 = $field1;

        return $this;
    }

    /**
     * Get field1
     *
     * @return string
     */
    public function getField1()
    {
        return $this->field1;
    }

    /**
     * Set field2
     *
     * @param string $field2
     *
     * @return TestDocumentRoleAuthor
     */
    public function setField2($field2)
    {
        $this->field2 = $field2;

        return $this;
    }

    /**
     * Get field2
     *
     * @return string
     */
    public function getField2()
    {
        return $this->field2;
    }

    /**
     * Set field3
     *
     * @param string $field3
     *
     * @return TestDocumentRoleAuthor
     */
    public function setField3($field3)
    {
        $this->field3 = $field3;

        return $this;
    }

    /**
     * Get field3
     *
     * @return string
     */
    public function getField3()
    {
        return $this->field3;
    }

    /**
     * Set author
     *
     * @param \Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface $author
     *
     * @return document implementing ModelInterface
     */
    public function setAuthor(UserInterface $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get authorId
     *
     * @return \Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface
     */
    public function getAuthor() {
        return $this->author;
    }

}

