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
 * POV: The correct object name should be 'Authflow.' But people tend to
 * generalize and not care for the individual steps of IAAA,
 * so for maintainability purposes I chose to call it 'login' instead
 * of 'Auth' where Auth might also cause duplication issues where Auth is
 * also being handled by OneLogin\PhpSaml\Auth.
 * 
 * The concern this class adresses is added because we want to add support
 * for multiple idp's. Deciding what idp to use might involve more complex
 * algorithms then we used (1:1) in the previous version of phpSaml. These
 * can then be implemented here.
 * 
 **/

 namespace GlpiPlugin\Glpisaml;

use Session;
use Migration;
use CommonDBTM;
use GlpiPlugin\Glpisaml\Config;
use GlpiPlugin\Glpisaml\Config\ConfigEntity;
use GlpiPlugin\Glpisaml\Exclude;
use GlpiPlugin\Glpisaml\LoginState;
use OneLogin\Saml2\Auth;
use OneLogin\Saml2\Settings;

class Loginflow extends CommonDBTM
{

    public const HTML_TEMPLATE_FILE = PLUGIN_GLPISAML_TPLDIR.'/loginScreen.html';
    /**
     * Evaluates the session and determins if login/logout is required
     * Called by post_init hook via function in hooks.php
     * @param void
     * @return boolean
     * @since 1.0.0
     */
    public function evalAuth()  : bool
    {
        if(isset($_GET['SAML'])){
            print "<h1> WE GOT THE LOGIN REQUEST LETS PROCESS</h1>";
            var_dump($_GET);
        }
        // Evaluate current login state
        $state = new Loginstate();
        if ($state->isAuthenticated()) {
            return true;
        }

        // Dont peform auth for CLI calls.
        if (PHP_SAPI === 'cli'         ||
            Exclude::ProcessExcludes() ){
            return true;
        }

        return true;
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
        // Define static translatable elements
        $tplvars['header']     = __('Connect with an external provider', PLUGIN_NAME);
        $tplvars['noconfig']   = __('No saml configuration found',PLUGIN_NAME);

        // Fetch the global DB object;
        global $DB;
        foreach($DB->request(['FROM' => (new Config())::getTable()]) as $value)
        {
            $tplvars['buttons'][] = ['id'      => $value[ConfigEntity::ID],
                                     'icon'    => $value[ConfigEntity::CONF_ICON],
                                     'name'    => $value[ConfigEntity::NAME]];
        }
        // Render twig template
        $loader = new \Twig\Loader\FilesystemLoader(PLUGIN_GLPISAML_TPLDIR);
        $twig = new \Twig\Environment($loader);
        $template = $twig->load('loginScreen.html.twig');
        echo $template->render($tplvars);
    }

}
