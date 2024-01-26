<?php
// temp deposit of code for debugging.
$var = <<<SQL
    CREATE TABLE `$table` (
    `id`                            INT {$default_key_sign} NOT NULL auto_increment,
    `name`                          VARCHAR(255) NOT NULL,
    `conf_domain`                   VARCHAR(50) NOT NULL,
    `conf_icon`                     VARCHAR(50) NOT NULL,
    `enforce_sso`                   tinyint NOT NULL DEFAULT '0',
    `proxied`                       tinyint NOT NULL DEFAULT '0',
    `strict`                        tinyint NOT NULL DEFAULT '0',
    `debug`                         tinyint NOT NULL DEFAULT '0',
    `user_jit`                      tinyint NOT NULL DEFAULT '0',
    `sp_certificate`                TEXT NOT NULL,
    `sp_private_key`                TEXT NOT NULL,
    `sp_nameid_format`              VARCHAR(128) NOT NULL,
    `idp_entity_id`                 VARCHAR(128) NOT NULL,
    `idp_single_sign_on_service`    VARCHAR(128) NOT NULL,
    `idp_single_logout_service`     VARCHAR(128) NOT NULL,
    `idp_certificate`               TEXT NOT NULL,
    `requested_authn_context`       TEXT NOT NULL,
    `requested_authn_context_comparison` VARCHAR(25) NOT NULL,
    `security_nameidencrypted`      tinyint NOT NULL DEFAULT '0',
    `security_authnrequestssigned`  tinyint NOT NULL DEFAULT '0',
    `security_logoutrequestsigned`  tinyint NOT NULL DEFAULT '0',
    `security_logoutresponsesigned` tinyint NOT NULL DEFAULT '0',
    `compress_requests`             tinyint NOT NULL DEFAULT '0',
    `compress_responses`            tinyint NOT NULL DEFAULT '0',
    `validate_xml`                  tinyint NOT NULL DEFAULT '0',
    `validate_destination`          tinyint NOT NULL DEFAULT '0',
    `lowercase_url_encoding`        tinyint NOT NULL DEFAULT '0',
    `comment`                       text NULL,
    `is_active`                     tinyint NOT NULL DEFAULT '0',
    `is_deleted`                    tinyint NOT NULL default '0',
    `date_creation`                 timestamp NULL DEFAULT CURRENT_TIMESTAMP,
    `date_mod`                      timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=COMPRESSED;
    SQL;
/**
     * rawSearchOptions() : array -
     * Add fields to search and potential table columns
     * @return array   $rawSearchOptions
     */
    function rawSearchOptions() : array
    {
        /*
        $tab[] = [
            'id'                 => '1',
            'table'              => $this->getTable(),
            'field'              => self::ID,
            'name'               => __('ID'),
            'massiveaction'      => false,
            'datatype'           => 'itemlink'
        ];
        */

        $tab[] = [
            'id'                 => 'common',
            'name'               => __('Generic')
        ];

        $tab[] = [
            'id'                 => '2',
            'table'              => $this->getTable(),
            'field'              => self::NAME,
            'name'               => __('Name'),
            'datatype'           => 'itemlink',
            'massiveaction'      => false,
            'autocomplete'       => true,
        ];

        $tab[] = [
            'id'                 => '3',
            'table'              => $this->getTable(),
            'field'              => self::CONF_DOMAIN,
            'name'               => __('Domain string'),
            'datatype'           => 'string',
            'massiveaction'      => false,
            'autocomplete'       => true,
        ];

        $tab[] = [
            'id'                 => '4',
            'table'              => $this->getTable(),
            'field'              => self::CONF_ICON,
            'name'               => __('Configuration Icon'),
            'datatype'           => 'string',
            'massiveaction'      => false,
            'autocomplete'       => true,
        ];

        $tab[] = [
            'id'                 => '5',
            'table'              => $this->getTable(),
            'field'              => self::ENFORCE_SSO,
            'name'               => __('Force SSO'),
            'datatype'           => 'bool',
            'massiveaction'      => true,
        ];

        $tab[] = [
            'id'                 => '6',
            'table'              => $this->getTable(),
            'field'              => self::PROXIED,
            'name'               => __('Proxied Responses'),
            'datatype'           => 'bool',
            'massiveaction'      => true,
        ];

        $tab[] = [
            'id'                 => '7',
            'table'              => $this->getTable(),
            'field'              => self::STRICT,
            'name'               => __('Strict mode'),
            'datatype'           => 'bool',
            'massiveaction'      => true,
        ];

        $tab[] = [
            'id'                 => '8',
            'table'              => $this->getTable(),
            'field'              => self::DEBUG,
            'name'               => __('Debug mode'),
            'datatype'           => 'bool',
            'massiveaction'      => true,
        ];

        $tab[] = [
            'id'                 => '9',
            'table'              => $this->getTable(),
            'field'              => self::USER_JIT,
            'name'               => __('Automatic user creation'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '10',
            'table'              => $this->getTable(),
            'field'              => self::SP_CERTIFICATE,
            'name'               => __('Service Provider Certificate'),
            'datatype'           => 'text',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '11',
            'table'              => $this->getTable(),
            'field'              => self::SP_KEY,
            'name'               => __('Service Provider private Key'),
            'datatype'           => 'text',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '12',
            'table'              => $this->getTable(),
            'field'              => self::SP_NAME_FORMAT,
            'name'               => __('Name format'),
            'datatype'           => 'string',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '13',
            'table'              => $this->getTable(),
            'field'              => self::IDP_ENTITY_ID,
            'name'               => __('IdP entity identifier'),
            'datatype'           => 'string',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '14',
            'table'              => $this->getTable(),
            'field'              => self::IDP_SSO_URL,
            'name'               => __('IdP Single Sign On Url'),
            'datatype'           => 'string',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '15',
            'table'              => $this->getTable(),
            'field'              => self::IDP_SLO_URL,
            'name'               => __('IdP Logoff Url'),
            'datatype'           => 'string',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '16',
            'table'              => $this->getTable(),
            'field'              => self::IDP_CERTIFICATE,
            'name'               => __('IdP Public Certificate'),
            'datatype'           => 'text',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '17',
            'table'              => $this->getTable(),
            'field'              => self::AUTHN_CONTEXT,
            'name'               => __('AuthN Context'),
            'datatype'           => 'string',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '18',
            'table'              => $this->getTable(),
            'field'              => self::AUTHN_COMPARE,
            'name'               => __('AuthN Compare'),
            'datatype'           => 'string',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '19',
            'table'              => $this->getTable(),
            'field'              => self::ENCRYPT_NAMEID,
            'name'               => __('Force SSO'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '20',
            'table'              => $this->getTable(),
            'field'              => self::ENCRYPT_NAMEID,
            'name'               => __('Encrypted nameId'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '21',
            'table'              => $this->getTable(),
            'field'              => self::SIGN_AUTHN,
            'name'               => __('Sign AuthN Requests'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '22',
            'table'              => $this->getTable(),
            'field'              => self::SIGN_SLO_REQ,
            'name'               => __('Sign SLO Requests'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '23',
            'table'              => $this->getTable(),
            'field'              => self::SIGN_AUTHN,
            'name'               => __('Sign SLO Responses'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '24',
            'table'              => $this->getTable(),
            'field'              => self::COMPRESS_REQ,
            'name'               => __('Compress requests'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '25',
            'table'              => $this->getTable(),
            'field'              => self::COMPRESS_RES,
            'name'               => __('Compress responses'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '26',
            'table'              => $this->getTable(),
            'field'              => self::XML_VALIDATION,
            'name'               => __('Perform XML validation'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '27',
            'table'              => $this->getTable(),
            'field'              => self::DEST_VALIDATION,
            'name'               => __('Perform destination validation'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        $tab[] = [
            'id'                 => '28',
            'table'              => $this->getTable(),
            'field'              => self::LOWERCASE_URL,
            'name'               => __('Perform lowercase encoding'),
            'datatype'           => 'bool',
            'massiveaction'      => false,
        ];

        return $tab;
    }

    