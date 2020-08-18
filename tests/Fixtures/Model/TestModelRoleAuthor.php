<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model;

use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

class TestModelRoleAuthor implements ModelInterface
{
    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string
     */
    private $field1 = '';

    /**
     * @var string
     */
    private $field2 = '';

    /**
     * @var string
     */
    private $field3 = '';

    /**
     * @var UserInterface|null
     */
    protected $author;

    /**
     * Get id.
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Set field1.
     *
     * @param string $field1
     *
     * @return self
     */
    public function setField1(string $field1): self
    {
        $this->field1 = $field1;

        return $this;
    }

    /**
     * Get field1.
     *
     * @return string
     */
    public function getField1(): string
    {
        return $this->field1;
    }

    /**
     * Set field2.
     *
     * @param string $field2
     *
     * @return self
     */
    public function setField2(string $field2): self
    {
        $this->field2 = $field2;

        return $this;
    }

    /**
     * Get field2.
     *
     * @return string
     */
    public function getField2(): string
    {
        return $this->field2;
    }

    /**
     * Set field3.
     *
     * @param string $field3
     *
     * @return self
     */
    public function setField3(string $field3): self
    {
        $this->field3 = $field3;

        return $this;
    }

    /**
     * Get field3.
     *
     * @return string
     */
    public function getField3(): string
    {
        return $this->field3;
    }

    /**
     * Set author.
     *
     * @param UserInterface $author
     *
     * @return ModelInterface
     */
    public function setAuthor(UserInterface $author): ModelInterface
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get authorId.
     *
     * @return UserInterface|null
     */
    public function getAuthor(): ?UserInterface
    {
        return $this->author;
    }
}
