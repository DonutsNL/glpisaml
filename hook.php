<?php

/**
 *  ------------------------------------------------------------------------
 *  PhpSaml2
 *
 *  PhpSaml2 was inspired by the initial work of Derrick Smith's
 *  PhpSaml. This project's intend is to address some structural issues
 *  caused by the gradual development of GLPI and the broad ammount of
 *  wishes expressed by the community. 
 *
 *  Copyright (C) 2024 by Chris Gralike
 *  ------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Ticket Filter project.
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
 *  @package  	   TicketFilter
 *  @version	   1.2.0
 *  @author    	Chris Gralike
 *  @copyright 	Copyright (c) 2023 by Chris Gralike
 *  @license   	GPLv2+
 *  @see       	https://github.com/DonutsNL/ticketfilter/readme.md
 *  @link		   https://github.com/DonutsNL/ticketfilter
 *  @since     	0.1.0
 * ------------------------------------------------------------------------
 **/

use GlpiPlugin\PhpSaml2\Config;

/**
 * Tell GLPI to add our dropdown to the dropdowns menu.
 * @return array [Classname => __('Menu label') ]
 */
// phpcs:ignore PSR1.Function.CamelCapsMethodName
function plugin_phpsaml2_getDropdown() : array
{
   return [Exclude::class => __("Excludes", 'phpsaml2')];
}


/**
 * Summary of plugin_ticketFilter install
 * @return booleansyste
 * test
 */
//phpcs:ignore PSR1.Function.CamelCapsMethodName
function plugin_phpsaml2_install() : bool
{

   if (method_exists(FilterPattern::class, 'install')) {
      $version   = plugin_version_ticketfilter();
      $migration = new Migration($version['version']);
      FilterPattern::install($migration);
   }
   return true;
   
}


/**
 * Summary of plugin_ticketFilter uninstall
 * @return boolean
 */
//phpcs:ignore PSR1.Function.CamelCapsMethodName
function plugin_phpsaml2_uninstall() : bool
{
   
   if (method_exists(FilterPattern::class, 'uninstall')) {
      $version   = plugin_version_ticketfilter();
      $migration = new Migration($version['version']);
      FilterPattern::uninstall($migration);
   }
   return true;

}