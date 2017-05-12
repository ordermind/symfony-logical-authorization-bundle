<?php

namespace Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Security\User;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Ordermind\LogicalAuthorizationBundle\Tests\Fixtures\Entity\TestUser;

class CustomUserProvider implements UserProviderInterface
{
    protected $users = [
      'authenticated_user' => [
        'password' => 'userpass',
        'roles' => ['ROLE_USER'],
        'email' => 'user@email.com',
      ],
      'admin_user' => [
        'password' => 'adminpass',
        'roles' => [
          'ROLE_USER',
          'ROLE_ADMIN',
        ],
        'email' => 'admin@email.com',
      ],
      'superadmin_user' => [
        'password' => 'superadminpass',
        'roles' =>  ['ROLE_USER'],
        'email' => 'superadmin@email.com',
        'bypass_access' => true,
      ],
    ];

    public function loadUserByUsername($username)
    {
        if (!empty($this->users[$username])) {
          $user_data = $this->users[$username];
          return new TestUser($username, $user_data['password'], $user_data['roles'], $user_data['email'], !empty($user_data['bypass_access']));
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof TestUser) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', get_class($user))
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return TestUser::class === $class;
    }
}
