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

use Session;
use Exception;
use User as glpiUser;
use GlpiPlugin\Glpisaml\LoginFlow;
use GlpiPlugin\Glpisaml\LoginState;
use GlpiPlugin\Glpisaml\RulerightCollection;
use GlpiPlugin\Glpisaml\Config\ConfigEntity;

class User
{

    /**
     * Gets or creates (if JIT is enabled for IDP) the GLPI user.
     *
     * @param   Response    SamlResponse to be evaluated.
     * @return  array       with user attributes
     * @since               1.0.0
     */
    public function getOrCreateUser(array $attributes): glpiUser
    {
        // Load GLPI user object
        $user = new \User();

        // Verify if user exists in database.
        if(!$user->getFromDBbyName($attributes['userData'][LoginFlow::SCHEMA_NAME]['0'])          &&
           !$user->getFromDBbyEmail($attributes['userData'][LoginFlow::SCHEMA_EMAILADDRESS]['0']) ){
            
            // Get current state
            if(!$state = new Loginstate()){ 
                throw new Exception(__('Could not load loginState from database!', PLUGIN_NAME)); //NOSONAR
            }
            // Fetch the correct configEntity
            if(!$configEntity = new ConfigEntity($state->getIdpId())){
                throw new Exception(__('Could not load ConfigEntity from database!', PLUGIN_NAME)); //NOSONAR
            }
            // Are we allowed to perform JIT user creation?
            if($configEntity->getField(ConfigEntity::USER_JIT)){

                 // Grab the correct firstname
                 $firstname = (isset($attributes['userData'][LoginFlow::SCHEMA_FIRSTNAME])) ? $attributes['userData'][LoginFlow::SCHEMA_FIRSTNAME][0]
                                                                                            : $attributes['userData'][LoginFlow::SCHEMA_GIVENNAME][0];
                 // Populate the input fields.
                 $password = bin2hex(random_bytes(20));
                 $input = ['name'        => $attributes['userData'][LoginFlow::SCHEMA_NAME][0],
                           'realname'    => $attributes['userData'][LoginFlow::SCHEMA_SURNAME][0],
                           'firstname'   => $firstname,
                           '_useremails' => [$attributes['userData'][LoginFlow::SCHEMA_EMAILADDRESS][0]],
                           'comment'     => 'Created by phpSaml Just-In-Time user creation on:'.date('Y-M-D H:i:s'),
                           'password'    => $password,
                           'password2'   => $password];

                if(!$id = $user->add($input)){
                    LoginFlow::showLoginError(__("Your SSO login was succesfull but there is no matching GLPI user account and
                                                  we failed to create one dynamically using Just In Time usercreation. Please
                                                  request a GLPI administrator to review the logs and correct the problem or
                                                  request the administrator to create a GLPI user manually.", PLUGIN_NAME));
                }

                // Load the rulesEngine and process them
                // If a match is made on email then a hook
                // see setup.php > hook.php.
                // is called by the ruleCollection object.
                $phpSamlRuleCollection = new RuleRightCollection();
                $matchInput = ['_useremails' => $input['_useremails']];
                $phpSamlRuleCollection->processAllRules($matchInput, [], []);

                // Return freshly created user!
                $user = new \User();
                if($user->getFromDB($id)){
                    Session::addMessageAfterRedirect('Dynamically created GLPI user for:'.$attributes['userData'][LoginFlow::SCHEMA_NAME][0]);
                    return $user;
                }
            }else{
                $idpName = $configEntity->getField(ConfigEntity::NAME);
                $email   = $attributes['userData'][LoginFlow::SCHEMA_EMAILADDRESS]['0'];
                LoginFlow::showLoginError(__("Your SSO login was succesfull but there is no matching GLPI user account. In addition the Just-in-time user creation
                                              is disabled for: $idpName. Please contact your GLPI administrator and request an account to be created matching the
                                              provided email claim: $email or login using a local user account.", PLUGIN_NAME));
            }
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

    public function updateUser(array $params): void
    {
        var_dump($params);
        exit;
    }
}
