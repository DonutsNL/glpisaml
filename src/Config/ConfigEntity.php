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
namespace GlpiPlugin\Glpisaml\Config;

use Session;
use ReflectionClass;
use GlpiPlugin\Glpisaml\Config as SamlConfig;
use GlpiPlugin\Glpisaml\Config\ConfigValidate;


/*
 * Class ConfigEntity's job is to always return a valid, normalized instance of a
 * samlConfiguration either based on a template or based on a Config database row
 */
class ConfigEntity
{
    /*
     * ConfigEntity can be reflected by this->getConstants(), 
     * private constants are not!
     */
    public const ID             = 'id';                                     // Database ID
    public const NAME           = 'name';                                   // Configuration name, not used in SAML handling
    public const CONF_DOMAIN    = 'conf_domain';                            // Configuration domain, used to identify saml handled logins
    public const CONF_ICON      = 'conf_icon';                              // Configuration ICON to use in UI elements
    public const ENFORCE_SSO    = 'enforce_sso';                            // Enforce SSO
    public const PROXIED        = 'proxied';                                // Handle proxied responses (X-forwarded-for)
    public const STRICT         = 'strict';                                 // Enforce encryption
    public const DEBUG          = 'debug';                                  // Enable debug logging
    public const USER_JIT       = 'user_jit';                               // Enable Just In Time user creation
    public const SP_CERTIFICATE = 'sp_certificate';                         // Service provider certificate
    public const SP_KEY         = 'sp_private_key';                         // Service provider certificate key
    public const SP_NAME_FORMAT = 'sp_nameid_format';                       // Service provider nameID formatting
    public const IDP_ENTITY_ID  = 'idp_entity_id';                          // Identity provider Entity ID
    public const IDP_SSO_URL    = 'idp_single_sign_on_service';             // Identity provider Single Sign On Url
    public const IDP_SLO_URL    = 'idp_single_logout_service';              // Identity provider Logout Url
    public const IDP_CERTIFICATE = 'idp_certificate';                       // Identity provider certificate
    public const AUTHN_CONTEXT  = 'requested_authn_context';                // Requested authn context (to be provided by Idp)
    public const AUTHN_COMPARE  = 'requested_authn_context_comparison';     // Requested authn context comparison (to be evaluated by Idp)
    public const ENCRYPT_NAMEID = 'security_nameidencrypted';               // Encrypt nameId field using service provider certificate
    public const SIGN_AUTHN     = 'security_authnrequestssigned';           // Sign authN request using service provider certificate
    public const SIGN_SLO_REQ   = 'security_logoutrequestsigned';           // Sign logout request using service provider certificate
    public const SIGN_SLO_RES   = 'security_logoutresponsesigned';          // Sign logout response using service provider certificate
    public const COMPRESS_REQ   = 'compress_requests';                      // Compress all requests
    public const COMPRESS_RES   = 'compress_responses';                     // Compress all responses
    public const XML_VALIDATION = 'validate_xml';                           // Validate XML messages
    public const DEST_VALIDATION = 'validate_destination';                  // relax destination validation
    public const LOWERCASE_URL  = 'lowercase_url_encoding';                 // lowercaseUrlEncoding
    public const COMMENT        = 'comment';                                // Field for comments on configuration page
    public const IS_ACTIVE      = 'is_active';                              // Toggle SAML config active or disabled

    /**
     * Set to true if configEntity was found to be valid after population
     */
    private $isValid            = false;

     /**
     * Contains all field values of a certain configuration
     */
    private $fields             = [];

     /**
     * Contains all fields that could not be validated, should be empty
     */
    private $unvalidatedFields  = [];

    /**
     * Contains all validation error messages generated during validation
     */
    private $invalidMessages    = [];

    /**
     * The ConfigEntity class constructor
     *
     * @param  int      $id             - Saml configuration ID to fetch, or null for default template
     * @param  array    $options        - Options['template'] what template to use;
     * @return object   ConfigEntity    - returns an instance of populated ConfigEntity;
     */
    public function __construct(int $id = -1, array $options = [])
    {
        if(!$id || $id == -1) {
            // negative identifier equals populate using a template file
            $template = (!empty($options['template'])) ? $options['template'] : 'Default';
            return $this->validateAndPopulateTemplateEntity($template);
        } else {
            // positive identifier equals populate using on a database row
            return $this->validateAndPopulateDBEntity($id);
        }
    }


    /**
     * Populates the instance of ConfigEntity using a template.
     *
     * @param  string   $template       - name of the Config[NAME]Tpl class to use as template.
     * @return object   ConfigEntity    - returns instance of ConfigEntity.
     */
    private function validateAndPopulateTemplateEntity($template = 'Default'): ConfigEntity
    {
        // Locate our template file
        $templateClass = 'GlpiPlugin\Glpisaml\Config\Config'.$template.'Tpl';
        if(!class_exists($templateClass)){
            //Fallback
            $templateClass = 'GlpiPlugin\Glpisaml\Config\ConfigDefaultTpl';
            if(!class_exists($templateClass)){
                // Fatal issue.
                Session::addMessageAfterRedirect(__("Could not locate configuration template $templateClass, please verify installation!"));
                exit;
            }
        }// Use found template.
        // Perform same validation
        foreach($templateClass::template() as $field => $val){
            // Might be issue, we assume the correct fields are declared in returned array.
            $configAsset = $this->validateConfigFields($field, $val);
            if(isset($configAsset['evaluation']) && $configAsset['evaluation'] == 'valid'){
                $this->fields[$field] = $val;
            }else{
                $this->invalidMessages = array_merge($configAsset['errors'], $this->invalidMessages);
                $this->unvalidatedFields[$field] = $val;
            }
        }
        // Validate the configuration is valid.
        if (empty($this->unvalidatedFields)) {
            $this->isValid = true;
        }// Else keep the default false;
        return $this;
    }


