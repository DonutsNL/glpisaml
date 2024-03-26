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
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Settings;
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

    /**
     * Evaluates the session and determins if login/logout is required
     * Called by post_init hook via function in hooks.php
     * @param void
     * @return boolean
     * @since 1.0.0
     */
    public function doAuth()  : bool
    {
        global $CFG_GLPI;
        // Get current state
        if(!$state = new Loginstate()){ return false; }
        $state;
        
        // Check if a SAML button was pressed and handle request!
        if (isset($_POST['phpsaml'])) {
            html::redirect($CFG_GLPI["root_doc"].'/#YESGOTLOGIN');
        }

        // Check if the logout button was pressed and handle request!
        if (strpos($_SERVER['REQUEST_URI'], 'front/logout.php') !== false) {
            // Stops GLPI from processing cookiebased autologin.
            $_SESSION['noAUTO'] = 1;
            $this->performGlpiLogOff();
            $this->performSamlLogOff();
        }

        // Else validate the state possibly redirect back to login
        // resetting the state if there is an issue.
        return false;
    }

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
        /*
        $returnTo           = null;
        $parameters         = [];
        $nameId             = (isset(self::$nameid))        ? self::$nameid         : null;
        $sessionIndex       = (isset(self::$sessionindex))  ? self::$sessionindex   : null;
        $nameIdFormat       = (isset(self::$nameidformat))  ? self::$nameidformat   : null;

        if (!empty(self::$phpsamlsettings['idp']['singleLogoutService'])){
            try {
                self::auth();
                self::$auth->logout($returnTo, $parameters, $nameId, $sessionIndex, false, $nameIdFormat);
            } catch (Exception $e) {
                $error = $e->getMessage();
                Toolbox::logInFile("php-errors", $error . "\n", true);
                
                Html::nullHeader("Login", $CFG_GLPI["url_base"] . '/index.php');
                echo '<div class="center b">'.$error.'<br><br>';
                // Logout whit noAUto to manage auto_login with errors
                echo '<a href="' . $CFG_GLPI["url_base"] .'/index.php">' .__('Log in again') . '</a></div>';
                Html::nullFooter();
            }
        }
        */
    }

    /**
     * Responsible to generate a login screen using available idp
     * configurations.
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
        $tplvars['noconfig']   = __('No valid saml configuration found', PLUGIN_NAME);

        // Render twig template
        $loader = new \Twig\Loader\FilesystemLoader(PLUGIN_GLPISAML_TPLDIR);
        $twig = new \Twig\Environment($loader);
        $template = $twig->load('loginScreen.html.twig');
        echo $template->render($tplvars);
    }

}
