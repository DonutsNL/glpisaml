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
    public const GLPI_LOGGED_IN             = 'glpi_logged_in';
    public const SAML_LOGGED_IN             = 'saml_logged_in';
    public const LOGIN_DATETIME             = 'loginTime';
    public const LAST_ACTIVITY              = 'lastClickTime';
    public const SAML_CONDITIONS_BEFORE     = 'notBefore';
    public const SAML_CONDITIONS_AFTER      = 'notOnOrAfter';
    public const ENFORCE_LOGOFF             = 'enforceLogoff';
    public const IDP_ID                     = 'idpId';
    public const USER_AGENT                 = 'userAgent';
    public const REMOTE_IP                  = 'remoteIP';
    public const STATE_VALID                = 'stateValid';

    private $state = [self::STATE_VALID => true];

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
        // Populate the stateObject.
        $this->populateState();
    }


    /**
     * Validate user is correctly authenticated in both the external
     * Idp aswell as GLPI.
     * @param   void
     * @return  void
     * @since   1.0.0
     */
    public function getLoginState(): LoginState
    {
       return $this;
    }

    /**
     * Validates session state and db state and updates the LoginState object accordingly;
     * @param   void
     * @return  void    - true on valid session
     * @since   1.0.0
     */
    private function populateState(): void
    {
        global $DB;
        $this->evaluateGlpiState();
        // Verify session against registered states.
        // If GLPI is authenticated we should always have a registered session in the LoginState;
        $result = $DB->request(['FROM' => self::getTable(), 'WHERE' => [self::SESSION_ID => session_id()]]);
        if (count($result) == 1){
            // check if its a local or remotely provided auth
            // do something meaningfull
            var_dump($result);
            exit;
            
        }elseif(count($result) > 1){
            // This should never happen!
        }else{
            // no registration exists.
            $this->registerState();
        }
    }

    private function evaluateGlpiState(): void
    {
        // Verify if user is allready authenticated by GLPI.
        // Name_Accessor: Populated with user->name in Session::class:128 after GLPI login->init;
        // Id_Accessor: Populated with session_id() in Session::class:107 after GLPI login;
        $this->state[self::GLPI_LOGGED_IN] = ((isset($_SESSION[self::SESSION_GLPI_NAME_ACCESSOR])) &&
                                              (isset($_SESSION[self::SESSION_VALID_ID_ACCESSOR] )) )? true : false;
    }

    /**
     * Validate GLPI registered session or register it as new GLPI session
     * for SIEM purposes.
     * @param   void
     * @return  void
     * @since   1.0.0
     */
    private function handleGlpiSession(): void  //NOSONAR - WIP
    {
        // Do something
    }

    /**
     * Update session state in the session LoginState database
     * for external (SIEM) evaluation and interaction
     * @param   void
     * @return  bool
     * @since   1.0.0
     */
    private function updateState(): bool   //NOSONAR - WIP
    {
        return true;
        // Do something
    }

    /**
     * Register new session state in the session LoginState database
     * for external (SIEM) evaluation and interaction
     * @param   void
     * @return  bool
     * @since   1.0.0
     */
    public function registerState(): bool   //NOSONAR - WIP
    {
        return true;
        // Do something
    }

    public function isAuthenticated(): bool
    {
        return $this->state[self::GLPI_LOGGED_IN];
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
            Session::addMessageAfterRedirect("🆗 Installed: $table.");
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
        Session::addMessageAfterRedirect("🆗 Removed: $table.");
        $migration->dropTable($table);
    }
    
}
