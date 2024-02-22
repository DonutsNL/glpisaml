<?php

namespace GlpiPlugin\Glpisaml\Config;

use GlpiPlugin\Glpisaml\Config\ConfigEntity;

/**
 * Provides a default template for new configurations
 * Future: use ConfigEntities database to store additional templates
 */
class ConfigDefaultTpl {
    public static function template(): array
    {
        // Do not define the 'id' field, this will break the ConfigEntity logic.
        return [ ConfigEntity::NAME             => 'Default Azure',
                 ConfigEntity::CONF_DOMAIN      => 'youruserdomain.tld',
                 ConfigEntity::CONF_ICON        => 'fa-brands fa-microsoft',
                 ConfigEntity::ENFORCE_SSO      => false,
                 ConfigEntity::PROXIED          => false,
                 ConfigEntity::STRICT           => false,
                 ConfigEntity::DEBUG            => false,
                 ConfigEntity::USER_JIT         => true,
                 ConfigEntity::SP_CERTIFICATE   => 'base64 encoded service provider certificate string',
                 ConfigEntity::SP_KEY           => 'base64 encoded service provider private key string',
                 ConfigEntity::SP_NAME_FORMAT   => 'emailaddress',
                 ConfigEntity::IDP_ENTITY_ID    => 'Azure SAML Entity ID string',
                 ConfigEntity::IDP_SSO_URL      => 'Azure SAML Single Sign On url',
                 ConfigEntity::IDP_SLO_URL      => 'Azure SAML Singlo Logoff Url',
                 ConfigEntity::IDP_CERTIFICATE  => 'Azure base 64 encoded public certificate string',
                 ConfigEntity::AUTHN_CONTEXT    => 'none',
                 ConfigEntity::AUTHN_COMPARE    => 'exact',
                 ConfigEntity::ENCRYPT_NAMEID   => false,
                 ConfigEntity::SIGN_AUTHN       => false,
                 ConfigEntity::SIGN_SLO_REQ     => false,
                 ConfigEntity::SIGN_SLO_RES     => false,
                 ConfigEntity::COMPRESS_REQ     => false,
                 ConfigEntity::COMPRESS_RES     => false,
                 ConfigEntity::XML_VALIDATION   => true,
                 ConfigEntity::LOWERCASE_URL    => true,
                 ConfigEntity::COMMENT          => 'Azure example configuration',
                 ConfigEntity::IS_ACTIVE        => false
        ];
    }
}
