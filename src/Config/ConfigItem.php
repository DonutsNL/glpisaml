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

use DateTime;
use GlpiPlugin\Glpisaml\Config\ConfigEntity;
use Plugin;

/*
 * Validate, evaluate, clean, normalizes, enriches, saml config items before
 * assigning them to the configEntity or invalidates the passed value with an
 * understandable translatable errormessage.
 */
class ConfigItem                                                        //NOSONAR
{
    public const FIELD      = 'field';
    public const TYPE       = 'datatype';
    public const NULL       = 'notnull';
    public const VALUE      = 'value';
    public const VALID      = 'valid';
    public const INVALID    = 'invalid';
    public const RICHVALUE  = 'richvalue';
    public const EVAL       = 'eval';
    public const ERRORS     = 'errors';
    public const CONSTANT   = 'itemconstant';
    public const FORMLABEL  = 'formlabel';
    public const FORMTITLE  = 'formtitle';
    public const VALIDATOR  = 'validator';

    public static function noMethod(string $field, string $value): array
    {
        return [self::FORMLABEL => self::INVALID,
                self::VALUE     => $value,
                self::FIELD     => $field,
                self::EVAL      => false,
                self::ERRORS    => __("Undefined or no type validation found in ConfigValidate for $field", PLUGIN_NAME)];
    }

    public static function id(mixed $val): array
    {
        // Do some validation
        $error = false;
        if($val){
            if($val != -1        &&
               !is_numeric($val) ){
                $error = __('ID must be a positive nummeric value!');
            }
        }

        return [self::FORMLABEL => __('Unique id for a Idp configuration', PLUGIN_NAME),
                self::FORMTITLE => __('Config ID', PLUGIN_NAME),
                self::EVAL      => ($error) ? self::INVALID : self::VALID,
                self::VALUE     => $val,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($error) ? $error : null,
        ];
    }

    public static function name(mixed $val): array
    {
        return [self::FORMLABEL => __('Friendly name for the Idp configuration', PLUGIN_NAME),
                self::FORMTITLE => __('IDP Friendly name', PLUGIN_NAME),
                self::EVAL      => ($val) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $val,
                self::FIELD     => __function__,
                self::ERRORS    => ($val) ? null : __('Name is a required field', PLUGIN_NAME)];
    }

    public static function conf_domain(mixed $val): array                   //NOSONAR
    {
        return [self::FORMLABEL => __('User domain for Idp config matching', PLUGIN_NAME),
                self::FORMTITLE => __('Userdomain', PLUGIN_NAME),
                self::EVAL      => ($val) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $val,
                self::FIELD     => __function__,
                self::ERRORS    => ($val) ? null : __('Configuration domain is a required field', PLUGIN_NAME)];
    }

