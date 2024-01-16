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
 *
 * POV: The correct object name should be 'Authflow.' But people tend to
 * generalize and not care for the individual steps of IAAA,
 * so for maintainability purposes I chose to call it 'login' instead
 * of 'Auth' where Auth might also cause duplication issues where Auth is
 * also being handled by OneLogin\PhpSaml\Auth.
 *
 **/

namespace GlpiPlugin\Phpsaml2;

use Session;
use CommonDBTM;
use Migration;
use GlpiPlugin\Phpsaml2\Exclude;
use GlpiPlugin\Phpsaml2\Loginflow\Loginstate;

class Loginflow extends CommonDBTM
{
        // Never perform auth for CLI calls
        /*
        if ( PHP_SAPI === 'cli' ){
           return true;
        }
        */

        /**
         * getMenuContent() : array | bool -
         * Method called by pre_item_add hook validates the object and passes
         * it to the RegEx Matching then decides what to do.
         *
         * @return mixed             boolean|array
         */
        public function evalAuth(){
            // GET ALL FILES FROM SRC DIRECTORY
            if(is_dir(PLUGIN_PHPSAML2_SRCDIR) &&
               is_readable(PLUGIN_PHPSAML2_SRCDIR)  ){
                $files = array_filter(scandir(PLUGIN_PHPSAML2_SRCDIR, SCANDIR_SORT_NONE), function($item) {
                    return !is_dir(PLUGIN_PHPSAML2_SRCDIR.'/'.$item);
                });
            }else{
                echo "The directory". PLUGIN_PHPSAML2_SRCDIR . "Isnt accessible, Plugin installation failed!";
                return false;
            }
            // TRY TO CALL CLASS INSTALLERS
            if(is_array($files)) {
                foreach($files as $name){
                    // Load the class
                    $className = "GlpiPlugin\\Phpsaml2\\" . basename($name, '.php');
                    if(method_exists($className, 'install')){
                        print basename($name, '.php') . ' : True<br>';
                    }
                }
            }
            die();
        }
}
