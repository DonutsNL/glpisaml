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
 *  @version    1.1.0
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
use Group_User;
use Profile_User;
use User as glpiUser;
use Glpi\Toolbox\Sanitizer;
use GlpiPlugin\Glpisaml\LoginFlow;
use GlpiPlugin\Glpisaml\LoginState;
use GlpiPlugin\Glpisaml\RuleSamlCollection;
use GlpiPlugin\Glpisaml\Config\ConfigEntity;

/**
 * This class is responsible to make sure a corresponding
 * user is returned after successfull login. If a user does
 * not exist it will create one if JIT is enabled else it will
 * trigger a human readable error. On Jit creation it will also
 * call the RuleSamlCollection and parse any configured rules.
 */
class User
{
    // Common user/group/profile constants
    public const USERID    = 'id';
    public const NAME       = 'name';
    public const REALNAME   = 'realname';
    public const FIRSTNAME  = 'firstname';
    public const EMAIL      = '_useremails';
    public const COMMENT    = 'comment';
    public const PASSWORD   = 'password';
    public const PASSWORDN  = 'password2';
    public const DELETED    = 'is_deleted';
    public const ACTIVE     = 'is_active';
    public const RULEOUTPUT = 'output';
    public const USERSID    = 'users_id';
    public const GROUPID    = 'groups_id';
    public const GROUP_DEFAULT = 'specific_groups_id';
    public const IS_DYNAMIC = 'is_dynamic';
    public const PROFILESID = 'profiles_id';
    public const PROFILE_DEFAULT = '_profiles_id_default';
    public const PROFILE_RECURSIVE = 'is_recursive';
    public const ENTITY_ID  = 'entities_id';
    public const ENTITY_DEFAULT = '_entities_id_default';


