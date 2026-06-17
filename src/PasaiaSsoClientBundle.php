<?php

declare(strict_types=1);

namespace Pasaia\SsoClientBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Thin wrapper over drenso/symfony-oidc-bundle for the SSO Municipal Pasaia.
 *
 * Responsibilities:
 *  - PasaiaSsoClientExtension::prepend() injects drenso_oidc config from SSO_* env vars.
 *  - Registers OidcUserProvider and SsoLoginController as services.
 *  - Exposes /sso/login and /sso/callback routes.
 */
class PasaiaSsoClientBundle extends Bundle
{
    /**
     * Return the bundle root directory (one level above src/).
     * Required when the bundle class lives in src/ but config/ is at the package root.
     */
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
