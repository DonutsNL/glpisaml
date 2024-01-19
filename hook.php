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
 * This file is part of PhpSaml2 project.
 *
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
 * along with PhpSaml2. If not, see <http://www.gnu.org/licenses/> or
 * https://choosealicense.com/licenses/gpl-3.0/
 *
 * ------------------------------------------------------------------------
 *
 *  @package    PhpSaml2
 *  @version    1.0.0
 *  @author     Chris Gralike
 *  @copyright  Copyright (c) 2024 by Chris Gralike
 *  @license    GPLv3+
 *  @see        https://github.com/DonutsNL/phpSaml2/readme.md
 *  @link       https://github.com/DonutsNL/phpSaml2
 *  @since      1.0.0
 * ------------------------------------------------------------------------
 **/

use GlpiPlugin\Phpsaml2\Config;
use GlpiPlugin\Phpsaml2\Loginflow;

/**
 * Tell GLPI to add our dropdown to the dropdowns menu.
 * @return array [Classname => __('Menu label') ]
 */
function plugin_phpsaml2_getDropdown() : array                              //NOSONAR - phpcs:ignore PSR1.Function.CamelCapsMethodName
{
   return [Exclude::class => __("Excludes", 'phpsaml2')];
}



function plugin_phpsaml2_evalAuth() : void                                  //NOSONAR - phpcs:ignore PSR1.Function.CamelCapsMethodName
{
    // Call the evalAuth hook;
    $flow = new Loginflow();
    $flow->evalAuth();
}


/**
 * Performs install of plugin classes in /src.
 *
 * @return boolean
 */
//phpcs:ignore PSR1.Function.CamelCapsMethodName
function plugin_phpsaml2_install() : bool                                   //NOSONAR - phpcs:ignore PSR1.Function.CamelCapsMethodName
{
    if($files = plugin_phpsaml2_getSrcClasses())
    {
        if(is_array($files)) {
            foreach($files as $name){
                $class = "GlpiPlugin\\Phpsaml2\\" . basename($name, '.php');
                if(method_exists($class, 'install')){
                    $version   = plugin_version_phpsaml2();
                    $migration = new Migration($version['version']);
                    $class::install($migration);
                }
            }
        }
    }
    return true;
}

/**
 * Performs uninstall of pluginclasses in /src.
 *
 * @return boolean
 */
function plugin_phpsaml2_uninstall() : bool                                 //NOSONAR - phpcs:ignore PSR1.Function.CamelCapsMethodName
{
    if($files = plugin_phpsaml2_getSrcClasses()) {
        if(is_array($files)) {
            foreach($files as $name){
                $class = "GlpiPlugin\\Phpsaml2\\" . basename($name, '.php');
                if(method_exists($class, 'install')){
                    $version   = plugin_version_phpsaml2();
                    $migration = new Migration($version['version']);
                    $class::uninstall($migration);
                }
            }
        }
    }
    return true;
}

/**
 * Fetches all classes from the plugin \src directory
 *
 * @return boolean
 */
function plugin_phpsaml2_getSrcClasses()                                    //NOSONAR - phpcs:ignore PSR1.Function.CamelCapsMethodName
{
    if(is_dir(PLUGIN_PHPSAML2_SRCDIR)       &&
       is_readable(PLUGIN_PHPSAML2_SRCDIR)  ){
        return array_filter(scandir(PLUGIN_PHPSAML2_SRCDIR, SCANDIR_SORT_NONE), function($item) {
            return !is_dir(PLUGIN_PHPSAML2_SRCDIR.'/'.$item);
        });
    }else{
        echo "The directory". PLUGIN_PHPSAML2_SRCDIR . "Isnt accessible, Plugin installation failed!";
        return false;
    }
}
