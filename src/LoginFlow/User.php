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

use GlpiPlugin\Glpisaml\LoginFlow;

class User
{

    /**
     * Gets or creates (if JIT is enabled for IDP) the GLPI user.
     *
     * @param   Response    SamlResponse to be evaluated.
     * @return  array       with user attributes
     * @since               1.0.0
     */
    public function getOrCreateUser(array $attributes)
    {
        // Load GLPI user object
        $user = new \User();
        // Verify if user exists in database.
        if(!$user->getFromDBbyName($attributes['userData'][LoginFlow::SCHEMA_NAME]['0'])          &&
           !$user->getFromDBbyEmail($attributes['userData'][LoginFlow::SCHEMA_EMAILADDRESS]['0']) ){
                // Do JIT?
                print 'do we need JIT? ';
                exit;
        }else{
            // Verify the user is not deleted (in trashbin)
            if($user->fields['is_deleted']){
                LoginFlow::showLoginError(__("User with GlpiUserid: ".$user->fields['id']." is marked deleted but still exists in the GLPI database. Because of
                                           this we cannot log you in as this would violate GLPI its security policies. Please contact the GLPI administrator
                                           to restore the user with provided ID or purge the user to allow the Just in Time (JIT) usercreation to create a
                                           new user with the idp provided claims.", PLUGIN_NAME));
            }
            // Verify the user is not disabled by the admin;
            if($user->fields['is_active'] == 0){
                LoginFlow::showLoginError(__("User with GlpiUserid: ".$user->fields['id']." is disabled. Please contact your GLPI administrator and request him to
                                            reactivate your account.", PLUGIN_NAME));
            }
            // Return the user to the Loginflow object for session initialization!.
            return $user;
        }
    }
}
