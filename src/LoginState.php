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

use Session;
use Migration;
use CommonDBTM;
use DBConnection;
use GlpiPlugin\Glpisaml\Exclude;

class LoginState extends CommonDBTM
{
    // CLASS CONSTANTS
    public const SESSION_GLPI_NAME_ACCESSOR = 'glpiname';       // NULL -> Populated with user->name in Session::class:128 after GLPI login->init;
    public const SESSION_VALID_ID_ACCESSOR  = 'valid_id';       // NULL -> Populated with session_id() in Session::class:107 after GLPI login;
    public const STATE_ID                   = 'id';             // State identifier
    public const USER_ID                    = 'userId';         // Glpi user_id
    public const USER_NAME                  = 'userName';       // The username
    public const SESSION_ID                 = 'sessionId';      // php session_id;
    public const SESSION_NAME               = 'sessionName';    // Php session_name();
    public const GLPI_AUTHED                = 'glpiAuthed';     // Session authed by GLPI
    public const SAML_AUTHED                = 'samlAuthed';     // Session authed by SAML
    public const LOGIN_DATETIME             = 'loginTime';      // When did we first see the session
    public const LAST_ACTIVITY              = 'lastClickTime';  // When did we laste update the session
    public const LOCATION                   = 'location';       // Request path
    public const ENFORCE_LOGOFF             = 'enforceLogoff';  // Do we want to enforce a logoff (one time)
    public const EXCLUDED_PATH              = 'excludedPath';   // If request was made using saml bypass.
    public const IDP_ID                     = 'idpId';          // What IdP handled the Auth?
    public const SERVER_PARAMS              = 'serverParams';   // Serialized $_SERVER object for SIEM analysis
    public const REQUEST_PARAMS             = 'requestParams';  // Serialized $_REQUEST object for SIEM analysis
    public const LOGGED_OFF                 = 'loggedOff';      // Was session logged off?
    public const STATE_VALID                = 'stateValid';     // Was the state considered valid?

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
        return $this->populateState();
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
     * Decide what to do and execute methods to populate or update state.
     * @param   void
     * @return  void    - true on valid session
     * @since   1.0.0
     */
    private function populateState(): bool
    {
        global $DB;

        // Evaluate if the call is excluded from saml auth
        // populate state accordingly.
        $this->isExcluded();

        // See if we are a new or existing session.
        $result = $DB->request(['FROM' => self::getTable(), 'WHERE' => [self::SESSION_ID => session_id()]]);
        if (count($result) == 1){
            // Validate and update
            return $this->updateState();
        }elseif(count($result) > 1){
            // This should never happen!
            // Maybe there is a scenario we want to handle/invalidate here.
        }else{
            // no registration exists.
            return $this->registerState();
        }
    }

    protected function isExcluded(): void
    {
        //https://github.com/derricksmith/phpsaml/issues/159
        // Dont perform auth on CLI, asserter service and manually excluded files.  
        if (PHP_SAPI == 'cli'                                    ||
            Exclude::ProcessExcludes()                           ||
            strpos($_SERVER['REQUEST_URI'], 'acs.php') !== false ){ 
             $this->state[self::EXCLUDED_PATH] = $_SERVER['REQUEST_URI'];
        }else{
            $this->state[self::EXCLUDED_PATH] = '';
        }
    }

    private function getGlpiState(): bool
    {
        // Verify if user is allready authenticated by GLPI.
        // Name_Accessor: Populated with user->name in Session::class:128 after GLPI login->init;
        // Id_Accessor: Populated with session_id() in Session::class:107 after GLPI login;
        return (isset($_SESSION[self::SESSION_GLPI_NAME_ACCESSOR]) && isset($_SESSION[self::SESSION_VALID_ID_ACCESSOR])) ? true : false;
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
    private function registerState(): bool   //NOSONAR - WIP
    {
        $serverParams[] = $_SERVER;
        $requestParams[] = $_REQUEST;
        $vars = [
            self::USER_ID           => '0',
            self::USER_NAME         => '',
            self::SESSION_ID        => session_id(),
            self::SESSION_NAME      => session_name(),
            self::GLPI_AUTHED       => $this->getGlpiState(),
            self::SAML_AUTHED       => false,
            self::LOCATION          => '/',
            self::ENFORCE_LOGOFF    => 0,
            self::EXCLUDED_PATH     => $this->state[self::EXCLUDED_PATH],
            self::IDP_ID            => null,
            self::SERVER_PARAMS     => serialize($serverParams),
            self::REQUEST_PARAMS    => serialize($requestParams),
            self::LOGGED_OFF        => 0,
            self::STATE_VALID       => $this->state[self::STATE_VALID],
        ];
        $this->state = $vars;
        if(!$this->add($vars)){
            Session::addMessageAfterRedirect(__('⭕ Unable to register session state in database, phpsaml wont function properly!', PLUGIN_NAME));
            return false;
        }else{
            return true;
        }
        // Do something
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
                `userId`                    int {$default_key_sign} NOT NULL,
                `userName`                  varchar(255) NULL,
                `sessionId`                 varchar(255) NOT NULL,
                `sessionName`               varchar(255) NOT NULL,
                `glpiAuthed`                tinyint {$default_key_sign} NULL,
                `samlAuthed`                tinyint {$default_key_sign} NULL,
                `loginTime`                 timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `lastClickTime`             timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `location`                  text NOT NULL,
                `enforceLogoff`             tinyint {$default_key_sign} NULL,
                `excludedPath`              text NULL,
                `idpId`                     int NULL,
                `serverParams`              text NULL,
                `requestParams`             text NULL,
                `loggedOff`                 tinyint {$default_key_sign} NULL,
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
