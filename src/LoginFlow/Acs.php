<?php
/**
 *  ------------------------------------------------------------------------
 *  GLPISaml
 *
 *  GLPISaml was inspired by the initial work of Derrick Smith's
 *  PhpSaml. This project's intend is to address some structural issues
 *  caused by the gradual development of GLPI and the broad amount of
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
 *  @version    1.1.6
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
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Response;
use GlpiPlugin\Glpisaml\LoginFlow;
use GlpiPlugin\Glpisaml\Loginstate;
use GlpiPlugin\Glpisaml\Config\ConfigEntity;


/**
 * Responsible to handle the incoming samlResponse. This object should
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
    public const EXTENDED_HEADER = "================ BEGIN EXTENDED =================\n\n";
    public const EXTENDED_FOOTER = "================= END EXTENDED ==================\n\n";
    public const SERVER_OBJ      = "###############  ServerGlobal  ##################\n\n";
    public const STATE_OBJ       = "###############    StateObj    ##################\n\n";
    public const RESPONSE_OBJ    = "###############    Response    ##################\n\n";
    public const ERRORS          = "###############     Errors     ##################\n\n";

    /**
     * Stores the loginState object.
     * @since 1.0.0
     */
    private $state          = false;

    /**
     * Stores the debug param.
     * @since 1.0.0
     */
    private $debug          = false;

    /**
     * Stores the idpId.
     * @since 1.2.0
     */
    private $idpId          = false;

    /**
     * Stores the samlResponse.
     * @since 1.2.0
     */
    private $samlResponse   = false;

    /**
     * Stores the idp configuration.
     * @since 1.2.0
     */
    private $configEntity   = false;


    /**
     * Constructor pre fetches loginState or fails.
     *
     * @since 1.0.0
     */
    public function __construct(array $idpId, array $samlResponse)
    {
        // If we have all required data we first need to unpack the samlResponse using
        // the samlRequest provided idpId. If all went well, the idpId was added as an
        // get value to the URL by the IdP using the value provided in the samlRequest
        // @see: ConfigEntity::getPhpSamlConfig()
        if(!empty($samlResponse)                                &&          //samlResponse post should not be empty
            array_key_exists('SAMLResponse', $samlResponse)     &&          //samlResponse arrayKey should exist
            !empty($idpId)                                      &&          //idpId get should not be empty
            array_key_exists(LoginState::IDP_ID, $idpId)        &&          //idpId arrayKey should exist
            is_numeric($idpId[LoginState::IDP_ID])              ){          //idpId should be a nummeric value (1>)
                
                // overwrite the value with the value from array
                // We already checked if it was available
                // getters are strings, so we cast it to int.
                $this->idpId = (int) $idpId[LoginState::IDP_ID];

                // We got everything we need!
                // get the configuration using the idpId provided
                try{
                    $this->configEntity = new ConfigEntity($this->idpId);
                }catch (Throwable $e){
                    $this->printError(__("Unable to fetch idp configuration with id:".$this->state->getIdpId()." from database",PLUGIN_NAME),
                                      __('Assert saml', PLUGIN_NAME));
                }

                // DEBUG ENABLED?
                // Only add extended logging with debug not to dump sensitive samlResponse data.
                // https://github.com/DonutsNL/glpisaml/issues/12
                $this->debug = ($this->configEntity->getField(ConfigEntity::DEBUG)) ? true : false;

                // PROXIED RESPONSES?
                // Does phpSaml needs to take proxy headers into account
                // for assertion url validation
                if($this->configEntity->getField(ConfigEntity::PROXIED)){
                    $samltoolkit = new Utils();
                    $samltoolkit::setProxyVars(true);
                }

                // GET POPULATED PHPSAML SETTINGS OBJECT
                // Or show error!
                try { $samlSettings = new Settings($this->configEntity->getPhpSamlConfig()); } catch(Throwable $e) {
                    $extended = ($this->debug) ? Acs::EXTENDED_HEADER.
                                Acs::ERRORS.var_export($samlSettings->getErrors(), true)."\n\n".
                                Acs::STATE_OBJ.var_export($this->state, true)."\n\n".
                                Acs::EXTENDED_FOOTER : '';

                    $this->printError($e->getMessage(),
                                    __('phpSaml::Settings->init'),
                                    $extended);
                }

                // PROCESS THE SAMLRESPONSE
                // process the samlResponse.
                try { $this->samlResponse = new Response($samlSettings, $samlResponse['SAMLResponse']); } catch(Throwable $e) {
                    $extended = '';
                    if($this->debug){
                        $extended = Acs::EXTENDED_HEADER.
                                    Acs::ERRORS.var_export($samlSettings->getErrors(), true)."\n\n".
                                    Acs::STATE_OBJ.var_export($this->state, true)."\n\n".
                                    Acs::EXTENDED_FOOTER;
                    }
                    $this->printError($e->getMessage(),
                                    __('Saml::Response->init'),
                                    $extended);
                }

                // Get the requestId from the samlResponse and generate LoginState using
                // the samlInResponseTo attribute. If the samlResponse was requested by
                // GLPI we should find an existing LoginState in the LoginState database
                // and the LoginState should be prepopulated with the 'database' marker set
                // to true.
                $InResponseTo = $this->samlResponse->getXMLDocument()->documentElement->getAttribute('InResponseTo');
                try{
                    $this->state = new LoginState($InResponseTo);
                } catch(Throwable $e) {
                    $this->printError(__("Could not fetch loginState from database with error: <br><br>$e<br><br>See: https://codeberg.org/QuinQuies/glpisaml/wiki/LoginState.php for more information.", PLUGIN_NAME), 
                                      __('LoginState'));
                }

                // Everything is prepared for assertion!
                // Perform assertion on the samlResponse
                $this->assertSaml();

        } else {
            $this->printError(__('We did not receive the required POST/GET headers, see: https://codeberg.org/QuinQuies/glpisaml/wiki/ACS.php for more information', PLUGIN_NAME),
                            __('Acs assertion'),
                            Acs::EXTENDED_HEADER.
                            Acs::SERVER_OBJ.var_export($_SERVER, true)."\n\n".
                            Acs::EXTENDED_FOOTER."\n");
        }
    }

    /**
     * This method asserts the provided samlResponse
     * and perform a callback to the loginFlow to authorize
     * the user if the samlResponse is valid.
     *
     * @since 1.0.0
     */
    public function assertSaml() : void                // NOSONAR method complexity by design.
    {
        // If we fetched the state, the fetched state should not (yet) have a
        // samlResponseId registered. If so, it should be considered a replayed
        // response and we need to register this and invalidate the login and
        // generate a meaningfull error. In addition the found response ID should
        // not exist in the database (anywhere)
        if(!$this->state->getSamlResponseId() == 0                        &&
           !$this->state->checkResponseIdUnique($this->samlResponse->getId())   ){
            $this->printError(__("It looks like this samlResponse has already been used to authenticate a different user.
                                 Maybe an error occurred and you pressed F5 and accidently resend the samlResponse that is
                                 already registered as processed. For security reasons we can not allow processed samlResponses
                                 to be processed again. Please login again to generate a new samlResponse. Sorry for any inconvenience.
                                 If the problem presists, then please contact your administrator.
                                 See: https://codeberg.org/QuinQuies/glpisaml/wiki/LoginState.php for more information", PLUGIN_NAME),
                                 'LoginState',
                                 Acs::EXTENDED_HEADER.
                                 "samlResponse with registered ID was replayed in acs.php. Possibly the user pressed F5 when encountering
                                  a different error or the response was send successively to the acs\n\n".
                                 Acs::SERVER_OBJ.var_export($_SERVER, true)."\n\n".
                                 Acs::STATE_OBJ.var_export($this->state, true)."\n\n".
                                 Acs::STATE_OBJ.var_export($this->samlResponse->getXMLDocument(), true)."\n\n".
                                 Acs::EXTENDED_FOOTER."\n");
        }else{
            // The response is unique, register it in the database
            // to prevent future replays of this document.
            try{
                $this->state->setSamlResponseId($this->samlResponse->getId());
            } catch(Throwable $e ) {
                $this->printError(__("An error occured while trying to update the samlResponseId into the LoginState database. Review the saml log for more details", PLUGIN_NAME), 
                                     'LoginState', "The following error was reported: $e");
            }
        }

        // Only if the registered session is in phase PHASE_SAML_ACS (2) do we allow further
        // processing. This check is to prevent parallel requests or intentionally created
        // race-conditions forcing the plugin into an inconsistant state possibly allowing
        // a session to forcefully being logged in.
        if($this->state->getPhase() != LoginState::PHASE_SAML_ACS){
            // Generate error and log state and response into the errorlog.
            $this->printError(__("GLPI did not expect an assertion from this Idp. The most likely reason is a race condition
                                  causing an inconsistant loginState in the database or software bug. Please login again via the
                                  GLPI-interface. Sorry for the inconvenience. See: https://codeberg.org/QuinQuies/glpisaml/wiki/LoginState.php 
                                  for more information", PLUGIN_NAME),
                              __('samlResponse assertion'),
                                  Acs::EXTENDED_HEADER.
                              __("Unexpected assertion triggered while session was in a different phase then expected (2). This error was triggered by external source
                                  with address:{$_SERVER['REMOTE_ADDR']}. Possible causes include race-conditions or parallel calls using the same samlResponse.\n").
                                  Acs::STATE_OBJ.var_export($this->state, true)."\n\n".
                                  Acs::EXTENDED_FOOTER."\n");
        }

        // Update the state to SAML AUTH, again to prevent raceconditions or parallel calls using the same
        // samlResponse to the acs.php. This first call should complete (if everything lines up).
        try{
            $this->state->setPhase(LoginState::PHASE_SAML_AUTH);
        } catch(Throwable $e) {
            $this->printError(__("An error occured while trying to update the login phase to LoginState::PHASE_SAML_AUTH  into the LoginState database.
                                  Review the saml log for more details", PLUGIN_NAME), 'LoginState', "The following error was reported: $e");
        }

        // Perform validation by phpSaml library
        try{
            $this->samlResponse->isValid();
        } catch(Throwable $e) {
            $this->printError(__("Validation of the samlResponse document failed. Review the saml log for more details", PLUGIN_NAME), 
                                 'LoginState', "The following error was reported: $e");
        }

        // Call the performSamlLogin from the LoginFlow object.
        $this->performSamlLogin($this->samlResponse);
    }
}

