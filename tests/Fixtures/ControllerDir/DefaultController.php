<?php

declare(strict_types=1);

namespace Ordermind\LogicalAuthorizationBundle\Test\Fixtures\ControllerDir;

use Ordermind\LogicalAuthorizationBundle\Annotation\Routing\Permissions;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    /**
     * @Route("/route-has-account", name="route_user_has_account")
     *
     * @Method({"GET"})
     *
     * @Permissions({
     *   "condition": "user_has_account"
     * })
     */
    public function routeHasAccountAction()
    {
        return new Response('');
    }

    /**
     * @Route("/multiple-route-1", name="multiple_route_1")
     * @Route("/multiple-route-2")
     *
     * @Permissions({
     *  "role": "ROLE_ADMIN"
     * })
     */
    public function multipleRouteAction()
    {
        return new Response('');
    }

    /**
     * @Route("/pattern-allowed", name="pattern_allowed")
     */
    public function patternAllowedAction()
    {
        return new Response('');
    }

    /**
     * @Route("/pattern-forbidden", name="pattern_forbidden")
     *
     * @Permissions({
     *   "no_bypass": true,
     *   FALSE
     * })
     */
    public function patternForbiddenAction()
    {
        return new Response('');
    }

    /**
     * @Route("/route-allowed", name="route_allowed")
     *
     * @Permissions({
     *   TRUE
     * })
     */
    public function routeAllowedAction()
    {
        return new Response('');
    }

    /**
     * @Route("/route-denied", name="route_denied")
     *
     * @Permissions({
     *   FALSE
     * })
     */
    public function routeDeniedAction()
    {
        return new Response('');
    }

    /**
     * @Route("/route-forbidden", name="route_forbidden")
     */
    public function routeForbiddenAction()
    {
        return new Response('');
    }

    /**
     * @Route("/count-available-routes", name="count_available_routes")
     *
     * @Method({"GET"})
     */
    public function countAvailableRoutesAction()
    {
        $laRoute = $this->get('test.logauth.service.logauth_route');
        $result = $laRoute->getAvailableRoutes();
        if (empty($result['routes'])) {
            return new Response(0);
        }

        return new Response((string) count($result['routes']));
    }

    /**
     * @Route("/count-available-route-patterns", name="count_available_route_patterns")
     *
     * @Method({"GET"})
     */
    public function countAvailableRoutePatternsAction()
    {
        $laRoute = $this->get('test.logauth.service.logauth_route');
        $result = $laRoute->getAvailableRoutes();
        if (empty($result['route_patterns'])) {
            return new Response(0);
        }

        return new Response((string) count($result['route_patterns']));
    }

    /**
     * @Route("/get-current-username", name="get_current_username")
     *
     * @Method({"GET"})
     */
    public function getCurrentUsernameAction()
    {
        $user = $this->get('test.logauth.service.helper')->getCurrentUser();
        if (is_null($user)) {
            return new Response($user);
        }
        if (is_string($user)) {
            return new Response($user);
        }

        return new Response($user->getUsername());
    }

    /**
     * @Route("/count-forbidden-entities-lazy", name="test_count_forbidden_entities_lazy")
     *
     * @Method({"GET"})
     */
    public function countForbiddenEntitiesLazyLoadAction()
    {
        $operations = $this->get('test_model_operations');
        $operations->setRepositoryDecorator($this->get('repository_decorator.forbidden_entity'));
        $collection = $operations->getLazyLoadedModelResult();

        return new Response((string) count($collection));
    }
}
