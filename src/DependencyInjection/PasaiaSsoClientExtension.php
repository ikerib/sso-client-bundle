<?php

declare(strict_types=1);

namespace Pasaia\SsoClientBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class PasaiaSsoClientExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Prepends drenso_oidc configuration so apps only need SSO_* env vars.
     *
     * This runs in MergeExtensionConfigurationPass before any extension's load() is called,
     * so drenso_oidc gets a complete client config even if the app has no drenso_oidc.yaml.
     *
     * Apps can still override individual fields in their own drenso_oidc.yaml — app config
     * is appended after this prepend and takes precedence for any key it defines.
     */
    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('drenso_oidc', [
            'clients' => [
                'default' => [
                    // SSO_ISSUER_URL must NOT have a trailing slash.
                    'well_known_url' => '%env(SSO_ISSUER_URL)%/.well-known/openid-configuration',
                    'client_id' => '%env(SSO_CLIENT_ID)%',
                    'client_secret' => '%env(SSO_CLIENT_SECRET)%',
                    // Must match the firewall check_path and the bundle's /sso/callback route.
                    'redirect_route' => '/sso/callback',
                    // Cache the JWKS and discovery document for 1 hour to reduce SSO load.
                    'jwks_cache_time' => 3600,
                    'well_known_cache_time' => 3600,
                    // PKCE S256 — the SSO requires code_challenge by default for all clients.
                    'code_challenge_method' => 'S256',
                ],
            ],
        ]);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new YamlFileLoader($container, new FileLocator(\dirname(__DIR__, 2) . '/config'));
        $loader->load('services.yaml');

        $container->setParameter('pasaia_sso_client.scopes', $config['scopes']);
        $container->setParameter('pasaia_sso_client.post_logout_redirect_uri', $config['post_logout_redirect_uri']);
        $container->setParameter('pasaia_sso_client.login_route', $config['login_route']);
    }
}
