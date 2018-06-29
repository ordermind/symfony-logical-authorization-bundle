<?php
declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as VoterBase;
use Symfony\Component\HttpFoundation\Request;

use Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationRouteInterface;

/**
 * {@inheritdoc}
 */
class RequestVoter extends VoterBase
{
    protected $laRoute;

  /**
   * @internal
   *
   * @param \Ordermind\LogicalAuthorizationBundle\Services\LogicalAuthorizationRouteInterface $laRoute LogicalAuthorizationRoute service for checking route access
   */
    public function __construct(LogicalAuthorizationRouteInterface $laRoute)
    {
        $this->laRoute = $laRoute;
    }

  /**
   * {@inheritdoc}
   */
    protected function supports($attribute, $subject): bool
    {
        if (strtolower($attribute) === 'logauth' && $subject instanceof Request) {
            return true;
        }

        return false;
    }

  /**
   * {@inheritdoc}
   */
    protected function voteOnAttribute($attribute, $request, TokenInterface $token): bool
    {
        $routeName = $request->get('_route');
        if ($routeName) {
            return $this->laRoute->checkRouteAccess($routeName);
        }

        return true;
    }
}
