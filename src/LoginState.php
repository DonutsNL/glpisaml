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
 *  @version    1.1.0
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
use Exception;
use CommonDBTM;
use DBConnection;
use GlpiPlugin\Glpisaml\Exclude;


/*
 * The goal of this object is to keep track of the login state in the database.
 * this will allow us to 'influence' the login state of a specific session if
 * we want to, for instance to forcefully log someone off or force reauthentication.
 * we can also extend this session logging for (future) SIEM purposes.
 */
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
    public const LOCATION                   = 'location';       // Location requested;
    public const IDP_ID                     = 'idpId';          // What IdP handled the Auth?
    public const LOGIN_DATETIME             = 'loginTime';      // When did we first see the session
    public const LAST_ACTIVITY              = 'lastClickTime';  // When did we laste update the session
    public const ENFORCE_LOGOFF             = 'enforceLogoff';  // Do we want to enforce a logoff (one time)
    public const EXCLUDED_PATH              = 'excludedPath';   // If request was made using saml bypass.
    public const SAML_RESPONSE              = 'serverParams';   // Stores the Saml Response
    public const SAML_REQUEST               = 'requestParams';  // Stores the SSO request
    public const PHASE                      = 'phase';          // Describes the current state GLPI, ACS, TIMEOUT, LOGGEDIN, LOGGEDOUT.
    public const PHASE_INITIAL              = 1;                // Initial visit
    public const PHASE_SAML_ACS             = 2;                // Performed SAML IDP call expected back at ACS
    public const PHASE_SAML_AUTH            = 3;                // Succesfully performed IDP auth
    public const PHASE_GLPI_AUTH            = 4;                // Succesfully performed GLPI auth
    public const PHASE_FILE_EXCL            = 5;                // Excluded file called
    public const PHASE_FORCE_LOG            = 6;                // Session forced logged off
    public const PHASE_TIMED_OUT            = 7;                // Session Timed out
    public const PHASE_LOGOFF               = 8;                // Session was logged off
    public const DATABASE                   = 'database';       // State from database?

    private $state = [];

    /**
     * Restore object if version has been cached and trigger
     * validation to make sure the session isnt hijacked
     * @since   1.0.0
     */
    public function __construct()
    {
        // Get database state (if any)
        $this->getInitialState();
    }

    /**
     * Loads initial state into the $this->state property
     * @since   1.0.0
     */
    private function getInitialState(): void
    {
        // Evaluate if the call is excluded from saml auth
        // populate state accordingly.
        $this->state[self::EXCLUDED_PATH] = Exclude::isExcluded();
        $this->getGlpiState();
        $this->setGlpiUserName();
        $this->getLastActivity();
        

        global $DB;
        // See if we are a new or existing session.
        if(!$sessionIterator = $DB->request(['FROM' => self::getTable(), 'WHERE' => [self::SESSION_NAME => session_name()]])){
            throw new Exception('Could not fetch Login State from database');               //NOSONAR - We use generic Exceptions
        }

        if($sessionIterator->numrows() == 1){
            foreach($sessionIterator as $sessionState)
            {
                $this->state = array_merge($this->state,[
                    self::STATE_ID          => $sessionState[self::STATE_ID],
                    self::USER_ID           => $sessionState[self::USER_ID],
                    self::SESSION_ID        => $sessionState[self::SESSION_ID],
                    self::SESSION_NAME      => $sessionState[self::SESSION_NAME],
                    self::SAML_AUTHED       => (bool) $sessionState[self::SAML_AUTHED],
                    self::LOGIN_DATETIME    => $sessionState[self::LOGIN_DATETIME],
                    self::ENFORCE_LOGOFF    => $sessionState[self::ENFORCE_LOGOFF],
                    self::IDP_ID            => $sessionState[self::IDP_ID],
                    self::PHASE             => $sessionState[self::PHASE],
                    self::DATABASE          => true,
                ]);
            }
        }else{
            // Populate session using actuals
            $this->state = $this->state = array_merge($this->state,[
                self::USER_ID           => 0,
                self::SESSION_ID        => session_id(),
                self::SESSION_NAME      => session_name(),
                self::SAML_AUTHED       => false,
                self::ENFORCE_LOGOFF    => 0,
                self::EXCLUDED_PATH     => $this->state[self::EXCLUDED_PATH],
                self::IDP_ID            => null,
                self::DATABASE          => false,
            ]);
        }
        if(!$this->WriteStateToDb()){
            throw new Exception('Could not write database state to database');          //NOSONAR - We use generic Exceptions
        }
    }

    /**
     * Write the state into the database
     * for external (SIEM) evaluation and interaction
     *
     * @return  bool
     * @since   1.0.0
     */
    private function writeStateToDb(): bool   //NOSONAR - WIP
    {
        // Register state in database;
        if(!$this->state[self::EXCLUDED_PATH]){
            if(!$this->state[self::DATABASE]){
                if(!$this->add($this->state)){
                    return false;
                }
            }else{
                if(!$this->update($this->state)){
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Get and set last activity in state array
     * @since   1.0.0
     */
    private function getLastActivity(): void
    {
        $this->state[self::LOCATION] = $_SERVER['REQUEST_URI'];
        $this->state[self::LAST_ACTIVITY] = date('Y-m-d H:i:s');
    }

    /**
     * Gets glpi state from the SESSION superglobal and
     * updates the state array accordingly.
     *
     * @since   1.0.0
     */
    private function getGlpiState(): void
    {
        // Verify if user is allready authenticated by GLPI.
        // Name_Accessor: Populated with user->name in Session::class:128 after GLPI login->init;
        // Id_Accessor: Populated with session_id() in Session::class:107 after GLPI login;
        if (isset($_SESSION[self::SESSION_GLPI_NAME_ACCESSOR]) &&
            isset($_SESSION[self::SESSION_VALID_ID_ACCESSOR])  ){

            $this->state[self::GLPI_AUTHED] = true;
            $this->state[self::PHASE] = self::PHASE_GLPI_AUTH;
        } else {
            $this->state[self::GLPI_AUTHED] = false;
            $this->state[self::PHASE] = self::PHASE_INITIAL;
        }
    }

    /**
     * Update the loginPhase in the state database.
     * @param int   $phase ID
     * @since       1.0.0
     * @see         LoginState::PHASE_## constants for valid values
     */
    public function setPhase(int $phase): bool
    {
        if($phase > 0 && $phase <= 8){
            $this->state[self::PHASE] = $phase;
            return ($this->update($this->state)) ? true : false;
        }
        return false;
    }

    /**
     * Gets the current login phase
     * @return int  phase id
     * @see         LoginState::PHASE_## constants for valid values
     * @since       1.0.0
     */
    public function getPhase(): int
    {
        return (!empty($this->state[self::PHASE])) ? $this->state[self::PHASE] : 0;
    }

    /**
     * Sets the IdpId used in current session.
     * @param int   ConfigItem::ID pointing to IdP provider.
     * @since       1.0.0
     */
    public function setIdpId(int $idpId): bool
    {
        if($idpId > 0 && $idpId < 999){
            $this->state[self::IDP_ID] = $idpId;
            return ($this->update($this->state)) ? true : false;
        }else{
            return false;
        }
    }

    /**
     * Fetches current IdpId used in current session.
     * @return int  ConfigItem::ID pointing to IdP provider.
     * @since       1.0.0
     */
    public function getIdpId(): int
    {
        return (!empty($this->state[self::IDP_ID])) ? $this->state[self::IDP_ID] : 0;
    }

    /**
     * Adds SamlResponse to the state table
     * @param  string   json_encoded samlResponse
     * @return bool     true on success.
     * @since           1.0.0
     */
    public function setSamlResponseParams(string $samlResponse): bool
    {
        if($samlResponse > 0){
            $this->state[self::SAML_RESPONSE] = $samlResponse;
            return ($this->update($this->state)) ? true : false;
        }
        return false;
    }

    /**
     * Adds SamlRequest to the state table
     * @param  string   json_encoded samlRequest
     * @return bool     true on success.
     * @since           1.0.0
     */
    public function setRequestParams(string $samlRequest): bool
    {
        if($samlRequest > 0){
            $this->state[self::SAML_REQUEST] = $samlRequest;
            return ($this->update($this->state)) ? true : false;
        }
        return false;
    }

    // get the glpi Username and set it in the state.
    // If no user was identified, use remote IP as user.
    private function setGlpiUserName(): void
    {
        // Use remote ip as username if session is anonymous.
        $remote = (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) ? $_SERVER['HTTP_X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'];
        $this->state[self::USER_NAME] = (!empty($_SESSION[self::SESSION_GLPI_NAME_ACCESSOR])) ? $_SESSION[self::SESSION_GLPI_NAME_ACCESSOR] : $remote;
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
                `phase`                     text NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=COMPRESSED;
            SQL;
            $DB->doQuery($query) or die($DB->error());
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