    /**
     * Populates the instance of ConfigEntity using a DB query from the glpisaml config table.
     *
     * @param  int      $id             - id of the database row to fetch
     * @return object   ConfigEntity    - returns instance of ConfigEntity.
     */
    private function validateAndPopulateDBEntity($id): ConfigEntity
    {
        // Get configuration from database;
        $config = new SamlConfig();
        if($config->getFromDB($id)) {
            // Iterate through fetched fields
            foreach($config->fields as $field => $val) {
                // Do validations on all provided fields. All fields need to be
                // verified by GlpiPlugin\Glpisaml\Config\ConfigValidate per default.
                $asset = $this->validateConfigFields($field, $val);
                if(isset($asset['evaluation']) && $asset['evaluation'] == 'valid'){
                    $this->fields[$field] = $asset['value'];
                }else{
                    $this->invalidMessages = (is_Array($asset['errors'])) ? array_merge($asset['errors'], $this->invalidMessages) : $this->invalidMessages;
                    $this->unvalidatedFields[$field] = $asset['value'];
                }
            }

            if (empty($this->unvalidatedFields)) {
                $this->isValid = true;
            }
            return $this;
        }else{
            // Return the default configuration, this exception can be verified
            // by checking the absence of the 'id' field in the returned ConfigEntity.
            return $this->validateAndPopulateTemplateEntity();
        }
    }


    /**
     * Validates and normalizes configuration fields using checks defined
     * in class GlpiPlugin\Glpisaml\Config\ConfigValidate. For instance
     * if defined in ConfigValidate, it will convert DB result (string) '1'
     * too (boolean) true in the returned array for type safety purposes.
     *
     * @param  string   $field  - name of the field to validate
     * @param  mixed    $val    - value beloging to the field.
     * @return array            - result of the validation including normalized values.
     * @see https://www.mysqltutorial.org/mysql-basics/mysql-boolean/
     */
    private function validateConfigFields(string $field, mixed $val): array
    {
        if(is_callable(array((new ConfigValidate), $field))){
            return configValidate::$field($val);
        } else {
            return ['value'     => $val,
                    'errors'    => __('No type validation found in ConfigValidate for $field', PLUGIN_NAME)];
        }
    }


    /**
     * This static function will return the configration constants
     * defined in this class. Idea is to use this reflection to
     * validate the database fields names, numbers and so forth to detect
     * update caused DB issues.
     *
     * @param  void
     * @return array            - defined ConfigEntity class constants.
     * @see https://www.php.net/manual/en/reflectionclass.getconstants.php
     */
    public static function getConstants(): array
    {
        $reflectedObj = new ReflectionClass(__CLASS__);
        return $reflectedObj->getConstants();                  //NOSONAR - ignore S3011 all constants here are intended to be public!
    }


    /**
     * This function will return contextual information about the available
     * configuration fields.
     *
     * Intended for generating Config->searchOptions, perform unittests and debugging.
     * (new ConfigEntity(id))->getFieldTypes() will return DB field information
     * and values of given ID.
     *
     * @param  bool     $debug  - If true will only return fields without predefined class Constant and preloaded value.
     * @return array            - ConfigEntity field information
     */
    public function getFields($debug = false): array
    {
        global $DB;
        $classConstants = self::getConstants();
        $sql = 'SHOW COLUMNS FROM '.SamlConfig::getTable();
        if ($result = $DB->query($sql)) {
            while ($data = $result->fetch_assoc()) {
                if($key = array_search($data['Field'], $classConstants)) {
                    if(!$debug && isset($this->fields[$data['Field']])){
                        $fields[] = [
                            'fieldName' =>  $data['Field'],
                            'fieldType' =>  $data['Type'],
                            'fieldNull' =>  $data['Null'],
                            'fieldConstant' =>  "ConfigEntity::$key",
                            'fieldValue'    =>  (isset($this->fields[$data['Field']])) ? $this->fields[$data['Field']] : 'UNDEFINED'
                        ];
                    }// For testing dont add correct fields to debug array so we can validate with count 0;
                }else{
                    $fields[] = [
                        'fieldName' =>  $data['Field'],
                        'fieldType' =>  $data['Type'],
                        'fieldNull' =>  $data['Null'],
                        'fieldConstant' =>  "UNDEFINED",
                        'fieldValue'    =>  (isset($this->fields[$data['Field']])) ? $this->fields[$data['Field']] : 'UNDEFINED'
                    ];
                }
            }
        }
        return $fields;
    }


    /**
     * Returns the validity state of the currently loaded ConfigEntity
     * @param  void
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }
}

