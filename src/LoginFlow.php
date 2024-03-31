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
 * ------------------------------------------------------------------------
 *
 * The concern this class adresses is added because we want to add support
 * for multiple idp's. Deciding what idp to use might involve more complex
 * algorithms then we used (1:1) in the previous version of phpSaml. These
 * can then be implemented here.
 *
 **/

namespace GlpiPlugin\Glpisaml;

use Html;
use Plugin;
use Session;
use Toolbox;
use Throwable;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Settings;
use OneLogin\Saml2\Response;
use GlpiPlugin\Glpisaml\Config;
use GlpiPlugin\Glpisaml\Exclude;
use GlpiPlugin\Glpisaml\LoginState;
use GlpiPlugin\Glpisaml\Config\ConfigEntity;

class LoginFlow
{
    /**
     * Where to find the loginScreen template.
     * @since 1.0.0
     */
    public const HTML_TEMPLATE_FILE = PLUGIN_GLPISAML_TPLDIR.'/loginScreen.html';

    
    // https://docs.oasis-open.org/security/saml/v2.0/saml-bindings-2.0-os.pdf
    public const SCHEMA_NAME                 = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/name';
    public const SCHEMA_SURNAME              = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/surname';
    public const SCHEMA_FIRSTNAME            = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/firstname';
    public const SCHEMA_GIVENNAME            = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/givenname';
    public const SCHEMA_EMAILADDRESS         = 'http://schemas.xmlsoap.org/ws/2005/05/identity/claims/emailaddress';

    // LOGIN FLOW PRESSING A BUTTON.

    /**
     * Evaluates the session and determins if login/logout is required
     * Called by post_init hook via function in hooks.php. It watches POST
     * information passed from the loginForm.
     *
     * @param void
     * @return boolean
     * @since 1.0.0
     */
    public function doAuth()  : bool
    {
        global $CFG_GLPI;
        // Get current state
        if(!$state = new Loginstate()){ return false; }

        // Check if the logout button was pressed and handle request!
        if (strpos($_SERVER['REQUEST_URI'], 'front/logout.php') !== false) {
            // Stop GLPI from processing cookiebased autologin.
            $_SESSION['noAUTO'] = 1;
            $this->performGlpiLogOff();
            $this->performSamlLogOff();
        }

        // Check if a SAML button was pressed and handle the corresponding logon request!
        if (isset($_POST['phpsaml'])        &&      // Must be set
            is_numeric($_POST['phpsaml'])    &&      // Value must be numeric
            strlen($_POST['phpsaml']) < 3  ){      // Should not exceed 999

            // If we know the idp we register it in the login State
            $state->setIdpId(filter_var($_POST['phpsaml'], FILTER_SANITIZE_NUMBER_INT));
            
            // Update the current phase in database. The state is verified by the Acs
            // while handling the received SamlResponse. Any other state will force Acs
            // into an error state. This is to prevent unexpected (possibly replayed)
            // samlResponses from being processed. to prevent playback attacks.
            $state->setPhase(LoginState::PHASE_SAML_ACS);

            // Actually perform SSO
            $this->performSamlSSO($state);
        }

        // else
        return false;
    }

    protected function performSamlSSO(Loginstate $state): void
    {
        global $CFG_GLPI;
        
        // Fetch the correct configEntity
        if($configEntity = new ConfigEntity($state->getIdpId())){
            $samlConfig = $configEntity->getPhpSamlConfig();
        }

        // Initialize the OneLogin phpSaml auth object
        // using the requested phpSaml configuration from
        // the glpisaml config database. Catch all throwable errors
        // and exceptions.
        try { $auth = new Auth($samlConfig); } catch (Throwable $e) {
            $this->printError($e->getMessage(), 'Saml::Auth->init', var_export($auth->getErrors(), true));
        }
        
        // Perform a login request with the loaded glpiSaml
        // configuration. Catch all throwable errors and exceptions
        try { $auth->login($CFG_GLPI["url_base"]); } catch (Throwable $e) {
            $this->printError($e->getMessage(), 'Saml::Auth->login', var_export($auth->getErrors(), true));
        }
    }

