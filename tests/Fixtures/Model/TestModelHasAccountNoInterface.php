<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model;

use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

class TestModelHasAccountNoInterface
{
    private ?int $id;

    private string $field1 = '';

    private string $field2 = '';

    private string $field3 = '';

    protected ?UserInterface $author;

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
     * @return self
     */
    public function setAuthor(UserInterface $author): self
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
