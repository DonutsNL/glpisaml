<?php
/**
 *  ------------------------------------------------------------------------
 *  GLPISaml
 *
 *  GLPISaml was inspired by the initial work of Derrick Smith's
 *  PhpSaml. This project's intend is to address some structural issues
 *  caused by the gradual development of GLPI and the broad ammount of
 *  wishes expressed by the community. 
 *
 *  Copyright (C) 2024 by Chris Gralike
 *  ------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPISaml project.
 *
 * GLPISaml plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GLPISaml is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with GLPISaml. If not, see <http://www.gnu.org/licenses/> or
 * https://choosealicense.com/licenses/gpl-3.0/
 *
 * ------------------------------------------------------------------------
 *
 *  @package    GLPISaml
 *  @version    1.0.0
 *  @author     Chris Gralike
 *  @copyright  Copyright (c) 2024 by Chris Gralike
 *  @license    GPLv3+
 *  @see        https://github.com/DonutsNL/GLPISaml/readme.md
 *  @link       https://github.com/DonutsNL/GLPISaml
 *  @since      1.0.0
 * ------------------------------------------------------------------------
 **/

 namespace GlpiPlugin\Glpisaml;

use Session;
use Plugin;
use Migration;
use CommonGLPI;
use CommonDBTM;
use CommonDropdown;
use DBConnection;

class Config extends CommonDBTM
{
    public const ID             = 'id';
    public const NAME           = 'name';
    public const CONF_DOMAIN    = 'conf_domain';            // idea is to 'detect' the correct idp config using the provided 'username's domain' in the loginform.
    public const CONF_ICON      = 'conf_icon';
    public const ENFORCE_SSO    = 'enforce_sso';
    public const PROXIED        = 'proxied';
    public const STRICT         = 'strict';
    public const DEBUG          = 'debug';
    public const USER_JIT       = 'user_jit';
    public const SP_CERTIFICATE = 'sp_certificate';
    public const SP_KEY         = 'sp_private_key';
    public const SP_NAME_FORMAT = 'sp_nameid_format';
    public const IDP_ENTITY_ID  = 'idp_entity_id';
    public const IDP_SSO_URL    = 'idp_single_sign_on_service';
    public const IDP_SLO_URL    = 'idp_single_logout_service';
    public const IDP_CERTIFICATE= 'idp_certificate';
    public const AUTHN_CONTEXT  = 'requested_authn_context';
    public const AUTHN_COMPARE  = 'requested_authn_context_comparison';
    public const ENCRYPT_NAMEID = 'security_nameidencrypted';
    public const SIGN_AUTHN     = 'security_authnrequestssigned';
    public const SIGN_SLO_REQ   = 'security_logoutrequestsigned';
    public const SIGN_SLO_RES   = 'security_logoutresponsesigned';
    public const COMPRESS_REQ   = 'compress_requests';
    public const COMPRESS_RES   = 'compress_responses';
    public const XML_VALIDATION = 'validate_xml';
    public const DEST_VALIDATION= 'validate_destination';  // relax destination validation
    public const LOWERCASE_URL  = 'lowercase_url_encoding'; // lowercaseUrlEncoding

    // From CommonDBTM
    public $dohistory = true;

    // 'Config (setup) only implements canRead and canUpdate
    public static $rightname = 'config';

    // use 'Update' as canCreate permission
    public static function canCreate()
    {
        return static::canUpdate();
    }

    // use 'Update' as canPurge permission
    public static function canPurge()
    {
        return static::canUpdate();
    }

    public static function getTypeName($nb = 0)
    {
        return __('SAML2 Config', PLUGIN_NAME);
    }

    public static function getIcon() : string
    {
        return 'fas fa-address-book';
    }

    function rawSearchOptions() : array                         //NOSONAR - phpcs:ignore PSR1.Function.CamelCapsMethodName
    {
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


    /**
     * install(Migration migration) : void -
     * Install table needed for Ticket Filter configuration dropdowns
     *
     * @return void
     * @see             hook.php:plugin_GLPISaml_install()
     */
    public static function install(Migration $migration) : void
    {
        global $DB;
        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();
        $table = self::getTable();

        // Create the base table if it does not yet exist;
        // Dont update this table for later versions, use the migration class;
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");
            $query = <<<SQL
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
            $DB->query($query) or die($DB->error());
            Session::addMessageAfterRedirect("Installed: $table.");
        }

        // Debug entries TODO: Delete when implemented.
        $query = <<<SQL
        INSERT INTO $table
        VALUES('1', 'test 1', '@Donuts.nl', 'icon', '1', '1', '1', '1', '1', 'sp_cert',
               'sp_key','email address','idp_entity_id','sso_url','slo_url','idp_cert',
               'authn1','authn2','1','1','1','1','1','1','1','1','1','comment','1','0',
               CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
        SQL;
        $DB->query($query) or die($DB->error());
        //
        $query = <<<SQL
        INSERT INTO $table
        VALUES('2', 'test 2', '@Donuts.nl', 'icon', '1', '1', '1', '1', '1', 'sp_cert',
               'sp_key','email address','idp_entity_id','sso_url','slo_url','idp_cert',
               'authn1','authn2','1','1','1','1','1','1','1','1','1','comment','1','0',
               CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
        SQL;
        $DB->query($query) or die($DB->error());
        //
        $query = <<<SQL
        INSERT INTO $table
        VALUES('3', 'test 3', '@Donuts.nl', 'icon', '1', '1', '1', '1', '1', 'sp_cert',
               'sp_key','email address','idp_entity_id','sso_url','slo_url','idp_cert',
               'authn1','authn2','1','1','1','1','1','1','1','1','1','comment','1','0',
               CURRENT_TIMESTAMP, CURRENT_TIMESTAMP);
        SQL;
        $DB->query($query) or die($DB->error());
    }

    /**
     * uninstall(Migration migration) : void -
     * Uninstall tables uncomment the line to make plugin clean table.
     *
     * @return void
     * @see             hook.php:plugin_GLPISaml_uninstall()
     */
    public static function uninstall(Migration $migration) : void
    {
        $table = self::getTable();
        $migration->backupTables([$table]);
        Session::addMessageAfterRedirect("Backupped: $table.");
        $migration->dropTable($table);
        Session::addMessageAfterRedirect("Removed: $table.");
    }
}
