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
use DateTimeImmutable;
use GlpiPlugin\Glpisaml\Config\ConfigEntity;
use Plugin;

/*
 * Validate, evaluate, clean, normalizes, enriches, saml config items before
 * assigning them to the configEntity or invalidates the passed value with an
 * understandable translatable errormessage.
 */
class ConfigItem    //NOSONAR
{
    public const FIELD      = 'field';                                  // Name of the database field
    public const TYPE       = 'datatype';                               // Database type
    public const NULL       = 'notnull';                                // NOT NULL setting
    public const VALUE      = 'value';                                  // Database value
    public const VALID      = 'valid';                                  // Is content valid?
    public const INVALID    = 'invalid';                                // Is content invalid?
    public const RICHVALUE  = 'richvalue';                              // Rich values (like date object)
    public const EVAL       = 'eval';                                   // Evaluated properties
    public const ERRORS     = 'errors';                                 // Encountered problems notnull will prevent DB update/inserts
    public const VALIDATE   = 'validate';                               // Could either be string or array
    public const CONSTANT   = 'itemconstant';                           // What class constant is used for item
    public const FORMLABEL  = 'formlabel';                              // Form label to use with field
    public const FORMTITLE  = 'formtitle';                              // Form title to use with field
    public const VALIDATOR  = 'validator';                              // What validator was used



    protected function noMethod(string $field, string $varue): array
    {
        return [self::FORMLABEL => self::INVALID,
                self::VALUE     => $varue,
                self::FIELD     => $field,
                self::VALIDATOR => __method__,
                self::EVAL      => false,
                self::ERRORS    => __("⭕ Undefined or no type validation found in ConfigValidate for item: $field", PLUGIN_NAME)];
    }



    protected function id(mixed $var): array
    {
        // Do some validation
        $error = false;
        if($var               &&
            $var != -1        &&
            !is_numeric($var) ){
            $error = __('⭕ ID must be a positive nummeric value!');
        }

        return [self::FORMLABEL => __('Unique id for a Idp configuration', PLUGIN_NAME),
                self::FORMTITLE => __('Config ID', PLUGIN_NAME),
                self::EVAL      => ($error) ? self::INVALID : self::VALID,
                self::VALUE     => $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($error) ? $error : null,
        ];
    }



    protected function name(mixed $var): array
    {
        return [self::FORMLABEL => __('Friendly name for the Idp configuration', PLUGIN_NAME),
                self::FORMTITLE => __('IDP Friendly name', PLUGIN_NAME),
                self::EVAL      => ($var) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($var) ? null : __('⭕ Name is a required field', PLUGIN_NAME)];
    }



    protected function conf_domain(mixed $var): array //NOSONAR
    {
        return [self::FORMLABEL => __('User domain for Idp config matching', PLUGIN_NAME),
                self::FORMTITLE => __('Userdomain', PLUGIN_NAME),
                self::EVAL      => ($var) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($var) ? null : __('⭕ Configuration domain is a required field', PLUGIN_NAME)];
    }



