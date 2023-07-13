<?php
/**
 *  ------------------------------------------------------------------------
 *  Copyright (C) 2023 by Chris Gralike, Derrick Smith
 *  ------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of phpSaml2.
 *
 * Ticket Filter plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Ticket Filter is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with ticket filter. If not, see <http://www.gnu.org/licenses/>.
 *
 * ------------------------------------------------------------------------
 *
 *  @package  	   phpSaml2
 *  @version	   1.0.0
 *  @author    	Chris Gralike
 *  @author       Derrick Smith
 *  @copyright 	Copyright (c) 2023 by Derrick Smith
 *  @license   	GPLv2+
 *  @see       	https://github.com/DonutsNL/phpSaml2/readme.md
 *  @link		   https://github.com/DonutsNL/phpSaml2
 *  @since     	0.1
 * ------------------------------------------------------------------------
 **/

use Glpi\Plugin\Hooks;
use GlpiPlugin\Phpsaml2\Phpsaml2;

/**
 * Maximum GLPI version, exclusive
 * Minimal GLPI version, inclusive
 */
define('PLUGIN_PHPSAML2_VERSION', '1.0.0');
define('PLUGIN_PHPSAML2_MIN_GLPI', '10.0.0');
define('PLUGIN_PHPSAML2_MAX_GLPI', '10.0.99');

/**
 * Init hooks of the plugin.
 *
 * @return void
 */
function plugin_init_ticketfilter() : void
{
   global $PLUGIN_HOOKS;

   Plugin::registerClass(Phpsaml2::class);

   // Config page: redirect to filterpatterns dropdown page
   $PLUGIN_HOOKS['config_page']['phpsaml2'] = 'front/ConfigDropdown.php';

   // State this plugin cross-site request forgery compliant
   $PLUGIN_HOOKS['csrf_compliant']['phpsaml2'] = true;
}


/**
 * Returns the name and the version of the plugin
 *
 * @return array
 */
function plugin_version_ticketfilter() : array
{
   return [
      'name'           => 'Phpsaml 2',
      'version'        => PLUGIN_TICKETFILTER_VERSION,
      'author'         => 'Derrick Smith, Chris Gralike',
      'license'        => 'GPLv2+',
      'homepage'       => 'https://github.com/DonutsNL/phpsaml2',
      'requirements'   => [
         'glpi' => [
            'min' => PLUGIN_TICKETFILTER_MIN_GLPI,
            'max' => PLUGIN_TICKETFILTER_MAX_GLPI,
         ]
      ]
   ];
}


/**
 * Check pre-requisites before install
 * @return boolean
 */
function plugin_phpsaml2_check_prerequisites() : bool
{
   if (false) {
      return false;
   }
   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 * @return boolean
 */
function plugin_phpsaml2_check_config($verbose = false) : bool
{
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo __('Installed / not configured', 'Phpsaml2');
   }
   return false;
}
