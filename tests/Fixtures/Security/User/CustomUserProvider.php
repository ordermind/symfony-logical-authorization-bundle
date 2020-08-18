<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Security\User;

use Ordermind\LogicalAuthorizationBundle\Test\Fixtures\Model\TestUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class CustomUserProvider implements UserProviderInterface
{
    public function loadUserByUsername(string $username): UserInterface
    {
        $userdata = $this->getUserData();

        if (!empty($userdata[$username])) {
            $userData = $userdata[$username];

            return new TestUser(
                $username,
                $userData['password'],
                $userData['roles'],
                $userData['email'],
                !empty($userData['bypass_access'])
            );
        }

        throw new UsernameNotFoundException(
            sprintf('Username "%s" does not exist.', $username)
        );
    }

    public function refreshUser(UserInterface $user): UserInterface
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

    private function getUserData(): array
    {
        return [
        'authenticated_user' => [
        'password' => 'userpass',
        'roles'    => ['ROLE_USER'],
        'email'    => 'user@email.com',
        ],
        'admin_user' => [
        'password' => 'adminpass',
        'roles'    => [
          'ROLE_USER',
          'ROLE_ADMIN',
        ],
        'email' => 'admin@email.com',
        ],
        'superadmin_user' => [
        'password'      => 'superadminpass',
        'roles'         => ['ROLE_USER'],
        'email'         => 'superadmin@email.com',
        'bypass_access' => true,
        ],
        ];
    }
}
