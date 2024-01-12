<?php
/**
 *  ------------------------------------------------------------------------
 *  PhpSaml2
 *  PhpSaml2 is heavily influenced by the initial work of Derrick Smith's
 *  PhpSaml. This project's intend is to address some structural issues
 *  caused by the gradual development of GLPI. It intends to use more of the
 *  GLPI core objects and php8/composer namespaces.
 *
 *  Copyright (C) 2024 by Chris Gralike
 *  ------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of PhpSaml2 project.
 * PhpSaml2 plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PhpSaml2 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with PhpSaml2. If not, see <http://www.gnu.org/licenses/>.
 *
 * ------------------------------------------------------------------------
 *
 *  @package    PhpSaml2
 *  @version    1.0.0
 *  @author     Chris Gralike
 *  @copyright  Copyright (c) 2023 by Chris Gralike
 *  @license    GPLv2+
 *  @see        https://github.com/DonutsNL/phpSaml2/readme.md
 *  @link       https://github.com/DonutsNL/phpSaml2
 *  @since      1.0.0
 * ------------------------------------------------------------------------
 **/

use Plugin;
use Session;
use Glpi\Plugin\Hooks;
use GlpiPlugin\PhpSaml2\User;
use GlpiPlugin\PhpSaml2\Config;
use GlpiPlugin\PhpSaml2\Excludes;
use GlpiPlugin\PhpSaml2\Loginflow;
use GlpiPlugin\PhpSaml2\Ruleright;
use GlpiPlugin\PhpSaml2\Rulerightcollection;
use GlpiPlugin\PhpSaml2\Autoloader;

// Constants
define('PLUGIN_PHPSAML2_VERSION', '1.0.0');
define('PLUGIN_PHPSAML2_MIN_GLPI', '10.0.0');
define('PLUGIN_PHPSAML2_MAX_GLPI', '10.9.99');
define('PLUGIN_NAME', 'phpsaml2');
define('PLUGIN_DIR', Plugin::getWebDir(PLUGIN_NAME, false));

/**
 * Init hooks of the plugin.
 * CALLED AND REQUIRED BY GLPI
 * 
 * @return void
 */
function plugin_init_phpsaml2() : void                                                  //NOSONAR - Not compliant with LINT naming convention
{
    global $PLUGIN_HOOKS;                                                               //NOSONAR - Not compliant with LINT naming convention

    // CSRF
    $PLUGIN_HOOKS[Hooks::CSRF_COMPLIANT][PLUGIN_NAME] = true;                           //NOSONAR - GLPI Default variable name  

    // CONFIG PAGES
    Plugin::registerClass(Config::class);
    Plugin::registerClass(Excludes::class);
    if (Session::haveRight('config', UPDATE)) {
        $PLUGIN_HOOKS['config_page'][PLUGIN_NAME] = 'front/config.php';                 //NOSONAR
    }

    // USER AND JIT HANDLING
    plugin::registerClass(User::class);
    Plugin::registerClass(Ruleright::class);
    Plugin::registerClass(Rulerightcollection::class);
    $PLUGIN_HOOKS[Hooks::RULE_MATCHED][PLUGIN_NAME]    = [User::class => 'updateUser'];

    // POSTINIT HOOK LOGINFLOW TRIGGER
    Plugin::registerClass(Loginflow::class);
    $PLUGIN_HOOKS[Hooks::POST_INIT][PLUGIN_NAME] = [Loginflow::class => 'evalAuth'];    //NOSONAR

}


/**
 * Returns the name and the version of the plugin
 * @return array
 */
function plugin_version_phpsaml2() : array                              //NOSONAR - GLPI Default function names
{
    return [
        'name'           => PLUGIN_NAME,
        'version'        => PLUGIN_TICKETFILTER_VERSION,
        'author'         => 'Chris Gralike',
        'license'        => 'GPLv2+',
        'homepage'       => 'https://github.com/DonutsNL/phpsaml2',
        'requirements'   => [
            'glpi' => [
            'min' => PLUGIN_TICKETFILTER_MIN_GLPI,
            'max' => PLUGIN_TICKETFILTER_MAX_GLPI,
            ],
            'php'    => [
            'min' => '8.0'
            ]
        ]
    ];
}


/**
 * Check pre-requisites before install
 * @return boolean
 */
function plugin_phpsaml2_check_prerequisites() : bool                   //NOSONAR - GLPI Default function names
{
   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 * @return boolean
 */
function plugin_phpsaml2_check_config($verbose = false) : bool         //NOSONAR - GLPI Default function names
{
   if ($verbose) {
      echo __('Installed / not configured', 'TICKETFILTER');
   }
   return (true) ? true : false;
}