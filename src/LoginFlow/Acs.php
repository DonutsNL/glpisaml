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

namespace GlpiPlugin\Glpisaml\LoginFlow;

use Throwable;
use OneLogin\Saml2\Utils;
use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Response;
use GlpiPlugin\Glpisaml\LoginFlow;
use GlpiPlugin\Glpisaml\Loginstate;
use GlpiPlugin\Glpisaml\Config\ConfigEntity;


/**
 * Responsible to handle the incomming samlResponse. This object should
 * validate we are actually expecting an response and if we do validate it
 * If the response is valid, perform a callback to the loginFlow to handle
 * authentication, user creation and what not. Class is called by /front/acs.php
 *
 * This class is intended to be very unforgivable given its the vulnerable nature
 * of the samlResponse assertion while providing enough logging for the administrator
 * to figure out whats going on and how to resolve or prevent issues.
 */
class Acs extends LoginFlow
{

    // Define some error headers we use allot, not the best place but ok for now.
    private const EXTENDED_HEADER = "================ BEGIN EXTENDED =================\n\n";
    private const EXTENDED_FOOTER = "================= END EXTENDED ==================\n\n";
    private const STATE_OBJ       = "###############    StateObj    ##################\n\n";
    private const RESPONSE_OBJ    = "###############    Response    ##################\n\n";
    private const ERRORS          = "###############     Errors     ##################\n\n";

    /**
     * Stores the loginState object.
     * @since 1.0.0
     */
    private $state;


    /**
     * Constructor pre fetches loginState or fails.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        // get the loginState for this session from database
        try{
            $this->state = new LoginState();
        } catch(Throwable $e) {
            $this->printError(__('Could not fetch loginState from database.', PLUGIN_NAME), 'Acs');
        }
    }


    /**
     * This method asserts the provided samlResponse
     * and perform a callback to the loginFlow to authorize
     * the user if the samlResponse is valid.
     *
     * @since 1.0.0
     */
    public function assertSaml($samlResponse) : void                // NOSONAR method complexity by design.
    {
        // Validate we are expecting a samlResponse in the first place!
        if($this->state->getPhase() != LoginState::PHASE_SAML_ACS){
            // Generate error and log state and response into the errorlog.
            $this->printError(__('GLPI did not expect an assertion from this Idp. Please login using the GLPI login interface', PLUGIN_NAME),
                              __('samlResponse assertion'),
                                 self::EXTENDED_HEADER.
                                 "Unexpected assertion triggered by external source with address:{$_SERVER['REMOTE_ADDR']}\n".
                                 self::STATE_OBJ.var_export($this->state, true)."\n\n".
                                 self::RESPONSE_OBJ.var_export($samlResponse, true)."\n\n".
                                 self::EXTENDED_FOOTER."\n");
        }else{
            // Update the state in loginState (this should also prevent replays of the received samlResponse)
            $this->state->setPhase(LoginState::PHASE_SAML_AUTH);
        }

        // Validate the samlResponse actually holds the expected result
        if( is_array($samlResponse) && array_key_exists('SAMLResponse', $samlResponse) ){
            // Write the samlResponse to the LoginState database for future SIEM eval;
            $this->state->setServerParams(serialize($samlResponse));

            // Fetch the configEntity for this assertion or print error on failure.
            if(!$configEntity = new ConfigEntity($this->state->getIdpId())){
                $this->printError(__("Unable to fetch idp configuration with id:".$this->state->getIdpId()." from database",PLUGIN_NAME),
                                  __('Assert saml', PLUGIN_NAME));
            }

            // Does phpSaml needs to take proxy headers into account
            // for assertion (url validation)
            if($configEntity->getField(ConfigEntity::PROXIED)){
                $samltoolkit = new Utils();
                $samltoolkit::setProxyVars(true);
            }

            // Get settings for the Response.
            try { $samlSettings = new Settings($configEntity->getPhpSamlConfig()); } catch(Throwable $e){
                $this->printError($e->getMessage(),
                                  __('phpSaml::Settings->init'),
                                     self::EXTENDED_HEADER.
                                     self::ERRORS.var_export($samlSettings->getErrors(), true)."\n\n".
                                     self::STATE_OBJ.var_export($this->state, true)."\n\n".
                                     self::EXTENDED_FOOTER);
            }

            // process the samlResponse.
            try { $response = new Response($samlSettings, $samlResponse['SAMLResponse']); } catch(Throwable $e) {
                $this->printError($e->getMessage(),
                                  __('Saml::Response->init'),
                                     self::EXTENDED_HEADER.
                                     self::ERRORS.var_export($response->getErrorException(), true)."\n\n".
                                     self::STATE_OBJ.var_export($this->state, true)."\n\n".
                                     self::RESPONSE_OBJ.var_export($response, true)."\n\n".
                                     self::EXTENDED_FOOTER);
            }

            // Validate the response. Is it valid then peform samlLogin!
            if (is_object($response) && $response->isValid() )
            {
                    $this->doSamlLogin($response);
            } else {
                $this->printError(__('Received samlResponse was not valid. Please review the errors in the logging and correct the problem', PLUGIN_NAME),
                                     'Saml::Response->validate',
                                     self::EXTENDED_HEADER.
                                     self::ERRORS.var_export($response->getErrorException(), true)."\n\n".
                                     self::STATE_OBJ.var_export($this->state, true)."\n\n".
                                     self::RESPONSE_OBJ.var_export($response, true)."\n\n".
                                     self::EXTENDED_FOOTER);
            }
        }else{
            $this->printError(__('Acs was called without sending it a samlResponse to assert while we where expecting one. Make sure the samlResponse is
                                  forwarded correctly. <u>Refreshing the request will not allow you to "replay" the samlResponse.</u> Please login again using
                                  the correct button on the login screen.', PLUGIN_NAME),
                                  __('Saml::Acs->init'),
                                  self::EXTENDED_HEADER.
                                  "Acs was called without sending it a samlResponse to assert. We where expecting an assertion.\n".
                                  self::STATE_OBJ.var_export($this->state, true)."\n\n".
                                  self::RESPONSE_OBJ.var_export($samlResponse, true)."\n\n".
                                  self::EXTENDED_FOOTER);
        }
    }
}

