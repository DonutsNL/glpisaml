<?php
/**
 * ------------------------------------------------------------------------
 * GLPISaml
 *
 * GLPISaml is heavily influenced by the initial work of Derrick Smith's
 * PhpSaml. This project's intent is to address some structural issues and
 * changes made by the gradual development of GLPI and provide a free, safe
 * and functional way of implementing SAML authentication in GLPI.
 *
 * Copyright (C) 2024 by Chris Gralike
 * ------------------------------------------------------------------------
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
 *
 * We have a GLPI state and a SAML state. This class is intended to manage
 * and validate both states and their validity at all times. It should for
 * instance allow the the plugin to invalidate a session for what ever reason
 * and force a user to login again. It also allows future SIEM integration
 * to forcefully invalidate any active sessions.
 *
 **/

namespace GlpiPlugin\Glpisaml;

use Htlm;               // Provides Html::redirectToLogin();
use Cache;
use Session;
use Migration;
use CommonDBTM;
use DBConnection;

class LoginState extends CommonDBTM
{
    // CLASS CONSTANTS
    public const SESSION_GLPI_NAME_ACCESSOR = 'glpiname';       // NULL -> Populated with user->name in Session::class:128 after GLPI login->init;
    public const SESSION_VALID_ID_ACCESSOR  = 'valid_id';       // NULL -> Populated with session_id() in Session::class:107 after GLPI login;
    public const USER_NAME                  = 'username';       // Glpi username
    public const USER_ID                    = 'userId';         // Glpi user_id
    public const SESSION_ID                 = 'glpiSessionId';  // Glpi session_id
    public const SESSION_ACTIVE             = 'sessionActive';
    public const LOGIN_DATETIME             = 'loginTime';
    public const LAST_ACTIVITY              = 'lastClickTime';
    public const SAML_CONDITIONS_BEFORE     = 'notBefore';
    public const SAML_CONDITIONS_AFTER      = 'notOnOrAfter';
    public const ENFORCE_LOGOFF             = 'enforceLogoff';
    public const IDP_ID                     = 'idpId';
    public const USER_AGENT                 = 'userAgent';
    public const REMOTE_IP                  = 'remoteIP';

    /**
     * Restore object if version has been cached and trigger
     * validation to make sure the session isnt hijacked
     *
     * @param   void
     * @return  void
     * @since   1.0.0
     */
    public function __construct()
    {
        // Get previous state from cache;
        global $GLPI_CACHE;
        $self = $GLPI_CACHE->get('GLPISaml_loginstateObj');
        echo "$self";
        //$GLPI_CACHE->set('phpsaml_'.session_id(), true); //NOSONAR - WIP
    }

    /**
     * Clean all type.resource for serialization;
     *
     * @param   void
     * @return  void
     * @since   1.0.0
     */
    public function __sleep(){}

    /**
     * Restore all type.resource for deserialization;
     *
     * @param   void
     * @return  void
     * @since   1.0.0
     */
    public function __wakeup(){}


    /**
     * Validate user is correctly authenticated in both the external
     * Idp aswell as GLPI.
     * @param   void
     * @return  void
     * @since   1.0.0
     */
    public function isAuthenticated() : bool
    {
        return ($this->isSamlAuthenticated() &&
                $this->isGlpiAuthenticated() )? true : false;
    }

    /**
     * Validate user is correctly authenticated with external Idp
     * @param   void
     * @return  bool    - true on valid session
     * @since   1.0.0
     */
    private function isSamlAuthenticated() : bool
    {
        return true;
    }

    /**
     * Validate user is correctly authenticated with GLPI
     * @param   void
     * @return  bool    - true on valid GLPI session
     * @since   1.0.0
     */
    private function isGlpiAuthenticated() : bool
    {
        // Versions prior to GLPI 0.85 dont support these indexes.
        return (isset($_SESSION[self::SESSION_GLPI_NAME_ACCESSOR])          &&
                isset($_SESSION[self::SESSION_VALID_ID_ACCESSOR])           &&
                $_SESSION[self::SESSION_VALID_ID_ACCESSOR] == session_id()  )? true : false;
    }

    /**
     * Update session state in the session LoginState database
     * for external (SIEM) evaluation and interaction
     * @param   void
     * @return  bool
     * @since   1.0.0
     */
    private function updateState() : bool   //NOSONAR - WIP
    {
        return true;
        // Do something
    }

    /**
     * Fetch the session state for a specific user
     * @param   int     $id         - Id of the session to fetch
     * @return  object  Loginstate  - instance of LoginState
     * @since   1.0.0
     */
    private function getState(int $id) : LoginState //NOSONAR - WIP
    {
        return $this;
    }

    /**
     * Forcefully invalidate the state of an active session
     * to enforce logoff and relogin.
     * @param   $id         - Id of the session to invalidate
     * @param   $message    - Logoff and log message
     * @return  bool        - Returns true on success
     * @since   1.0.0
     */
    private function invalidateState(int $id, string $message) : bool   //NOSONAR - WIP
    {
        return true;
    }

    /**
     * Install the LoginState DB table
     * @param   Migration $obj
     * @return  void
     * @since   1.0.0
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
                `username`                  varchar(255) DEFAULT NULL,
                `userId`                    int {$default_key_sign} NOT NULL,
                `glpiSessionId`             varchar(255) DEFAULT NULL,
                `sessionActive`             tinyint {$default_key_sign} NULL,
                `loginTime`                 timestamp NOT NULL,
                `lastClickTime`             timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `notBefore`                 timestamp NOT NULL,
                `notOnOrAfter`              timestamp NOT NULL,
                `enforceLogoff`             tinyint {$default_key_sign} NULL,
                `idpId`                     int NOT NULL,
                `userAgent`                 varchar(255) DEFAULT NULL,
                `remoteIP`                  varchar(255) DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=COMPRESSED;
            SQL;
            $DB->query($query) or die($DB->error());
            Session::addMessageAfterRedirect("Installed: $table.");
        }
    }

    /**
     * Uninstall the LoginState DB table
     * @param   Migration $obj
     * @return  void
     * @since   1.0.0
     */
    public static function uninstall(Migration $migration) : void
    {
        $table = self::getTable();
        Session::addMessageAfterRedirect("Removed: $table.");
        $migration->dropTable($table);
    }
    
}
