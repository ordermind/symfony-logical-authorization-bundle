<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\ORM\Fixtures\Entity\User;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Ordermind\LogicalAuthorizationBundle\Annotation\Doctrine\LogicalAuthorizationPermissions;
use Ordermind\LogicalAuthorizationBundle\Interfaces\UserInterface as LogicalAuthorizationUserInterface;

/**
 * TestUser
 *
 * @ORM\Table(name="testusers")
 * @ORM\Entity(repositoryClass="Ordermind\LogicalAuthorizationBundle\Tests\ORM\Fixtures\Repository\TestUserRepository")
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
 *     "no_bypass": {
 *       "flag": "is_author"
 *     },
 *     "AND": {
 *       "role": "ROLE_ADMIN",
 *       "flag": {
 *         "NOT": "is_author"
 *       }
 *     }
 *   }
 * })
 */
class TestUser implements UserInterface, LogicalAuthorizationUserInterface, \Serializable
{
  /**
   * @var string
   *
   * @ORM\Column(name="id", type="guid")
   * @ORM\Id
   * @ORM\GeneratedValue(strategy="UUID")
   */
  private $id;

  /**
   * @var string
   *
   * @ORM\Column(name="username", type="string", length=25)
   * @LogicalAuthorizationPermissions({
   *   "get": {
   *     "OR": {
   *       "role": "ROLE_ADMIN",
   *       "flag": "is_author"
   *     }
   *   },
   *   "set": {
   *     "role": "ROLE_ADMIN"
   *   }
   * })
   */
  private $username;

  /**
   * @var string
   *
   * @ORM\Column(name="password", type="string", length=64)
   */
  private $password;

  /**
   * @var string
   * @LogicalAuthorizationPermissions({
   *   "set": {
   *     "no_bypass": true,
   *     "flag": "is_author"
   *   }
   * })
   */
  private $oldPassword;

  /**
   * @var array
   *
   * @ORM\Column(name="roles", type="json_array")
   * @LogicalAuthorizationPermissions({
   *   "get": {
   *     "role": "ROLE_ADMIN"
   *   },
   *   "set": {
   *     "AND": {
   *       "role": "ROLE_ADMIN",
   *       "flag": {
   *         "NOT": "is_author"
   *       }
   *     }
   *   }
   * })
   */
  private $roles;

  /**
   * @var string
   *
   * @ORM\Column(name="email", type="string", length=60)
   */
  private $email;

  /**
   * @var bool
   *
   * @ORM\Column(name="bypassAccess", type="boolean")
   */
  private $bypassAccess;

  public function __construct($username = '', $password = '', $roles = [], $email = '', $bypassAccess = false) {
    if($username) {
      $this->setUsername($username);
    }
    if($password) {
      $this->setPassword($password);
    }
    $this->setRoles($roles);
    if($email) {
      $this->setEmail($email);
    }
    $this->setBypassAccess($bypassAccess);
  }


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
   * Set username
   *
   * @param string $username
   *
   * @return TestUser
   */
  public function setUsername($username)
  {
    $this->username = $username;

    return $this;
  }

  /**
   * Get username
   *
   * @return string
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * Set password
   *
   * @param string $password
   *
   * @return TestUser
   */
  public function setPassword($password)
  {
    $this->password = $password;

    return $this;
  }

  /**
   * Get password
   *
   * @return string
   */
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * Set old password
   *
   * @param string $oldPassword
   *
   * @return TestUser
   */
  public function setOldPassword($password)
  {
    $encoder = new BCryptPasswordEncoder(static::bcryptStrength);
    $this->oldPassword = $encoder->encodePassword($password, $this->getSalt());

    return $this;
  }

  /**
   * Get old password
   *
   * @return string
   */
  public function getOldPassword()
  {
    return $this->oldPassword;
  }

  /**
   * Set roles
   *
   * @return array
   */
  public function setRoles($roles) {
    if(array_search('ROLE_USER', $roles) === false) {
      array_unshift($roles, 'ROLE_USER');
    }
    $this->roles = $roles;
  }

  /**
   * Get roles. Please use getFilteredRoles() instead.
   *
   * @return array
   */
  public function getRoles() {
    return $this->roles;
  }

  /**
   * Get filtered roles.
   *
   * @return array
   */
  public function getFilteredRoles() {
    $roles = $this->roles;
    if(($key = array_search('ROLE_USER', $roles)) !== false) {
      unset($roles[$key]);
    }
    return $roles;
  }

  /**
   * Set email
   *
   * @param string $email
   *
   * @return TestUser
   */
  public function setEmail($email)
  {
    $this->email = $email;

    return $this;
  }

  /**
   * Get email
   *
   * @return string
   */
  public function getEmail()
  {
    return $this->email;
  }

  /**
   * Set bypassAccess
   *
   * @param boolean $bypassAccess
   *
   * @return TestUser
   */
  public function setBypassAccess($bypassAccess)
  {
    $this->bypassAccess = $bypassAccess;

    return $this;
  }

  /**
   * Get bypassAccess
   *
   * @return bool
   */
  public function getBypassAccess()
  {
    return $this->bypassAccess;
  }

  public function getSalt() {
    return null; //bcrypt doesn't require a salt.
  }

  public function eraseCredentials() {

  }

  public function serialize() {
    return serialize(array(
      $this->id,
      $this->username,
      $this->password,
    ));
  }

  public function unserialize($serialized) {
    list (
      $this->id,
      $this->username,
      $this->password,
    ) = unserialize($serialized);
  }
}

