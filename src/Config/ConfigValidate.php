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

/*
 * ConfigValidate evaluates, verfies, cleans, normalizes saml config properties before
 * assigning them to the configEntity or invalidates the passed value with an
 * understandable translatable errormessage.
 */
class ConfigValidate                                                        //NOSONAR
{
    public const VALID      = 'valid';
    public const INVALID    = 'invalid';
    public const VALUE      = 'value';
    public const RICHVALUE  = 'richvalue';
    public const EVAL       = 'evaluation';
    public const ERRORS     = 'errors';

    public static function id(mixed $val): array
    {
        return [self::EVAL   => ($val) ? self::VALID : self::INVALID,
                self::VALUE  =>  $val,
                self::ERRORS => ($val) ? null : __('Id is a required field', PLUGIN_NAME)];
    }

    public static function name(mixed $val): array
    {
        return [self::EVAL   => ($val) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $val,
                self::ERRORS => ($val) ? null : __('Name is a required field', PLUGIN_NAME)];
    }

    public static function conf_domain(mixed $val): array                   //NOSONAR
    {
        return [self::EVAL   => ($val) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $val,
                self::ERRORS => ($val) ? null : __('Configuration domain is a required field', PLUGIN_NAME)];
    }

    public static function sp_certificate(mixed $val): array                //NOSONAR
    {
        return [self::EVAL => self::VALID,
                self::VALUE  => $val];
    }

    public static function sp_private_key(mixed $val): array                //NOSONAR
    {
        return [self::EVAL => self::VALID,
                self::VALUE  => $val];
    }

    public static function sp_nameid_format(mixed $val): array              //NOSONAR
    {
        return [self::EVAL   => ($val) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $val,
                self::ERRORS => ($val) ? null : __('Service provider name id is a required field', PLUGIN_NAME)];
    }

    public static function idp_entity_id(mixed $val): array                 //NOSONAR
    {
        return [self::EVAL   => ($val) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $val,
                self::ERRORS => ($val) ? null : __('Identity provider entity id is a required field', PLUGIN_NAME)];
    }

    public static function idp_single_sign_on_service(mixed $val): array    //NOSONAR
    {
        return [self::EVAL   => ($val) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $val,
                self::ERRORS => ($val) ? null : __('Identity provider SSO URL is a required field', PLUGIN_NAME)];
    }

    public static function idp_single_logout_service(mixed $val): array     //NOSONAR
    {
        return [self::EVAL   => ($val) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $val,
                self::ERRORS => ($val) ? null : __('Identity provider SLO URL is a required field', PLUGIN_NAME)];
    }
    public static function idp_certificate(mixed $val): array               //NOSONAR
    {
        return [self::EVAL   => ($val) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $val,
                self::ERRORS => ($val) ? null : __('Identity provider certificate is a required field', PLUGIN_NAME)];
    }

    public static function requested_authn_context(mixed $val): array       //NOSONAR
    {
        return [self::EVAL   => ($val) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $val,
                self::ERRORS => ($val) ? null : __('Requested authN context is a required field', PLUGIN_NAME)];
    }

    public static function requested_authn_context_comparison(mixed $val): array  //NOSONAR
    {
        return [self::EVAL   => ($val) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $val,
                self::ERRORS => ($val) ? null : __('Requested authN context comparison is a required field', PLUGIN_NAME)];
    }

    // IGNORED FIELDS
    public static function conf_icon(mixed $val): array                     //NOSONAR
    {
        return [self::EVAL   => self::VALID,
                self::VALUE  => (string) $val];
    }

    public static function comment(mixed $val): array                       //NOSONAR
    {
        return [self::EVAL   => self::VALID,
                self::VALUE  => (string) $val];
    }

    // Might cast it into an EPOCH date with invalid values.
    public static function date_creation(mixed $val): array                 //NOSONAR
    {
        return [self::EVAL      => self::VALID,
                self::VALUE     => (string) $val,
                self::RICHVALUE => new DateTime($val)];
    }

    // Might cast it into an EPOCH date with invalid values.
    public static function date_mod(mixed $val): array                      //NOSONAR
    {
        return [self::EVAL      => self::VALID,
                self::VALUE     => (string) $val,
                self::RICHVALUE => new DateTime($val)];
    }

    // BOOLEANS
    public static function is_deleted(mixed $val): array                    //NOSONAR
    {
        return self::handleAsBool($val, 'is_deleted');
    }

    public static function is_active(mixed $val): array                     //NOSONAR
    {
        return self::handleAsBool($val, ConfigEntity::IS_ACTIVE);
    }

    public static function enforce_sso(mixed $val): array                   //NOSONAR 
    {
        return self::handleAsBool($val, ConfigEntity::ENFORCE_SSO);
    }

    public static function proxied(mixed $val): array
    {
        return self::handleAsBool($val, ConfigEntity::PROXIED);
    }

    public static function strict(mixed $val): array
    {
        return self::handleAsBool($val, ConfigEntity::STRICT);
    }

    public static function debug(mixed $val): array
    {
        return self::handleAsBool($val, ConfigEntity::DEBUG);
    }

    public static function user_jit(mixed $val): array                      //NOSONAR
    {
        return self::handleAsBool($val, ConfigEntity::USER_JIT);
    }

    public static function security_nameidencrypted(int $val): array        //NOSONAR
    {
        return self::handleAsBool($val, ConfigEntity::ENCRYPT_NAMEID);
    }

    public static function security_authnrequestssigned(int $val): array    //NOSONAR
    {
        return self::handleAsBool($val, ConfigEntity::SIGN_AUTHN);
    }

    public static function security_logoutrequestsigned(int $val): array    //NOSONAR
    {
        return self::handleAsBool($val, ConfigEntity::SIGN_SLO_REQ);
    }

    public static function security_logoutresponsesigned(int $val): array   //NOSONAR
    {
        return self::handleAsBool($val, ConfigEntity::SIGN_SLO_RES);
    }

    public static function compress_requests(int $val): array               //NOSONAR
    {
        return self::handleAsBool($val, ConfigEntity::COMPRESS_REQ);
    }

    public static function compress_responses(int $val): array              //NOSONAR
    {
        return self::handleAsBool($val, ConfigEntity::COMPRESS_RES);
    }

    public static function validate_xml(int $val): array                    //NOSONAR
    {
        return self::handleAsBool($val, ConfigEntity::XML_VALIDATION);
    }

    public static function validate_destination(int $val): array            //NOSONAR
    {
        return self::handleAsBool($val, ConfigEntity::DEST_VALIDATION);
    }

    public static function lowercase_url_encoding(int $val): array          //NOSONAR
    {
        return self::handleAsBool($val, ConfigEntity::LOWERCASE_URL);
    }

    // Make sure we allways return the correct datatype.
    public static function handleAsBool(int $val, $field = null): array
    {
        return [self::EVAL   => (is_numeric($val)) ? self::VALID : self::INVALID,
                self::VALUE  => (bool) $val,
                self::ERRORS => (is_numeric($val)) ? null : __("$field does not appear to be an boolean, defaulted to false", PLUGIN_NAME)];
    }

}