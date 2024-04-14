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
 *  @version    1.1.0
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
use Migration;
use DBConnection;
use CommonDropdown;

/**
 * Be carefull with PSR4 Namespaces when extending common GLPI objects.
 * Only Characters are allowed in namespaces extending glpi Objects.
 * @see https://github.com/pluginsGLPI/example/issues/51
 */
class Exclude extends CommonDropdown
{
    /**
     * Exclude DB fields
     */
    const NAME              = 'name';
    const DATE_CREATION     = 'date_creation';
    const DATE_MOD          = 'date_mod';
    const CLIENTAGENT       = 'ClientAgent';
    const EXCLUDEPATH       = 'ExcludePath';
    const ACTION            = 'action';


    public static $rightname = 'config';
    
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
        return __('SAML Excludes', PLUGIN_NAME);
    }

    /**
     * Overloads missing canCreate Setup right and returns canUpdate instead
     *
     * @return bool     - Returns true if profile assgined Setup->Setup->Update right
     * @see             - https://github.com/pluginsGLPI/example/issues/50
     */
    public static function canCreate(): bool
    {
        return static::canUpdate();
    }

    /**
     * Overloads missing canPurge Setup right and returns canUpdate instead
     *
     * @return bool     - Returns true if profile assgined Setup->Setup->Update right
     * @see             - https://github.com/pluginsGLPI/example/issues/50
     */
    public static function canPurge(): bool
    {
        return static::canUpdate();
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
            $menu['page']  = '/' . PLUGIN_GLPISAML_WEBDIR . '/front/exclude.php';
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
        return 'fa-regular fa-eye-slash';
    }

    /**
     * getAdditionalFields(): array
     * Fetch fields for Dropdown 'add' form. Array order is equal with
     * field order in the form
     *
     * @return array   additional fields for dropdown
     */
    public function getAdditionalFields(): array
    {
        return [
            [
                'name'      => 'ClientAgent',
                'label'     => __('Agent contains', PLUGIN_NAME),
                'type'      => 'text',
                'list'      => true,
            ],
            [
                'name'      => 'action',
                'label'     => __('Bypass SAML auth', PLUGIN_NAME),
                'type'      => 'bool',
            ],
            [
                'name'      => 'ExcludePath',
                'label'     => __('Url contains path or file', PLUGIN_NAME),
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
     * getExcludes(): array -
     * Get configured excludes from Excludes dropdown
     *
     * @return array    Array with all configured patterns
     * @since           1.1.0
     */
    public static function getExcludes(): array
    {
        global $DB;
        $excludes = [];
        $dropdown = new Exclude();
        $table = $dropdown::getTable();
        foreach($DB->request($table) as $id => $row){                           //NOSONAR - For readability
            $excludes[] = [self::NAME                => $row[self::NAME],
                           self::ACTION              => $row[self::ACTION],
                           self::DATE_CREATION       => $row[self::DATE_CREATION],
                           self::DATE_MOD            => $row[self::DATE_MOD],
                           self::CLIENTAGENT         => $row[self::CLIENTAGENT],
                           self::EXCLUDEPATH         => $row[self::EXCLUDEPATH]];
        }
        return $excludes;
    }

    /**
     * Process aexcluded from SAML auth return true if excluded.
     *
     * @return bool     On success
     * @since           1.1.0
     */
    public static function ProcessExcludes(): bool                                                         //NOSONAR - Maybe fix complexity in future.
    {
        $excludes = self::getExcludes();
        // Process configured excluded URIs and agents.
        foreach($excludes as $exclude){
            if (strpos($_SERVER['REQUEST_URI'], $exclude[Exclude::EXCLUDEPATH]) !== false) {
                // Do we need to validate client agent?
                if(!empty($exclude[Exclude::CLIENTAGENT])                                        &&         //NOSONAR - Maybe fix verbosity in future.
                   strpos($_SERVER['HTTP_USER_AGENT'], $exclude[Exclude::CLIENTAGENT]) !== false ){
                    return ($exclude[Exclude::ACTION]) ? true : false;
                }else{
                    // return configured action true for bypass, false for auth.
                    return ($exclude[Exclude::ACTION]) ? true : false;
                }
            } // Else Continue
        }
        return false;
    }

    /**
     * Combines database excludes with hardcoded excludes.
     * @since   1.0.0
     */
    public static function isExcluded(): string|bool
    {
        //https://github.com/derricksmith/phpsaml/issues/159
        // Dont perform auth on CLI, asserter service and manually excluded files.
        if (PHP_SAPI == 'cli'                                            ||
            strpos($_SERVER['REQUEST_URI'], 'acs.php') !== false         ||         // dont process acs
            strpos($_SERVER['REQUEST_URI'], 'common.tabs.php') !== false ||         // dont process common.tabs
            strpos($_SERVER['REQUEST_URI'], 'dashboard.php') !== false   ||         // dont process dashboard
            Exclude::ProcessExcludes()                                   ){
             return $_SERVER['REQUEST_URI'];
        }else{
             return false;
        }
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
            $query = <<<SQL
            CREATE TABLE IF NOT EXISTS `$table` (
                `id`                        int {$default_key_sign} NOT NULL AUTO_INCREMENT,
                `name`                      varchar(255) DEFAULT NULL,
                `comment`                   text,
                `date_creation`             timestamp NULL DEFAULT NULL,
                `date_mod`                  timestamp NULL DEFAULT NULL,
                `ClientAgent`               text      NOT NULL,
                `ExcludePath`               text      NOT NULL,
                `action`                    tinyint unsigned NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=COMPRESSED;
            SQL;
            $DB->doQuery($query) or die($DB->error());
            Session::addMessageAfterRedirect("🆗 Installed: $table.");
            // insert default excludes;
            $query = <<<SQL
            INSERT INTO `$table`(name, comment, action, ClientAgent, ExcludePath)
            VALUES('Bypass Cron.php', 'backport configuration', '1', '', '/front/cron.php');
            SQL;
            $DB->doQuery($query) or die($DB->error());

            // insert default excludes;
            $query = <<<SQL
            INSERT INTO `$table`(name, comment, action, ClientAgent, ExcludePath)
            VALUES('Bypass Inventory.php', '', '1', '', 'front/inventory.php');
            SQL;
            $DB->doQuery($query) or die($DB->error());

            // insert default excludes;
            $query = <<<SQL
            INSERT INTO `$table`(name, comment, action, ClientAgent, ExcludePath)
            VALUES('Bypass ldap_mass_sync.php', '', '1', '', 'ldap_mass_sync.php');
            SQL;
            $DB->doQuery($query) or die($DB->error());

            // insert default excludes;
            $query = <<<SQL
            INSERT INTO `$table`(name, comment, action, ClientAgent, ExcludePath)
            VALUES('Bypass apirest.php', '', '1', '', 'apirest.php');
            SQL;
            $DB->doQuery($query) or die($DB->error());

            // insert default excludes;
            $query = <<<SQL
            INSERT INTO `$table`(name, comment, action, ClientAgent, ExcludePath)
            VALUES('Bypass all fusioninventory files', '', '1', '', '/fusioninventory/');
            SQL;
            $DB->doQuery($query) or die($DB->error());
            Session::addMessageAfterRedirect("🆗 Inserted exclude defaults.");
        }
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
        Session::addMessageAfterRedirect("🆗 Removed: $table");
        $migration->dropTable($table);
    }
}