    protected function sp_certificate(mixed $var): array //NOSONAR
    {
        // Certificate is not required, if missing the ConfigEntity will toggle
        // depending security options false.
        return [self::FORMLABEL => __('Service provider X509 certificate', PLUGIN_NAME),
                self::FORMTITLE => __('base64 encoded x509 certificate', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::VALIDATE  => self::parseX509Certificate($var)];
    }



    protected function sp_private_key(mixed $var): array //NOSONAR
    {
        // Private is not required, if missing or invalid the ConfigEntity will toggle
        // depending security options to false.
        return [self::FORMLABEL => __('Service Provider certificate key', PLUGIN_NAME),
                self::FORMTITLE => __('SP Certificate private key', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,];
    }



    protected function sp_nameid_format(mixed $var): array //NOSONAR
    {
        return [self::FORMLABEL => __('Service Provider provided nameId format', PLUGIN_NAME),
                self::FORMTITLE => __('Service Provider nameID format', PLUGIN_NAME),
                self::EVAL   => ($var) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $var,
                self::FIELD  => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS => ($var) ? null : __('Service provider name id is a required field', PLUGIN_NAME)];
    }



    protected function idp_entity_id(mixed $var): array //NOSONAR
    {
        return [self::FORMLABEL => __('Identity provider Entity ID', PLUGIN_NAME),
                self::FORMTITLE => __('Identity Provider Entity ID', PLUGIN_NAME),
                self::EVAL   => ($var) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $var,
                self::FIELD  => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS => ($var) ? null : __('⭕ Identity provider entity id is a required field', PLUGIN_NAME)];
    }



    protected function idp_single_sign_on_service(mixed $var): array //NOSONAR
    {
        $error = false;
        $options = [FILTER_FLAG_PATH_REQUIRED];
        if(!filter_var($var, FILTER_VALIDATE_URL, $options)){
            $error = __('⭕ Invalid Idp SSO URL, use: scheme://host.domain.tld/path/', PLUGIN_NAME);
        }
        

        return [self::FORMLABEL => __('Identity provider SSO URL', PLUGIN_NAME),
                self::FORMTITLE => __('Identity provider SSO URL', PLUGIN_NAME),
                self::EVAL      => ($error) ? self::INVALID : self::VALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($error) ? $error : null,];
    }


    protected function idp_single_logout_service(mixed $var): array //NOSONAR
    {
        $error = false;
        $options = [FILTER_FLAG_PATH_REQUIRED];
        if(!filter_var($var, FILTER_VALIDATE_URL, $options)){
            $error = __('⭕ Invalid Idp SLO URL, use: scheme://host.domain.tld/path/', PLUGIN_NAME);
        }

        return [self::FORMLABEL => __('Identity provider logout URL', PLUGIN_NAME),
                self::FORMTITLE => __('Identity provider logout URL', PLUGIN_NAME),
                self::EVAL      => ($error) ? self::INVALID : self::VALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($error) ? $error : null,];
    }


    protected function idp_certificate(mixed $var): array //NOSONAR
    {
        // Is a required field!
        $e = false;
        if(($certificate = self::parseX509Certificate($var)) &&
           (!array_key_exists('subject', $certificate))      ){
            $e = __('⭕ Valid Idp X509 certificate required! (base64 encoded)', PLUGIN_NAME);
        }

        return [self::FORMLABEL => __('The required Identity Provider certificate', PLUGIN_NAME),
                self::FORMTITLE => __('Identity provider X509 certificate', PLUGIN_NAME),
                self::EVAL      => ($e) ? self::INVALID : self::VALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($e) ? $e : null,
                self::VALIDATE  => self::parseX509Certificate($var)];
    }


    protected function requested_authn_context(mixed $var): array //NOSONAR
    {
        // Normalize multiselect for database insert, form will pass an array
        // Database field expects a string.
        $val = '';
        if(is_array($var)){
            $j = (count($var)-1);
            for($i = 0; $i <= $j; $i++){
                $val .= ($i == $j) ? $var[$i] : $var[$i].':';
            }
        }else{
            $val = $var;
        }
        $val = (empty($val)) ? 'none' : $val;

        return [self::FORMLABEL => __('Required AuthN context', PLUGIN_NAME),
                self::FORMTITLE => __('Required AuthN context', PLUGIN_NAME),
                self::EVAL      => ($val) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $val,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($val) ? null : __('⭕ Requested authN context is a required field', PLUGIN_NAME)];
    }

    protected function requested_authn_context_comparison(mixed $var): array  //NOSONAR
    {
        return [self::FORMLABEL => __('Required AuthN comparison', PLUGIN_NAME),
                self::FORMTITLE => __('Required AuthN comparison', PLUGIN_NAME),
                self::EVAL      => ($var) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($var) ? null : __('⭕ Requested authN context comparison is a required field', PLUGIN_NAME)];
    }

    protected function conf_icon(mixed $var): array                     //NOSONAR
    {
        return [self::FORMLABEL => __('Icon to use with this configuration', PLUGIN_NAME),
                self::FORMTITLE => __('Login screen icon', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => (string) $var,
                self::VALIDATOR => __method__,
                self::FIELD     => __function__,
                self::ERRORS    => ($var) ? null : __('⭕ Configuration icon is a required field', PLUGIN_NAME)];
    }

    protected function comment(mixed $var): array                       //NOSONAR
    {
        return [self::FORMLABEL => __('Comments', PLUGIN_NAME),
                self::FORMTITLE => __('Comments', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => (string) $var,
                self::VALIDATOR => __method__,
                self::FIELD     => __function__,];
    }

    // Might cast it into an EPOCH date with invalid values.
    protected function date_creation(mixed $var): array                 //NOSONAR
    {
        return [self::FORMLABEL => __('Date the config was created', PLUGIN_NAME),
                self::FORMTITLE => __('Creation date', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::RICHVALUE => new DateTime($var)];
    }

    // Might cast it into an EPOCH date with invalid values.
    protected function date_mod(mixed $var): array                      //NOSONAR
    {
        return [self::FORMLABEL => __('Date the config was modified', PLUGIN_NAME),
                self::FORMTITLE => __('Modification date', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::RICHVALUE => new DateTime($var)];
    }

    // BOOLEANS, We accept mixed, normalize in the handleAsBool function.
    // non ints are defaulted to boolean false.
    protected function is_deleted(mixed $var): array                    //NOSONAR
    {
        if(empty($var)){ $var = '0'; }

        return array_merge([self::FORMLABEL     => __('Is field marked deleted', PLUGIN_NAME),
                            self::FORMTITLE     => __('is deleted', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, 'is_deleted'));
    }

    protected function is_active(mixed $var): array                     //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Is configuration active', PLUGIN_NAME),
                            self::FORMTITLE     => __('Is active', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::IS_ACTIVE));
    }

    protected function enforce_sso(mixed $var): array                   //NOSONAR 
    {
        return array_merge([self::FORMLABEL     => __('Is SSO enforced?', PLUGIN_NAME),
                            self::FORMTITLE     => __('SSO Enforced (depr)', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::ENFORCE_SSO));
    }

    protected function proxied(mixed $var): array
    {
        return array_merge([self::FORMLABEL     => __('Is GLPI proxied', PLUGIN_NAME),
                            self::FORMTITLE     => __('Proxied', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::PROXIED));
    }

    protected function strict(mixed $var): array
    {
        return array_merge([self::FORMLABEL     => __('Is encryption enforced?', PLUGIN_NAME),
                            self::FORMTITLE     => __('Enforce SP encryption', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::STRICT));
    }

    protected function debug(mixed $var): array
    {
        return array_merge([self::FORMLABEL     => __('Is debug enabled?'),
                            self::FORMTITLE     => __('Enable debug', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::DEBUG));
    }

    protected function user_jit(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Is just in time usercreation enabled?'),
                            self::FORMTITLE     => __('Enable User JIT', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::USER_JIT));
    }

    protected function security_nameidencrypted(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Is nameId encrypted?'),
                            self::FORMTITLE     => __('Encrypt NameID', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::ENCRYPT_NAMEID));
    }

    protected function security_authnrequestssigned(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Is AuthN request encrypted?'),
                            self::FORMTITLE     => __('Encrypt authN request', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::SIGN_AUTHN));
    }

    protected function security_logoutrequestsigned(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Is the logout request Signed?'),
                            self::FORMTITLE     => __('Sign logout request', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::SIGN_SLO_REQ));
    }

    protected function security_logoutresponsesigned(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Is logout response signed?'),
                            self::FORMTITLE     => __('Sign logout response', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::SIGN_SLO_RES));
    }

    protected function compress_requests(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Are requests compressed?'),
                            self::FORMTITLE     => __('Compress Idp requests', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::COMPRESS_REQ));
    }

    protected function compress_responses(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Are responses compressed?'),
                            self::FORMTITLE     => __('Compress Idp responses', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::COMPRESS_RES));
    }

    protected function validate_xml(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Should we validate XML?'),
                            self::FORMTITLE     => __('Validate XML body', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::XML_VALIDATION));
    }

    protected function validate_destination(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Should we validate destinations?'),
                            self::FORMTITLE     => __('Validate destination', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::DEST_VALIDATION));
    }