    /**
     * Called by the Acs class if the received response was valid
     * to handle the samlLogin or invalidate the login if
     * there are deeper issues with the response, for instance
     * important claims are missing.
     *
     * @see https://github.com/DonutsNL/glpisaml/issues/7
     * @param void
     * @return string   html form for the login screen
     * @since 1.0.0
     */
    protected function doSamlLogin(Response $response): void{
        $response;
        $this->printError('We succesfully loggedIn!');
    }

     /**
     * Responsible to generate a login screen with Idp buttons
     * using available idp configurations.
     *
     * @see https://github.com/DonutsNL/glpisaml/issues/7
     * @param void
     * @return string   html form for the login screen
     * @since 1.0.0
     */
    public function showLoginScreen(): void
    {
        // Fetch the global DB object;
        $tplvars = Config::getLoginButtons(12);

        // Define static translatable elements
        $tplvars['action']     = Plugin::getWebDir(PLUGIN_NAME, true);
        $tplvars['header']     = __('Login with external provider', PLUGIN_NAME);
        $tplvars['noconfig']   = __('No valid or enabled saml configuration found', PLUGIN_NAME);

        // Render twig template
        $loader = new \Twig\Loader\FilesystemLoader(PLUGIN_GLPISAML_TPLDIR);
        $twig = new \Twig\Environment($loader);
        $template = $twig->load('loginScreen.html.twig');
        echo $template->render($tplvars);
    }


    // LOGOUT FLOW EITHER REQUESTED BY GLPI OR REQUESTED BY THE IDP (SLO) OR FORCED BY ADMIN

    /**
     * Makes sure user is logged out of GLPI
     * @return void
     */
    protected function performGlpiLogOff(): void
    {
        $validId   = @$_SESSION['valid_id'];
        $cookieKey = array_search($validId, $_COOKIE);
        
        Session::destroy();
        
        //Remove cookie to allow new login
        $cookiePath = ini_get('session.cookie_path');
        
        if (isset($_COOKIE[$cookieKey])) {
           setcookie($cookieKey, '', time() - 3600, $cookiePath);
           unset($_COOKIE[$cookieKey]);
        }
    }
    
     /**
     * Makes sure user is logged out of responsible IDP provider
     * @return void
     */
    protected function performSamlLogOff(): void
    {
        global $CFG_GLPI;
    }


    // ERROR HANDLING
   
    /**
     * Prints a nice error message with 'back' button and
     * logs the error passed in the GlpiSaml logfile.
     *
     * @see https://github.com/DonutsNL/glpisaml/issues/7
     * @param string errorMsg   string with raw error message to be printed
     * @param string action     optionally add 'action' that was performed to error message
     * @param string extended   optionally add 'extended' information about the error in the logfile.
     * @return void             no return, PHP execution is terminated by this method.
     * @since 1.0.0
     */
    public function printError(string $errorMsg, string $action = '', string $extended = '') : void
    {
        // Pull GLPI config into scope.
        global $CFG_GLPI;

        // Log in file
        Toolbox::logInFile(PLUGIN_NAME."-errors", $errorMsg . "\n", true);
        if($extended){
            Toolbox::logInFile(PLUGIN_NAME."-errors", $extended . "\n", true);
        }

        // Define static translatable elements
        $tplvars['header']      = __('⚠️ An error occured', PLUGIN_NAME);
        $tplvars['leading']     = __("We are sorry, something went terribly wrong while we where processing your $action request!", PLUGIN_NAME);
        $tplvars['error']       = $errorMsg;
        $tplvars['returnPath']  = $CFG_GLPI["root_doc"] .'/index.php';
        $tplvars['returnLabel'] = __('Return to GLPI', PLUGIN_NAME);

        // print header
        Html::nullHeader("Login",  $CFG_GLPI["root_doc"] . '/index.php');

        // Render twig template
        $loader = new \Twig\Loader\FilesystemLoader(PLUGIN_GLPISAML_TPLDIR);
        $twig = new \Twig\Environment($loader);
        $template = $twig->load('errorScreen.html.twig');
        echo $template->render($tplvars);

        // print footer
        Html::nullFooter();
        
        // stop execution.
        exit;
    }

}
