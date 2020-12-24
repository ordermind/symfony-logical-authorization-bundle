<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model;

use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface as LogicalAuthorizationUserInterface;
use Serializable;
use Symfony\Component\Security\Core\User\UserInterface;

class ErroneousUser implements UserInterface, LogicalAuthorizationUserInterface, Serializable
{
    private ?int $id;

    private string $username;

    private string $password;

    private ?string $oldPassword;

    private array $roles;

    private string $email;

    private bool $bypassAccess;

    public function __construct(
        string $username = '',
        string $password = '',
        array $roles = [],
        string $email = '',
        bool $bypassAccess = false
    ) {
        if ($username) {
            $this->setUsername($username);
        }
        if ($password) {
            $this->setPassword($password);
        }
        $this->setRoles($roles);
        if ($email) {
            $this->setEmail($email);
        }
        $this->setBypassAccess($bypassAccess);
    }

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
     * {@inheritDoc}
     */
    public function getIdentifier()
    {
        return $this->getId();
    }

    /**
     * Set username.
     *
     * @param string $username
     *
     * @return self
     */
    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username.
     *
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * Set password.
     *
     * @param string $password
     *
     * @return self
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password.
     *
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Set old password.
     *
     * @param string $oldPassword
     *
     * @return self
     */
    public function setOldPassword(string $password): self
    {
        $encoder = new BCryptPasswordEncoder(static::bcryptStrength);
        $this->oldPassword = $encoder->encodePassword($password, $this->getSalt());

        return $this;
    }

    /**
     * Get old password.
     *
     * @return string|null
     */
    public function getOldPassword(): ?string
    {
        return $this->oldPassword;
    }

    /**
     * Set roles.
     *
     * @param array $roles
     *
     * @return self
     */
    public function setRoles(array $roles): self
    {
        if (array_search('ROLE_USER', $roles) === false) {
            array_unshift($roles, 'ROLE_USER');
        }
        $this->roles = $roles;

        return $this;
    }

    /**
     * Get roles. Please use getFilteredRoles() instead.
     *
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Get filtered roles.
     *
     * @return array
     */
    public function getFilteredRoles(): array
    {
        $roles = $this->roles;
        if (($key = array_search('ROLE_USER', $roles)) !== false) {
            unset($roles[$key]);
        }

        return $roles;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return self
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Set bypassAccess.
     *
     * @param bool $bypassAccess
     *
     * @return LogicalAuthorizationUserInterface
     */
    public function setBypassAccess(bool $bypassAccess): LogicalAuthorizationUserInterface
    {
        $this->bypassAccess = $bypassAccess;

        return $this;
    }

    /**
     * Get bypassAccess.
     *
     * @return bool
     */
    public function getBypassAccess(): bool
    {
        return (string) $this->bypassAccess;
    }

    public function getSalt(): ?string
    {
        return null; //bcrypt doesn't require a salt.
    }

    public function eraseCredentials()
    {
    }

    public function serialize(): string
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
        ]);
    }

    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->username,
            $this->password
        ) = unserialize($serialized);
    }
}
