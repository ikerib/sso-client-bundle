<?php

declare(strict_types=1);

namespace Pasaia\SsoClientBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Placeholder controller for the OIDC callback route (/sso/callback).
 *
 * The Symfony RouterListener runs at priority 32, before the security firewall
 * (priority 8). Without a real route, the router throws 404 before drenso's
 * OidcAuthenticator can intercept the request via its check_path.
 *
 * This controller exists so the route is matched by the router. The firewall
 * intercepts all requests that contain both "code" and "state" parameters
 * (the normal OIDC callback). This controller only runs if the request arrives
 * without those parameters (e.g. a direct browser visit to /sso/callback).
 */
final class SsoCallbackController
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function callback(Request $request): Response
    {
        // The security firewall intercepts requests with code+state (normal OIDC callback).
        // If we reach this controller it means the firewall didn't handle the request:
        // either the SSO returned an error response, or the user navigated here directly.
        //
        // If we redirect to login on an SSO error, the client immediately goes back to
        // the SSO authorize endpoint, which returns the same error, creating an infinite loop.
        if ($request->query->has('error')) {
            return new Response(
                sprintf('SSO error: %s — %s', $request->query->get('error'), $request->query->get('error_description', '')),
                Response::HTTP_BAD_GATEWAY,
            );
        }

        return new RedirectResponse($this->urlGenerator->generate('pasaia_sso_client_login'));
    }
}
