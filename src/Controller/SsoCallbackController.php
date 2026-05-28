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
        // The security firewall should intercept requests with code+state.
        // If we reach this controller, the parameters are missing — redirect to login.
        return new RedirectResponse($this->urlGenerator->generate('pasaia_sso_client_login'));
    }
}
