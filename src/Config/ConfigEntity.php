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

use ReflectionClass;
use GlpiPlugin\Glpisaml\Config as SamlConfig;
use GlpiPlugin\Glpisaml\Config\ConfigValidate;

class ConfigEntity
{
    // Database fields
    public const ID             = 'id';
    public const NAME           = 'name';
    public const CONF_DOMAIN    = 'conf_domain';
    public const CONF_ICON      = 'conf_icon';
    public const ENFORCE_SSO    = 'enforce_sso';
    public const PROXIED        = 'proxied';
    public const STRICT         = 'strict';
    public const DEBUG          = 'debug';
    public const USER_JIT       = 'user_jit';
    public const SP_CERTIFICATE = 'sp_certificate';
    public const SP_KEY         = 'sp_private_key';
    public const SP_NAME_FORMAT = 'sp_nameid_format';
    public const IDP_ENTITY_ID  = 'idp_entity_id';
    public const IDP_SSO_URL    = 'idp_single_sign_on_service';
    public const IDP_SLO_URL    = 'idp_single_logout_service';
    public const IDP_CERTIFICATE = 'idp_certificate';
    public const AUTHN_CONTEXT  = 'requested_authn_context';
    public const AUTHN_COMPARE  = 'requested_authn_context_comparison';
    public const ENCRYPT_NAMEID = 'security_nameidencrypted';
    public const SIGN_AUTHN     = 'security_authnrequestssigned';
    public const SIGN_SLO_REQ   = 'security_logoutrequestsigned';
    public const SIGN_SLO_RES   = 'security_logoutresponsesigned';
    public const COMPRESS_REQ   = 'compress_requests';
    public const COMPRESS_RES   = 'compress_responses';
    public const XML_VALIDATION = 'validate_xml';
    public const DEST_VALIDATION = 'validate_destination';  // relax destination validation
    public const LOWERCASE_URL  = 'lowercase_url_encoding'; // lowercaseUrlEncoding
    public const COMMENT        = 'comment';
    public const IS_ACTIVE      = 'is_active';

    // Entity properties
    private $isValid            = false;
    private $invalidMessages    = [];
    private $fields             = [];
    private $unvalidatedFields  = [];

    public function __construct(int $id = -1, string $template = 'default')
    {
        // Make sure we always return a consistant Configuration entity
        if($id == -1) {
            // Get some example configuration config
            return $this->buildTemplateEntity($template);
        } else {
            return $this->validateAndBuildEntity($id);
        }
    }

    // Create entity using template
    private function buildTemplateEntity($template): ConfigEntity
    {
        $templateClass = 'GlpiPlugin\Glpisaml\Config\ConfigTpl'.$template;
        if(!class_exists($templateClass)){
            $templateClass = 'GlpiPlugin\Glpisaml\Config\ConfigTplDefault';
        }
        // Perform same validation
        foreach($templateClass::Template as $field => $val){
            $asset = $this->validate($field, $val);
            if(isset($asset['evaluation']) && $asset['evaluation'] == 'valid'){
                $this->fields[$field] = $val;
            }else{
                $this->invalidMessages = array_merge($asset['errors'], $this->invalidMessages);
                $this->unvalidatedFields[$field] = $val;
            }
        }
        if (empty($this->unvalidatedFields)) {
            $this->isValid = true;
        }
        return $this;
    }

    // Create entity using DB id
    private function validateAndBuildEntity($id): ConfigEntity
    {
        // Get configuration from database;
        $config = new SamlConfig();
        $config->getFromDB($id);
        // 
        foreach($config->fields as $field => $val) {
            // Do defined validations
            $asset = $this->validate($field, $val);
            if(isset($asset['evaluation']) && $asset['evaluation'] == 'valid'){
                $this->fields[$field] = $val;
            }else{
                $this->invalidMessages = array_merge($asset['errors'], $this->invalidMessages);
                $this->unvalidatedFields[$field] = $val;
            }
        }
        if (empty($this->unvalidatedFields)) {
            $this->isValid = true;
        }
        return $this;
    }

    private function validate(string $field, $val): array
    {
        if(is_callable(array((new ConfigValidate), $field))){
            return configValidate::$field($val);
        } else {
            return [];
        }
    }

    public static function getConstants() {
        $class = new ReflectionClass(__CLASS__);
        return $class->getConstants();                  //NOSONAR - ignore S3011 all constants here are intended to be public!
    }

    public function isValid(): bool
    {
        return $this->isValid;
    }

    public function getConfig(): array
    {
        return $this->fields;
    }
}

