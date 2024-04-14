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
    public const FORMEXPLAIN= 'formexplain';                            // Field explaination
    public const FORMTITLE  = 'formtitle';                              // Form title to use with field
    public const VALIDATOR  = 'validator';                              // What validator was used



    protected function noMethod(string $field, string $varue): array
    {
        return [self::FORMEXPLAIN => self::INVALID,
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

        return [self::FORMEXPLAIN => __('Unique identifier for this configuration', PLUGIN_NAME),
                self::FORMTITLE => __('CONFIG ID', PLUGIN_NAME),
                self::EVAL      => ($error) ? self::INVALID : self::VALID,
                self::VALUE     => $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($error) ? $error : null,
        ];
    }



    protected function name(mixed $var): array
    {
        return [self::FORMEXPLAIN => __('This name is shown with the login button on the login page.
                                         Try to keep this name short en to the point.', PLUGIN_NAME),
                self::FORMTITLE => __('FRIENDLY NAME', PLUGIN_NAME),
                self::EVAL      => ($var) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($var) ? null : __('⭕ Name is a required field', PLUGIN_NAME)];
    }



    protected function conf_domain(mixed $var): array //NOSONAR
    {
        return [self::FORMEXPLAIN => __('Future use', PLUGIN_NAME),
                self::FORMTITLE => __('USERDOMAIN', PLUGIN_NAME),
                self::EVAL      => ($var) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($var) ? null : __('⭕ Configuration domain is a required field', PLUGIN_NAME)];
    }



    protected function sp_certificate(mixed $var): array //NOSONAR
    {
        // Certificate is not required, if missing the ConfigEntity will toggle
        // depending security options false if there is an error. Provided certificate
        // string (if any) should be valid.
         $e = false;
        if((!empty($var))                                     &&
           ($certificate = self::parseX509Certificate($var)) &&
           (!array_key_exists('subject', $certificate))      ){

            $e = __('⭕ Provided certificate does not like look a valid (base64 encoded) certificate', PLUGIN_NAME);
        }
        return [self::FORMEXPLAIN => __('The base62 encoded x509 service provider certificate. Used to sign and encrypt
                                         messages send by the service provider to the identity provider. Required for most
                                         of the security options', PLUGIN_NAME),
                self::FORMTITLE => __('SP CERTIFICATE', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($e) ? $e : null,
                self::VALIDATE  => $certificate];
    }



    protected function sp_private_key(mixed $var): array //NOSONAR
    {
        // Private is not required, if missing or invalid the ConfigEntity will toggle
        // depending security options to false.
        return [self::FORMEXPLAIN => __('The base62 encoded x509 service providers private key. Should match the modulus of the
                                         provided X509 service provider certificate', PLUGIN_NAME),
                self::FORMTITLE => __('SP Certificate private key', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,];
    }



    protected function sp_nameid_format(mixed $var): array //NOSONAR
    {
        return [self::FORMEXPLAIN => __('The Service Provider nameid format specifies the constraints
                                         on the name identifier to be used to represent the requested
                                         subject.', PLUGIN_NAME),
                self::FORMTITLE => __('NAMEID FORMAT', PLUGIN_NAME),
                self::EVAL   => ($var) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $var,
                self::FIELD  => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS => ($var) ? null : __('Service provider name id is a required field', PLUGIN_NAME)];
    }



    protected function idp_entity_id(mixed $var): array //NOSONAR
    {
        return [self::FORMEXPLAIN => __('Identifier of the IdP entity which is an URL provided by
                                         the SAML2 Identity Provider (IdP)', PLUGIN_NAME),
                self::FORMTITLE => __('ENTITY ID', PLUGIN_NAME),
                self::EVAL   => ($var) ? self::VALID : self::INVALID,
                self::VALUE  => (string) $var,
                self::FIELD  => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS => ($var) ? null : __('⭕ Identity provider entity id is a required field', PLUGIN_NAME)];
    }

    /**
     * Validates the URL the passed SAML Single Sign On Service string
     * @param string    Single Sign On Service URL to be validated
     * @return array    Contextual information about the parameter and validation outcomes
     */
    protected function idp_single_sign_on_service(string $var): array //NOSONAR
    {
        $error = '';
        // This setting is required for SAML to function
        if(empty($var)){
            $error .= __('⭕ The IdP SSO URL is a required field!<br>', PLUGIN_NAME);
        }
        // The value should look like a valid URL
        $options = [FILTER_FLAG_PATH_REQUIRED];
        if(!filter_var($var, FILTER_VALIDATE_URL, $options)){
            $error .= __('⭕ Invalid IdP SSO URL, use: scheme://host.domain.tld/path/', PLUGIN_NAME);
        }
        // Maybe add actual webcall here to validate if the URL is accessible
        // if its not, show a warning that the validity of the url could not be validated
        // Accessibility by the server is not a requirement given its the client browser
        // that needs to access the provided resource not the webserver itself.
        
        return [self::FORMEXPLAIN => __('Single Sign On Service endpoint of the IdP. URL Target of the IdP where the
                                         Authentication Request Message will be sent. OneLogin PHPSAML
                                         only supports the \'HTTP-redirect\' binding for this endpoint.', PLUGIN_NAME),
                self::FORMTITLE => __('SSO URL', PLUGIN_NAME),
                self::EVAL      => ($error) ? self::INVALID : self::VALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($error) ? $error : null,];
    }

    /**
     * Validates the URL the passed SAML Single Log Off Service string
     * @param string    Single Sign On Service URL to be validated
     * @return array    Contextual information about the parameter and validation outcomes
     */
    protected function idp_single_logout_service(string $var): array //NOSONAR
    {
        $error = false;
        // This setting is not required because for example in Azure it will log the user out
        // of all online sessions not just GLPI. No url will result in SAML not performing a
        // SLO when the glpi Logoff is triggered. It will allow the user to 'relogin' by
        // pressing the correct button.
        $options = [FILTER_FLAG_PATH_REQUIRED];
        if(!empty($var) && !filter_var($var, FILTER_VALIDATE_URL, $options)){
            $error = __('⭕ Invalid Idp SLO URL, use: scheme://host.domain.tld/path/', PLUGIN_NAME);
        }

        return [self::FORMEXPLAIN  => __('Single Logout service endpoint of the IdP. URL Location of the IdP where
                                          SLO Request will be sent.OneLogin PHPSAML only supports
                                          the \'HTTP-redirect\' binding for this endpoint.', PLUGIN_NAME),
                self::FORMTITLE => __('SLO URL', PLUGIN_NAME),
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
            $e = __('⭕ Valid Idp X509 certificate is required! (base64 encoded)', PLUGIN_NAME);
        }

        return [self::FORMEXPLAIN  => __('The Public Base64 encoded x509 certificate used by the IdP. Fingerprinting
                                          can be used, but is not recommended. Fingerprinting requires you to manually
                                          alter the Saml Config array located in ConfigEntity.php and provide the
                                          required configuration options', PLUGIN_NAME),
                self::FORMTITLE => __('X509 CERTIFICATE', PLUGIN_NAME),
                self::EVAL      => ($e) ? self::INVALID : self::VALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($e) ? $e : null,
                self::VALIDATE  => $certificate];
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

        return [self::FORMEXPLAIN => __('Authentication context needs to be satisfied by the IdP in order to allow Saml login. Set
                                         to "none" and OneLogin PHPSAML will not send an AuthContext in the AuthNRequest. Or,
                                         select one or more options using the "control+click" combination.', PLUGIN_NAME),
                self::FORMTITLE => __('REQ AUTHN CONTEXT', PLUGIN_NAME),
                self::EVAL      => ($val) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $val,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($val) ? null : __('⭕ Requested authN context is a required field', PLUGIN_NAME)];
    }

    protected function requested_authn_context_comparison(mixed $var): array  //NOSONAR
    {
        return [self::FORMEXPLAIN => __('AUTHN Comparison attribute value', PLUGIN_NAME),
                self::FORMTITLE => __('AUTHN COMPARISON', PLUGIN_NAME),
                self::EVAL      => ($var) ? self::VALID : self::INVALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::ERRORS    => ($var) ? null : __('⭕ Requested authN context comparison is a required field', PLUGIN_NAME)];
    }

    protected function conf_icon(mixed $var): array                     //NOSONAR
    {
        return [self::FORMEXPLAIN => __('The FontAwesome (https://fontawesome.com/) icon to show on the button on the login page.', PLUGIN_NAME),
                self::FORMTITLE => __('LOGIN ICON', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => (string) $var,
                self::VALIDATOR => __method__,
                self::FIELD     => __function__,
                self::ERRORS    => ($var) ? null : __('⭕ Configuration icon is a required field', PLUGIN_NAME)];
    }

    protected function comment(mixed $var): array                       //NOSONAR
    {
        return [self::FORMEXPLAIN => __('The comments', PLUGIN_NAME),
                self::FORMTITLE => __('COMMENTS', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => (string) $var,
                self::VALIDATOR => __method__,
                self::FIELD     => __function__,];
    }

    // Might cast it into an EPOCH date with invalid values.
    protected function date_creation(mixed $var): array                 //NOSONAR
    {
        return [self::FORMEXPLAIN => __('The date this configuration item was created', PLUGIN_NAME),
                self::FORMTITLE => __('CREATE DATE', PLUGIN_NAME),
                self::EVAL      => self::VALID,
                self::VALUE     => (string) $var,
                self::FIELD     => __function__,
                self::VALIDATOR => __method__,
                self::RICHVALUE => new DateTime($var)];
    }

    // Might cast it into an EPOCH date with invalid values.
    protected function date_mod(mixed $var): array                      //NOSONAR
    {
        return [self::FORMEXPLAIN => __('The date this config was modified', PLUGIN_NAME),
                self::FORMTITLE => __('MODIFICATION DATE', PLUGIN_NAME),
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

        return array_merge([self::FORMEXPLAIN   => __('Is this configuration marked as deleted by GLPI', PLUGIN_NAME),
                            self::FORMTITLE     => __('IS DELETED', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, 'is_deleted'));
    }

    protected function is_active(mixed $var): array                     //NOSONAR
    {
        return array_merge([self::FORMEXPLAIN   => __('Indicates if this configuration activated. Disabled configurations cannot be
                                                       used to login into GLPI and will NOT be shown on the login page.', PLUGIN_NAME),
                            self::FORMTITLE     => __('IS ACTIVE', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::IS_ACTIVE));
    }

    protected function enforce_sso(mixed $var): array                   //NOSONAR 
    {
        return array_merge([self::FORMEXPLAIN   => __('If enabled PHPSAML will replace the default GLPI login screen with a version
                                                       that does not have the default GLPI login options and only allows the user to
                                                       authenticate using the configured SAML2 idps. This setting can be bypassed using
                                                       a bypass URI parameter', PLUGIN_NAME),
                            self::FORMTITLE     => __('ENFORCED', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::ENFORCE_SSO));
    }

    protected function proxied(mixed $var): array
    {
        return array_merge([self::FORMEXPLAIN   => __('Is GLPI positioned behind a proxy that alters the SAML response scheme?', PLUGIN_NAME),
                            self::FORMTITLE     => __('REQUESTS PROXIED', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::PROXIED));
    }

    protected function strict(mixed $var): array
    {
        return array_merge([self::FORMEXPLAIN   => __('If enabled the OneLogin PHPSAML Toolkit will reject unsigned or unencrypted
                                                       messages if it expects them to be signed or encrypted. Also it will reject the
                                                       messages if the SAML standard is not strictly followed: Destination, NameId,
                                                       Conditions are validated too. Strongly adviced in production environments.', PLUGIN_NAME),
                            self::FORMTITLE     => __('STRICT', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::STRICT));
    }

    protected function debug(mixed $var): array
    {
        return array_merge([self::FORMEXPLAIN   => __('If enabled it will enforce OneLogin PHPSAML to print status and error messages.
                                                       be aware that not all message\'s might be captured by GLPISAML and might therefor
                                                       not become visable.'),
                            self::FORMTITLE     => __('PHPSAML DEBUG', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::DEBUG));
    }

    protected function user_jit(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMEXPLAIN     => __('If enabled GLPISAML will create new GLPI users on the fly and assign the properties
                                                         defined in the GLPISAML assignment rules. If disables users that do not have a valid
                                                         GLPI user will not be able to login into GLPI until a user is manually created.'),
                            self::FORMTITLE     => __('JIT USER CREATION', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::USER_JIT));
    }

    protected function security_nameidencrypted(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMEXPLAIN     => __('If enabled the OneLogin PHPSAML toolkit will encrypt the <asmlp:logoutRequest> sent by
                                                         this SP using the provided SP certificate and private key. This option will be toggled
                                                         "off" automatically if no, or no valid SP certificate and key is provided.'),
                            self::FORMTITLE     => __('ENCRYPT NAMEID', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::ENCRYPT_NAMEID));
    }

    protected function security_authnrequestssigned(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMEXPLAIN     => __('If enabled the OneLogin PHPSAML toolkit will sign the <samlp:AuthnRequest> messages
                                                         send by this SP. The IDP should consult the metadata to get the information required
                                                         to validate the signatures.'),
                            self::FORMTITLE     => __('SIGN AUTHN REQUEST', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::SIGN_AUTHN));
    }

    protected function security_logoutrequestsigned(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMEXPLAIN     => __('If enabled the OneLogin PHPSAML toolkit will sign the <samlp:logoutRequest> messages
                                                         send by this SP.'),
                            self::FORMTITLE     => __('SIGN LOGOUT REQUEST', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::SIGN_SLO_REQ));
    }

    protected function security_logoutresponsesigned(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMEXPLAIN     => __('If enabled the OneLogin PHPSAML toolkit will sign the <samlp:logoutResponse> messages
                                                         send by this SP.'),
                            self::FORMTITLE     => __('SIGN LOGOUT RESPONSE', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::SIGN_SLO_RES));
    }

    protected function compress_requests(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMEXPLAIN     => __('If enabled the authentication requests send to the IdP will be compressed by the SP.'),
                            self::FORMTITLE     => __('COMPRESS REQUESTS', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::COMPRESS_REQ));
    }

    protected function compress_responses(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMEXPLAIN     => __('If enabled the SP expects responses send by the IdP to be compressed.'),
                            self::FORMTITLE     => __('COMPRESS RESPONSES', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::COMPRESS_RES));
    }

    protected function validate_xml(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMEXPLAIN   => __('If enabled the SP will validate all received XMLs. In order to validate the XML
                                                        "strict" security setting must be true.'),
                            self::FORMTITLE     => __('VALIDATE XML', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::XML_VALIDATION));
    }

    protected function validate_destination(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMEXPLAIN   => __('If enabled, SAMLResponses with an empty value at its
                                                       Destination attribute will not be rejected for this fact.'),
                            self::FORMTITLE     => __('RELAX DEST VALIDATION', PLUGIN_NAME),
                            self::FIELD         => __function__,
                            self::VALIDATOR     => __method__,],
                            self::handleAsBool($var, ConfigEntity::DEST_VALIDATION));
    }

    protected function lowercase_url_encoding(mixed $var): array //NOSONAR
    {
        return array_merge([self::FORMEXPLAIN   => __('ADFS URL-Encodes SAML data as lowercase, and the OneLogin PHPSAML
                                                       toolkit by default uses uppercase. Enable this setting for ADFS
                                                       compatibility on signature verification'),
                            self::FORMTITLE     => __('LOWER CASE ENCODING', PLUGIN_NAME),
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
