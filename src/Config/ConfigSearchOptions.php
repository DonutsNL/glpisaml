<?php

namespace GlpiPlugin\Glpisaml\Config;

use GlpiPlugin\Glpisaml\Config as SamlConfig;
use GlpiPlugin\Glpisaml\Config\ConfigEntity;

class ConfigSearchOptions extends SamlConfig{
    public static function get(): array
    {
        $tab[] = [
            'id'                 => '1',
            'table'              => SamlConfig::getTable(),
            'field'              => ConfigEntity::NAME,
            'name'               => __('Name'),
            'datatype'           => 'itemlink',
            'massiveaction'      => false,
            'autocomplete'       => true,
        ];

        $tab[] = [
            'id'                 => '2',
            'table'              => SamlConfig::getTable(),
            'field'              => ConfigEntity::CONF_DOMAIN,
            'name'               => __('Domain string'),
            'datatype'           => 'string',
            'massiveaction'      => false,
            'autocomplete'       => true,
        ];

        $tab[] = [
            'id'                 => '3',
            'table'              => SamlConfig::getTable(),
            'field'              => ConfigEntity::ENFORCE_SSO,
            'name'               => __('Force SSO'),
            'datatype'           => 'bool',
            'massiveaction'      => true,
        ];

        $tab[] = [
            'id'                 => '4',
            'table'              => SamlConfig::getTable(),
            'field'              => ConfigEntity::PROXIED,
            'name'               => __('Proxied Responses'),
            'datatype'           => 'bool',
            'massiveaction'      => true,
        ];

        $tab[] = [
            'id'                 => '5',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::STRICT,
            'name'               => __('Strict mode'),
            'datatype'           => 'bool',
            'massiveaction'      => true,
        ];

        $tab[] = [
            'id'                 => '6',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::DEBUG,
            'name'               => __('Debug mode'),
            'datatype'           => 'bool',
            'massiveaction'      => true,
        ];

        $tab[] = [
            'id'                 => '7',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::USER_JIT,
            'name'               => __('Automatic user creation'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '8',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::SP_NAME_FORMAT,
            'name'               => __('Name format'),
            'datatype'           => 'string',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '9',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::IDP_SSO_URL,
            'name'               => __('IdP Single Sign On Url'),
            'datatype'           => 'string',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '10',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::IDP_SLO_URL,
            'name'               => __('IdP Logoff Url'),
            'datatype'           => 'string',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '11',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::AUTHN_CONTEXT,
            'name'               => __('AuthN Context'),
            'datatype'           => 'string',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '12',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::AUTHN_COMPARE,
            'name'               => __('AuthN Compare'),
            'datatype'           => 'string',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '13',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::ENCRYPT_NAMEID,
            'name'               => __('Force SSO'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '14',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::ENCRYPT_NAMEID,
            'name'               => __('Encrypted nameId'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '15',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::SIGN_AUTHN,
            'name'               => __('Sign AuthN Requests'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '16',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::SIGN_SLO_REQ,
            'name'               => __('Sign SLO Requests'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '17',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::SIGN_AUTHN,
            'name'               => __('Sign SLO Responses'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '18',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::COMPRESS_REQ,
            'name'               => __('Compress requests'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '19',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::COMPRESS_RES,
            'name'               => __('Compress responses'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '20',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::XML_VALIDATION,
            'name'               => __('Perform XML validation'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '21',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::DEST_VALIDATION,
            'name'               => __('Perform destination validation'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '22',
            'table'              => SamlConfig::getTable()(),
            'field'              => ConfigEntity::LOWERCASE_URL,
            'name'               => __('Perform lowercase encoding'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        return $tab;
    }
}