    protected function lowercase_url_encoding(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMLABEL     => __('Should we use lowercase encodings?'),
                            self::FORMTITLE     => __('Use lowercase encoding', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::LOWERCASE_URL));
    }

    // Make sure we allways return the correct boolean datatype.
    protected function handleAsBool(mixed $var, $field = null): array
    {
        // Default to false if no or an impropriate value is provided.
        $error = (!empty($var) && !preg_match('/[0-1]/', $var)) ? __("⭕ $field can only be 1 or 0", PLUGIN_NAME) : null;

        return [self::EVAL   => (is_numeric($var)) ? self::VALID : self::INVALID,
                self::VALUE  => (!$error) ? $var : '0',
                self::ERRORS => $error];
    }

    protected function parseX509Certificate(string $certificate): array|bool         //NOSONAR - Maybe fix complexity in the future
    {
        // Try to parse the reconstructed certificate.
        if (function_exists('openssl_x509_parse')) {
            $validations = [];
            if ($parsedCertificate = openssl_x509_parse($certificate)) {
                $n = new DateTimeImmutable('now');
                $t = (array_key_exists('validTo', $parsedCertificate)) ? DateTimeImmutable::createFromFormat("ymdHisT", $parsedCertificate['validTo']) : '';
                $f = (array_key_exists('validFrom', $parsedCertificate)) ? DateTimeImmutable::createFromFormat("ymdHisT", $parsedCertificate['validFrom']) : '';
                $aged = $n->diff($t);
                $born = $f->diff($n);
                $cn= $parsedCertificate['subject']['CN'];
                $aged = $aged->format('%R%a');
                if(strpos($aged,'-') !== false){
                    $validations['validTo'] = __("⚠️ Warning, certificate with Common Name (CN): $cn is expired: $aged days", PLUGIN_NAME);
                }
                $born = $born->format('%R%a');
                // Check issue date
                if(strpos($born,'-') !== false){
                    $validations['validFrom'] = __("⚠️ Warning, certificate with Common Name (CN): $cn issued in the future ($born days)", PLUGIN_NAME);
                }
                $parsedCertificate['validations'] = $validations;
                return $parsedCertificate;
            }else{
                return ['validations'   => __('⚠️ No valid X509 certificate found')];
            }
        } else {
            // Cant parse certificate OpenSSL not availble!
            return false;
        }
    }

    protected function validateCertKeyPairModulus(string $certificate, string $privateKey): bool         //NOSONAR - Maybe fix complexity in the future
    {
        if (function_exists('openssl_x509_parse') && function_exists('openssl_x509_check_private_key')){
            return (openssl_x509_check_private_key($certificate, [$privateKey, ''])) ? true : false;
        }else{
            // Cannot validate always return true;
            return true;
        }
    }

}
