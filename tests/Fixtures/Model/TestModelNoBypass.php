<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model;

use Ordermind\LogicalAuthorizationBundle\Interfaces\ModelInterface;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface;

class TestModelNoBypass implements ModelInterface
{
    private ?int $id;

    private string $field1 = '';

    private string $field2 = '';

    private string $field3 = '';

    protected ?UserInterface $author;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setField1(string $field1): self
    {
        $this->field1 = $field1;

        return $this;
    }

    public function getField1(): string
    {
        return $this->field1;
    }

    public function setField2(string $field2): self
    {
        $this->field2 = $field2;

        return $this;
    }

    public function getField2(): string
    {
        return $this->field2;
    }

    public function setField3(string $field3): self
    {
        $this->field3 = $field3;

        return $this;
    }

    public function getField3(): string
    {
        return $this->field3;
    }

    /**
     * {@inheritDoc}
     */
    public function setAuthor(UserInterface $author): ModelInterface
    {
        $this->author = $author;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getAuthor(): ?UserInterface
    {
        return $this->author;
    }
}
