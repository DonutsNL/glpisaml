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

use Plugin;
use GlpiPlugin\Glpisaml\Config;



class ConfigForm extends Config{

    private const TEMPLATE_FILE = '/configForm.html';
    private const GIT_ATOM_URL  = 'https://github.com/donutsnl/GLPISaml/releases.atom';

    public static function getTypeName($nb = 0)
    {
        return __('IdP Configuration', PLUGIN_NAME);
    }

    public static function getIcon() : string
    {
        return 'fas fa-address-book';
    }

    /**
     * Print the auth ldap form
     *
     * @param integer $ID      ID of the item
     * @param array   $options Options
     *     - target for the form
     *
     * @return void|boolean (display) Returns false if there is a rights error.
     */
    public function showForm($ID, array $options = [])
    {
        $this->generateForm($ID);
    }

    private function generateForm(){
        // Read the template file containing the HTML template;
        $path = PLUGIN_GLPISAML_TPLDIR.self::TEMPLATE_FILE;
        if (file_exists($path)) {
            $htmlForm = file_get_contents($path);
        }else{
            $htmlForm = 'empty :(';
        }
        
           


    }

    // For reference use
    public function post_getEmpty()
    {
        $this->fields[self::NAME]               = '';
        $this->fields[self::CONF_DOMAIN]        = '';
        $this->fields[self::CONF_ICON]          = '';
        $this->fields[self::ENFORCE_SSO]        = false;
        $this->fields[self::PROXIED]            = false;
        $this->fields[self::STRICT]             = false;
        $this->fields[self::DEBUG]              = false;
        $this->fields[self::USER_JIT]           = false;
        $this->fields[self::SP_CERTIFICATE]     = '';
        $this->fields[self::SP_KEY]             = '';
        $this->fields[self::SP_NAME_FORMAT]     = '';
        $this->fields[self::IDP_ENTITY_ID]      = '';
        $this->fields[self::IDP_SSO_URL]        = '';
        $this->fields[self::IDP_SLO_URL]        = '';
        $this->fields[self::IDP_CERTIFICATE]    = '';
        $this->fields[self::AUTHN_CONTEXT]      = '';
        $this->fields[self::AUTHN_COMPARE]      = '';
        $this->fields[self::ENCRYPT_NAMEID]     = false;
        $this->fields[self::SIGN_AUTHN]         = false;
        $this->fields[self::SIGN_SLO_REQ]       = false;
        $this->fields[self::SIGN_SLO_RES]       = false;
        $this->fields[self::COMPRESS_REQ]       = false;
        $this->fields[self::COMPRESS_RES]       = false;
        $this->fields[self::XML_VALIDATION]     = true;
        $this->fields[self::DEST_VALIDATION]    = true;
        $this->fields[self::LOWERCASE_URL]      = true;
    }


}