    /**
     * Gets or creates (if JIT is enabled for IDP) the GLPI user.
     *
     * @param   array       Containing user attributes found in Saml claim
     * @return  glpiUser    GlpiUser object with populated fields.
     * @since               1.0.0
     */
    public function getOrCreateUser(array $attributes): glpiUser    //NOSONAR Complexity by design
    {
        // Load GLPI user object
        $user = new glpiUser();
        
        // Verify if user exists in database.
        if(!$user->getFromDBbyName($attributes[LoginFlow::USERDATA][LoginFlow::SCHEMA_NAME]['0'])          &&
           !$user->getFromDBbyEmail($attributes[LoginFlow::USERDATA][LoginFlow::SCHEMA_EMAILADDRESS]['0']) ){
            
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
                 $firstname = (isset($attributes[LoginFlow::USERDATA][LoginFlow::SCHEMA_FIRSTNAME])) ? $attributes[LoginFlow::USERDATA][LoginFlow::SCHEMA_FIRSTNAME][0]
                                                                                                     : $attributes[LoginFlow::USERDATA][LoginFlow::SCHEMA_GIVENNAME][0];
                 // Populate the input fields.
                 $password = bin2hex(random_bytes(20));
                 $input = [self::NAME        => $attributes[LoginFlow::USERDATA][LoginFlow::SCHEMA_NAME][0],
                           self::REALNAME    => $attributes[LoginFlow::USERDATA][LoginFlow::SCHEMA_SURNAME][0],
                           self::FIRSTNAME   => $firstname,
                           self::EMAIL       => [$attributes[LoginFlow::USERDATA][LoginFlow::SCHEMA_EMAILADDRESS][0]],
                           self::COMMENT     => __('Created by phpSaml Just-In-Time user creation on:'.date('Y-M-D H:i:s')),
                           self::PASSWORD    => $password,
                           self::PASSWORDN   => $password];

                if(!$id = $user->add(Sanitizer::sanitize($input))){
                    LoginFlow::showLoginError(__("Your SSO login was succesfull but there is no matching GLPI user account and
                                                  we failed to create one dynamically using Just In Time usercreation. Please
                                                  request a GLPI administrator to review the logs and correct the problem or
                                                  request the administrator to create a GLPI user manually.", PLUGIN_NAME));
                    // PHP0405-no return by design.
                }else{
                    $ruleCollection = new RuleSamlCollection();
                    $matchInput = [self::EMAIL => $input[self::EMAIL]];
                    // Uses a hook to call $this->updateUser() if a rule was found.
                    $ruleCollection->processAllRules($matchInput, [self::USERSID => $id], []);
                }

                // Return freshly created user!
                $user = new glpiUser();
                if($user->getFromDB($id)){
                    Session::addMessageAfterRedirect('Dynamically created GLPI user for:'.$attributes[LoginFlow::USERDATA][LoginFlow::SCHEMA_NAME][0]);
                    return $user;
                }
            }else{
                $idpName = $configEntity->getField(ConfigEntity::NAME);
                $email   = $attributes[LoginFlow::USERDATA][LoginFlow::SCHEMA_EMAILADDRESS]['0'];
                LoginFlow::showLoginError(__("Your SSO login was succesfull but there is no matching GLPI user account. In addition the Just-in-time user creation
                                              is disabled for: $idpName. Please contact your GLPI administrator and request an account to be created matching the
                                              provided email claim: $email or login using a local user account.", PLUGIN_NAME));
                // PHP0405-no return by design.
            }
        }else{
            // Verify the user is not deleted (in trashbin)
            if($user->fields[self::DELETED]){
                LoginFlow::showLoginError(__("User with GlpiUserid: ".$user->fields[self::USERID]." is marked deleted but still exists in the GLPI database. Because of
                                           this we cannot log you in as this would violate GLPI its security policies. Please contact the GLPI administrator
                                           to restore the user with provided ID or purge the user to allow the Just in Time (JIT) usercreation to create a
                                           new user with the idp provided claims.", PLUGIN_NAME));
                // PHP0405-no return by design.
            }
            // Verify the user is not disabled by the admin;
            if($user->fields[self::ACTIVE] == 0){
                LoginFlow::showLoginError(__("User with GlpiUserid: ".$user->fields[self::USERID]." is disabled. Please contact your GLPI administrator and request him to
                                            reactivate your account.", PLUGIN_NAME));
                // PHP0405-no return by design.
            }
            // Return the user to the Loginflow object for session initialization!.
            return $user;
        }
    }

    
    public function updateUserRights(array $params): void
    {
        $update = $params[self::RULEOUTPUT];
        // Do we need to add a group?
        if(isset($update[self::GROUPID])  &&
           isset($update[self::USERSID])  ){
            // Get the Group_User object to update the user group relation.
            $groupuser = new Group_User();
            if(!$groupuser->add([self::USERSID   => $update[self::USERSID],
                                 self::GROUPID   => $update[self::GROUPID]])){
                Session::addMessageAfterRedirect(__('GLPI SAML was not able to assign the correct permissions to your user.
                                                     Please let an Administrator review them before using GLPI.',PLUGIN_NAME));
            }
        }

        // Do we need to add profiles
        // If no profiles_id and user_idis present we skip.
        if(isset($update[self::PROFILESID]) && isset($update[self::USERSID])){
            // Set the user to update
            $rights[self::USERSID] = $update[self::USERSID];
            // Set the profile to rights assignment
            $rights[self::PROFILESID] = $update[self::PROFILESID];
            // Do we need to set a profile for a specific entity?
            if(isset($update[self::ENTITY_ID])){
                $rights[self::ENTITY_ID] = $update[self::ENTITY_ID];
            }
            // Do we need to make the profile behave recursive?
            if(isset($update[self::PROFILE_RECURSIVE])){
                $rights[self::PROFILE_RECURSIVE] = (isset($update[self::PROFILE_RECURSIVE])) ? '1' : '0';
            }
            // Delete all default profile assignments
            $profileUser = new Profile_User();
            if($pid = $profileUser->getForUser($update[self::USERSID])){
                foreach($pid as $key => $data){
                    $profileUser->delete(['id' => $key]);
                }
            }
            // Assign collected Rights
            $profileUser = new Profile_User();
            if(!$profileUser->add($rights)){
                Session::addMessageAfterRedirect(__('GLPI SAML was not able to assign the correct permissions to your user.
                                                    Please let an Administrator review the user before using GLPI.',PLUGIN_NAME));
            }
        }

        // Do we need to update the user profile defaults?
        if(isset($update[self::GROUP_DEFAULT])   ||
           isset($update[self::ENTITY_DEFAULT]) ||
           isset($update[self::PROFILE_DEFAULT]) ){
            // Set the user Id.
            $userDefaults['id'] = $update['users_id'];
            // Do we need to set a default group?
            if(isset($update[self::GROUP_DEFAULT])){
                $userDefaults[self::GROUPID]  = $update[self::GROUP_DEFAULT];
            }
            // Do we need to set a specific default entity?
            if(isset($update[self::ENTITY_DEFAULT])){
                $userDefaults[self::ENTITY_ID] = $update[self::ENTITY_DEFAULT];
            }
            // Do we need to set a specific profile?
            if(isset($update[self::PROFILE_DEFAULT])){
                $userDefaults[self::PROFILESID] = $update[self::PROFILE_DEFAULT];
            }

            $user = new glpiUser();
            if(!$user->update($userDefaults)){
                Session::addMessageAfterRedirect(__('GLPI SAML was not able to update the user defaults.
                                                     Please let an administrator review the user before using GLPI.',PLUGIN_NAME));
            }
        }
    }
}