    public static function sp_certificate(mixed $val): array                //NOSONAR
    {
        return [self::FORMLABEL => __('Service provider X509 certificate', PLUGIN_NAME),
                self::FORMTITLE => __('base64 encoded x509 certificate', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => $val,
                self::FIELD     => __function__];
    }

    public static function sp_private_key(mixed $val): array                //NOSONAR
    {
        return [self::FORMLABEL => __('Service Provider certificate key', PLUGIN_NAME),
                self::FORMTITLE => __('SP Certificate private key', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => $val,
                self::FIELD     => __function__];
    }

    public static function sp_nameid_format(mixed $val): array              //NOSONAR
    {
        return [self::FORMLABEL => __('Service Provider provided nameId format', PLUGIN_NAME),
                self::FORMTITLE => __('Service Provider nameID format', PLUGIN_NAME),
                self::EVAL   => ($val) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $val,
                self::FIELD  => __function__,
                self::ERRORS => ($val) ? null : __('Service provider name id is a required field', PLUGIN_NAME)];
    }

    public static function idp_entity_id(mixed $val): array                 //NOSONAR
    {
        return [self::FORMLABEL => __('Identity provider Entity ID', PLUGIN_NAME),
                self::FORMTITLE => __('Identity Provider Entity ID', PLUGIN_NAME),
                self::EVAL   => ($val) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $val,
                self::FIELD  => __function__,
                self::ERRORS => ($val) ? null : __('Identity provider entity id is a required field', PLUGIN_NAME)];
    }

    public static function idp_single_sign_on_service(mixed $val): array    //NOSONAR
    {
        return [self::FORMLABEL => __('Identity provider SSO URL', PLUGIN_NAME),
                self::FORMTITLE => __('Identity provider SSO URL', PLUGIN_NAME),
                self::EVAL      => ($val) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $val,
                self::FIELD     => __function__,
                self::ERRORS    => ($val) ? null : __('Identity provider SSO URL is a required field', PLUGIN_NAME)];
    }

    public static function idp_single_logout_service(mixed $val): array     //NOSONAR
    {
        return [self::FORMLABEL => __('Identity provider logout URL', PLUGIN_NAME),
                self::FORMTITLE => __('Identity provider logout URL', PLUGIN_NAME),
                self::EVAL      => ($val) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $val,
                self::FIELD     => __function__,
                self::ERRORS    => ($val) ? null : __('Identity provider SLO URL is a required field', PLUGIN_NAME)];
    }
    public static function idp_certificate(mixed $val): array               //NOSONAR
    {
        return [self::FORMLABEL => __('The required Identity Provider certificate', PLUGIN_NAME),
                self::FORMTITLE => __('Identity provider X509 certificate', PLUGIN_NAME),
                self::EVAL      => ($val) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $val,
                self::FIELD     => __function__,
                self::ERRORS    => ($val) ? null : __('Identity provider certificate is a required field', PLUGIN_NAME)];
    }

    public static function requested_authn_context(mixed $val): array       //NOSONAR
    {
        return [self::FORMLABEL => __('Required AuthN context', PLUGIN_NAME),
                self::FORMTITLE => __('Required AuthN context', PLUGIN_NAME),
                self::EVAL      => ($val) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $val,
                self::FIELD     => __function__,
                self::ERRORS    => ($val) ? null : __('Requested authN context is a required field', PLUGIN_NAME)];
    }

    public static function requested_authn_context_comparison(mixed $val): array  //NOSONAR
    {
        return [self::FORMLABEL => __('Required AuthN comparison', PLUGIN_NAME),
                self::FORMTITLE => __('Required AuthN comparison', PLUGIN_NAME),
                self::EVAL      => ($val) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $val,
                self::FIELD     => __function__,
                self::ERRORS    => ($val) ? null : __('Requested authN context comparison is a required field', PLUGIN_NAME)];
    }

    public static function conf_icon(mixed $val): array                     //NOSONAR
    {
        return [self::FORMLABEL => __('Icon to use with this configuration', PLUGIN_NAME),
                self::FORMTITLE => __('Login screen icon', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => (string) $val,
                self::FIELD     => __function__,];
    }

    public static function comment(mixed $val): array                       //NOSONAR
    {
        return [self::FORMLABEL => __('Comments', PLUGIN_NAME),
                self::FORMTITLE => __('Comments', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => (string) $val,
                self::FIELD     => __function__,];
    }

    // Might cast it into an EPOCH date with invalid values.
    public static function date_creation(mixed $val): array                 //NOSONAR
    {
        return [self::FORMLABEL => __('Date the config was created', PLUGIN_NAME),
                self::FORMTITLE => __('Creation date', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => (string) $val,
                self::FIELD     => __function__,
                self::RICHVALUE => new DateTime($val)];
    }

    // Might cast it into an EPOCH date with invalid values.
    public static function date_mod(mixed $val): array                      //NOSONAR
    {
        return [self::FORMLABEL => __('Date the config was modified', PLUGIN_NAME),
                self::FORMTITLE => __('Modification date', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => (string) $val,
                self::FIELD     => __function__,
                self::RICHVALUE => new DateTime($val)];
    }

    // BOOLEANS, We accept mixed, normalize in the handleAsBool function.
    // non ints are defaulted to boolean false.
    public static function is_deleted(mixed $val): array                    //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Is field marked deleted', PLUGIN_NAME),
                            self::FORMTITLE     => __('is deleted', PLUGIN_NAME)],
                            self::handleAsBool($val, 'is_deleted'));
    }

    public static function is_active(mixed $val): array                     //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Is configuration active', PLUGIN_NAME),
                            self::FORMTITLE     => __('Is active', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::IS_ACTIVE));
    }

    public static function enforce_sso(mixed $val): array                   //NOSONAR 
    {
        return array_merge([self::FORMLABEL     => __('Is SSO enforced?', PLUGIN_NAME),
                            self::FORMTITLE     => __('SSO Enforced (depr)', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::ENFORCE_SSO));
    }

    public static function proxied(mixed $val): array
    {
        return array_merge([self::FORMLABEL     => __('Is GLPI proxied', PLUGIN_NAME),
                            self::FORMTITLE     => __('Proxied', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::PROXIED));
    }

    public static function strict(mixed $val): array
    {
        return array_merge([self::FORMLABEL     => __('Is encryption enforced?', PLUGIN_NAME),
                            self::FORMTITLE     => __('Enforce SP encryption', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::STRICT));
    }

    public static function debug(mixed $val): array
    {
        return array_merge([self::FORMLABEL     => __('Is debug enabled?'),
                            self::FORMTITLE     => __('Enable debug', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::DEBUG));
    }

    public static function user_jit(mixed $val): array                      //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Is just in time usercreation enabled?'),
                            self::FORMTITLE     => __('Enable User JIT', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::USER_JIT));
    }

    public static function security_nameidencrypted(mixed $val): array        //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Is nameId encrypted?'),
                            self::FORMTITLE     => __('Encrypt NameID', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::ENCRYPT_NAMEID));
    }

    public static function security_authnrequestssigned(mixed $val): array    //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Is AuthN request encrypted?'),
                            self::FORMTITLE     => __('Encrypt authN request', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::SIGN_AUTHN));
    }

    public static function security_logoutrequestsigned(mixed $val): array    //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Is the logout request Signed?'),
                            self::FORMTITLE     => __('Sign logout request', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::SIGN_SLO_REQ));
    }

    public static function security_logoutresponsesigned(mixed $val): array   //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Is logout response signed?'),
                            self::FORMTITLE     => __('Sign logout response', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::SIGN_SLO_RES));
    }

    public static function compress_requests(mixed $val): array               //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Are requests compressed?'),
                            self::FORMTITLE     => __('Compress Idp requests', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::COMPRESS_REQ));
    }

    public static function compress_responses(mixed $val): array              //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Are responses compressed?'),
                            self::FORMTITLE     => __('Compress Idp responses', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::COMPRESS_RES));
    }

    public static function validate_xml(mixed $val): array                    //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Should we validate XML?'),
                            self::FORMTITLE     => __('Validate XML body', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::XML_VALIDATION));
    }

    public static function validate_destination(mixed $val): array            //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Should we validate destinations?'),
                            self::FORMTITLE     => __('Validate destination', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::DEST_VALIDATION));
    }

    public static function lowercase_url_encoding(mixed $val): array          //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Should we use lowercase encodings?'),
                            self::FORMTITLE     => __('Use lowercase encoding', PLUGIN_NAME)],
                            self::handleAsBool($val, ConfigEntity::LOWERCASE_URL));
    }

    // Make sure we allways return the correct boolean datatype.
    public static function handleAsBool(mixed $val, $field = null): array
    {
        // Default to false if no or an impropriate value is provided.
        $val = (!is_int($val) || $val == -1) ? 0 : 1;
        return [self::EVAL   => (is_numeric($val)) ? self::VALID : self::INVALID,
                self::VALUE  => (bool) $val,
                self::ERRORS => (is_numeric($val)) ? null : __("$field does not appear to be an boolean, defaulted to false", PLUGIN_NAME)];
    }

}