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

/**
 * Make plugin dropdown visible in the dropdowns menu.
 * @return boolean
 * test
 */
// phpcs:ignore PSR1.Function.CamelCapsMethodName
/*
function plugin_ticketfilter_getDropdown() : array 
{
   return [Phpsaml2ConfigDropdown::class => __("IdentityProviders", 'phpSaml2')];
}
*/

/**
 * Call install methods
 * @return boolean
 */
// phpcs:ignore PSR1.Function.CamelCapsMethodName
function plugin_ticketfilter_install() : bool
{

   if (method_exists(Phpsaml::class, 'install')) {
      $version   = plugin_version_phpsaml2();
      $migration = new Migration($version['version']);
      Phpsaml::install($migration);
   }
   return true;
   
}

/**
 * 
 * Call all uninstall methods
 * @return boolean
 */
// phpcs:ignore PSR1.Function.CamelCapsMethodName
function plugin_ticketfilter_uninstall() : bool
{
   
   if (method_exists(Phpsaml::class, 'uninstall')) {
      $version   = plugin_version_ticketfilter();
      $migration = new Migration($version['version']);
      Phpsaml::uninstall($migration);
   }
   return true;

}