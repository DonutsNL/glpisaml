<?php
/**
 *  ------------------------------------------------------------------------
 *  PhpSaml2
 *
 *  PhpSaml2 was inspired by the initial work of Derrick Smith's
 *  PhpSaml. This project's intend is to address some structural issues
 *  caused by the gradual development of GLPI and the broad ammount of
 *  wishes expressed by the community. 
 *
 *  Copyright (C) 2024 by Chris Gralike
 *  ------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of PhpSaml2 project.
 *
 * PhpSaml2 plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PhpSaml2 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with PhpSaml2. If not, see <http://www.gnu.org/licenses/> or
 * https://choosealicense.com/licenses/gpl-3.0/
 *
 * ------------------------------------------------------------------------
 *
 *  @package    PhpSaml2
 *  @version    1.0.0
 *  @author     Chris Gralike
 *  @copyright  Copyright (c) 2024 by Chris Gralike
 *  @license    GPLv3+
 *  @see        https://github.com/DonutsNL/phpSaml2/readme.md
 *  @link       https://github.com/DonutsNL/phpSaml2
 *  @since      1.0.0
 * ------------------------------------------------------------------------
 **/

namespace GlpiPlugin\Phpsaml2;

use Session;
use Migration;
use CommonDropdown;
use DBConnection;

class Config extends CommonDropdown
{
    public const ID             = 'id';
    public const NAME           = 'name';
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
    public const CONF_NAME      = 'configuration_name';
    public const CONF_ICON      = 'configuration_icon';
    public const COMPRESS_REQ   = 'compress_requests';
    public const COMPRESS_RES   = 'compress_responses';
    public const XML_VALIDATION = 'validate_xml';
    public const DEST_VALIDATION= 'validate_destination';  // relax destination validation
    public const LOWERCASE_URL  = 'lowercase_url_encoding'; // lowercaseUrlEncoding

     /**
     * getTypeName(int nb) : string -
     * Method called by pre_item_add hook validates the object and passes
     * it to the RegEx Matching then decides what to do.
     *
     * @param  int      $nb     number of items.
     * @return void
     */
    public static function getTypeName($nb = 0) : string
    {
        return _n('Saml Idp config', 'Saml Idp configs', $nb, PLUGIN_NAME);
    }

    /**
     * getMenuContent() : array | bool -
     * Method called by pre_item_add hook validates the object and passes
     * it to the RegEx Matching then decides what to do.
     *
     * @return mixed             boolean|array
     */
    public static function getMenuContent()
    {
        $menu = [];
        if (Config::canUpdate()) {
            $menu['title'] = self::getMenuName();
            $menu['page']  = '/' . PLUGIN_DIR . '/front/config.php';
            $menu['icon']  = self::getIcon();
        }
        if (count($menu)) {
          return $menu;
        }
        return false;
    }

    /**
     * getIcon() : string -
     * Sets icon for object.
     *
     * @return string   $icon
     */
    public static function getIcon() : string
    {
        return 'fas fa-check-square';
    }

    /**
     * getAdditionalFields() : array -
     * Fetch fields for Dropdown 'add' form. Array order is equal with
     * field order in the form
     *
     * @return string   $icon
     */
    public function getAdditionalFields()
    {
        return [
            [
                'name'      => self::ENFORCE_SSO,
                'label'     => __('Enforce SSO', PLUGIN_NAME),
                'type'      => 'bool',
                'list'      => true,
            ],
            [
                'name'      => self::PROXIED,
                'label'     => __('Handle proxied responses', PLUGIN_NAME),
                'type'      => 'bool',
            ],
            [
                'name'      => self::STRICT,
                'label'     => __('Strict mode', PLUGIN_NAME),
                'type'      => 'bool',
            ],
            [
                'name'      => self::DEBUG,
                'label'     => __('Enable debug', PLUGIN_NAME),
                'type'      => 'bool',
            ],
            [
                'name'      => self::USER_JIT,
                'label'     => __('Enable user JIT', PLUGIN_NAME),
                'type'      => 'bool',
            ],
            [
                'name'      => self::ENCRYPT_NAMEID,
                'label'     => __('Encrypt NameId', PLUGIN_NAME),
                'type'      => 'bool'
            ],
            [
                'name'      => self::SIGN_AUTHN,
                'label'     => __('SP Sign AUTHN', PLUGIN_NAME),
                'type'      => 'bool'
            ],
            [
                'name'      => self::SIGN_SLO_REQ,
                'label'     => __('SP Sign SLO Request', PLUGIN_NAME),
                'type'      => 'bool'
            ],
            [
                'name'      => self::SIGN_SLO_RES,
                'label'     => __('SP Sign SLO Response', PLUGIN_NAME),
                'type'      => 'bool'
            ],
            [
                'name'      => self::SIGN_SLO_REQ,
                'label'     => __('SP Sign SLO Request', PLUGIN_NAME),
                'type'      => 'bool'
            ],
            [
                'name'      => self::COMPRESS_REQ,
                'label'     => __('SP Compress Request', PLUGIN_NAME),
                'type'      => 'bool'
            ],
            [
                'name'      => self::COMPRESS_RES,
                'label'     => __('IDP Compress Response', PLUGIN_NAME),
                'type'      => 'bool'
            ],
            [
                'name'      => self::XML_VALIDATION,
                'label'     => __('SP Perform XML validation', PLUGIN_NAME),
                'type'      => 'bool'
            ],
            [
                'name'      => self::DEST_VALIDATION,
                'label'     => __('SP perform dest validation', PLUGIN_NAME),
                'type'      => 'bool'
            ],
            [
                'name'      => self::LOWERCASE_URL,
                'label'     => __('Lowercase URL encoding', PLUGIN_NAME),
                'type'      => 'bool'
            ],
            [
                'name'      => self::CONF_NAME,
                'label'     => __('Configuration name', PLUGIN_NAME),
                'type'      => 'text',
                'list'      => true,
                'min'       => 1,
            ],
            [
                'name'      => self::CONF_ICON,
                'label'     => __('Configuration icon', PLUGIN_NAME),
                'type'      => 'text',
                'list'      => true,
                'min'       => 1,
            ],
            [
                'name'      => self::SP_CERTIFICATE,
                'label'     => __('Serviceprovider certificate', PLUGIN_NAME),
                'type'      => 'text',
                'list'      => true,
                'min'       => 1,
            ],
            [
                'name'      => self::SP_KEY,
                'label'     => __('Serviceprovider private key', PLUGIN_NAME),
                'type'      => 'text',
                'list'      => true,
                'min'       => 1,
            ],
            [
                'name'      => self::SP_NAME_FORMAT,
                'label'     => __('Name formate', PLUGIN_NAME),
                'type'      => 'text',
                'list'      => true,
                'min'       => 1,
            ],
            [
                'name'      => self::IDP_ENTITY_ID,
                'label'     => __('Idp entity ID', PLUGIN_NAME),
                'type'      => 'text',
                'list'      => true,
                'min'       => 1,
            ],
            [
                'name'      => self::IDP_SSO_URL,
                'label'     => __('Idp Single Sign On URL', PLUGIN_NAME),
                'type'      => 'text',
                'list'      => true,
                'min'       => 1,
            ],
            [
                'name'      => self::IDP_SLO_URL,
                'label'     => __('Idp Logout URL', PLUGIN_NAME),
                'type'      => 'text',
                'list'      => true,
                'min'       => 1,
            ],
            [
                'name'      => self::IDP_CERTIFICATE,
                'label'     => __('Idp certificate', PLUGIN_NAME),
                'type'      => 'text',
                'list'      => true,
                'min'       => 1,
            ],
            [
                'name'      => self::AUTHN_CONTEXT,
                'label'     => __('Required AUTHN Context', PLUGIN_NAME),
                'type'      => 'text',
                'list'      => true,
                'min'       => 1,
            ],
            [
                'name'      => self::AUTHN_COMPARE,
                'label'     => __('AUTHN Comparison', PLUGIN_NAME),
                'type'      => 'text',
                'list'      => true,
                'min'       => 1,
            ],
        ];
    }

