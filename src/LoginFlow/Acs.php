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

use Toolbox;
use OneLogin\Saml2\Response;
use OneLogin\Saml2\Settings;
use GlpiPlugin\Glpisaml\LoginFlow;
use GlpiPlugin\Glpisaml\Loginstate;
use GlpiPlugin\Glpisaml\ConfigEntity;


class Acs extends LoginFlow
{
    private $state           = null;
    private $samlResponse    = null;

    public function __construct()
    {
        // Load the state to find our idpid
        $this->state = new LoginState();
    }

    public function assertSaml($samlResponse) : void    //NOSONAR - Complexity by design.
    {
        if(is_array($samlResponse)                          &&
           array_key_exists('SAMLResponse', $samlResponse)  ){
            // Insert the response into the database
            $this->state->setServerParams(serialize($samlResponse));
            
            // First check if we are expecting an assertion in the first place;
            if($this->state->getPhase() != LoginState::PHASE_SAML_ACS){
                Toolbox::logInFile("saml-errors", "Unexpected assertion triggered by external source {$_SERVER['REMOTE_ADDRESS']}." . "\n", true);
                $this->printError(__('GLPI did not expect an assertion from this Idp. Please login using the GLPI login interface', PLUGIN_NAME));
            }else{
                // Assertion was expected from this idp.
                $this->state->setPhase(LoginState::PHASE_SAML_AUTH);
            }

            // Fetch the configEntity using the registered IdpId.
            if($configEntity = new ConfigEntity($this->state->getIdpId())){
                // Populate saml settings;
                try {
                    $samlSettings = new OneLogin\Saml2\Settings($configEntity->getPhpSamlConfig());
                } catch(Exception | Error $e){
                    $this->printError($e->getMessage());
                }
                // Evaluate samlResponse;
                try {
                    $this->samlResponse = new OneLogin\Saml2\Response($samlSettings, $samlResponse['SAMLResponse']);
                } catch(Exception $e) {
                    $this->printError($e->getMessage());
                }

                // Validate SamlResponse
                if (is_object($this->samlResponse) && 
                    $this->samlResponse->isValid() ){
                    // Valid response, check required properties
                    
                    if($this->validateFields()){
                        echo "valid response!";
                        exit;
                    }
                } else {
                    // Exit with error
                    $this->printError('samlResponse is not valid!');
                }

            }else{
                $this->printError(__('Unable to load registered idp configuration, was it deleted?'));
            }  
        } else {
            $this->printError('No valid phpSaml configuration received.');
        }
    }

    private function validateFields(): bool
    {
        if(!$response['nameId'] = $this->samlResponse->getNameId()) {
            $this->printError('Required nameId field is missing in response!');
        } else {
            // If the string #EXT# if found, a guest account is used thats not
            // transformed properly. Write an error and exit!
            // https://github.com/derricksmith/phpsaml/issues/135
            if(strstr($response['nameId'], '#EXT#@')){
                $this->printError('Detected an inproperly transformed guest claims, make sure nameid,
                                   name are populated using user.mail instead of the uset.principalname.<br>
                                   You can use the debug saml dumps to validate and compare the claims passed.<br>
                                   They should contain the original email addresses.<br>
                                   Also see: https://learn.microsoft.com/en-us/azure/active-directory/develop/saml-claims-customization');
            }
        }

        if(!$response['userData'] = $this->samlResponse->getAttributes()) {
            $this->printError('Required attribute userData missing');
        }

        if(!$response['nameIdFormat'] = $this->phpsaml::$auth->getNameIdFormat()) {
            $this->printError('No or invalid nameIdFormat');
        }
        
        if($response['sessionIndex'] = $this->phpsaml::$auth->getSessionIndex()) {
            $this->printError('No or invalid sessionIndex');
        }
    }

    public function printError(string $msg) : void
    {
        global $CFG_GLPI;
        Toolbox::logInFile("php-errors", $msg . "\n", true);
        Html::nullHeader("Login",  $CFG_GLPI["root_doc"] . '/index.php');
        echo '<div class="center b">'.$msg.'<br><br>';
        // Logout with noAUto to manage auto_login with errors
        echo '<a href="' .  $CFG_GLPI["root_doc"] .'/index.php">' .__('Log in again') . '</a></div>';
        Html::nullFooter();
        exit;
    }
}