    /**
     * rawSearchOptions() : array -
     * Add fields to search and potential table columns
     *
     * @return array   $rawSearchOptions
     */
    public function rawSearchOptions() : array
    {
        $tab = parent::rawSearchOptions();

        $tab[] = [
            'id'                 => '5',
            'table'              => $this->getTable(),
            'field'              => 'ClientAgent',
            'name'               => __('Client Agent performing the call', PLUGIN_NAME),
            'searchtype'         => ['equals', 'notequals'],
            'datatype'           => 'text',
        ];

        $tab[] = [
            'id'                 => '6',
            'table'              => $this->getTable(),
            'field'              => 'ExcludePath',
            'name'               => __('To be excluded path', PLUGIN_NAME),
            'datatype'           => 'text',
        ];
        return $tab;
    }




    /**
     * install(Migration migration) : void -
     * Install table needed for Ticket Filter configuration dropdowns
     *
     * @return void
     * @see             hook.php:plugin_phpsaml2_install()
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
                `id` INT {$default_key_sign} NOT NULL auto_increment,
                `name` VARCHAR(255) NOT NULL,
                `enforce_sso` INT(2) unsigned NOT NULL,
                `proxied` INT(2) unsigned NOT NULL,
                `strict` INT(2) unsigned NOT NULL,
                `debug` INT(2) unsigned NOT NULL,
                `user_jit` INT(2) unsigned NOT NULL,
                `sp_certificate` TEXT NOT NULL,
                `sp_private_key` TEXT NOT NULL,
                `sp_nameid_format` VARCHAR(128) NOT NULL,
                `idp_entity_id` VARCHAR(128) NOT NULL,
                `idp_single_sign_on_service` VARCHAR(128) NOT NULL,
                `idp_single_logout_service` VARCHAR(128) NOT NULL,
                `idp_certificate` TEXT NOT NULL,
                `requested_authn_context` TEXT NOT NULL,
                `requested_authn_context_comparison` VARCHAR(25) NOT NULL,
                `security_nameidencrypted` INT(2) unsigned NOT NULL,
                `security_authnrequestssigned` INT(2) unsigned NOT NULL,
                `security_logoutrequestsigned` INT(2) unsigned NOT NULL,
                `security_logoutresponsesigned` INT(2) unsigned NOT NULL,
                `configuration_name` VARCHAR(50) NOT NULL,
                `configuration_icon` VARCHAR(50) NOT NULL,
                `compress_requests` INT(2) unsigned NOT NULL,
                `compress_responses` INT(2) unsigned NOT NULL,
                `validate_xml` INT(2) unsigned NOT NULL,
                `validate_destination` INT(2) unsigned NOT NULL,
                `lowercase_url_encoding` INT(2) unsigned NOT NULL,
                PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;
            SQL;
            $DB->query($query) or die($DB->error());
            Session::addMessageAfterRedirect("Installed: $table.");
        }
    }

    /**
     * uninstall(Migration migration) : void -
     * Uninstall tables uncomment the line to make plugin clean table.
     *
     * @return void
     * @see             hook.php:plugin_phpsaml2_uninstall()
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
