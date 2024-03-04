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

use Html;
use Plugin;
use Session;
use GlpiPlugin\Glpisaml\Config as SamlConfig;


/**
 * Class Handles the Configuration front/config.form.php Form
 */
class ConfigForm        //NOSONAR - Ignore number of methods.
{
    /**
     * Where is the template file located for the configuration form
     */
    private const HTML_TEMPLATE_FILE = PLUGIN_GLPISAML_TPLDIR.'/configForm.html';

    /**
     * Add new phpSaml configuration
     *
     * @param array $postData $_POST data from form
     * @return void -
     */
    public function addSamlConfig($postData) : string
    {
        // Validate the configuration;
        $configEntity = new ConfigEntity(-1, ['template' => 'post', 'postData' => $postData]);
        if($configEntity->isValid()){
            // Remove ID from the postData
            unset($postData[ConfigEntity::ID]);
            $config = new SamlConfig();
            if($id = $config->add($postData)) {
                Html::redirect(Plugin::getWebDir(PLUGIN_NAME, true)."/front/config.form.php?id=$id");
            } else {
                Session::addMessageAfterRedirect(__('Error: Unable to add new GlpiSaml configuration!', PLUGIN_NAME));
                Html::redirect(Plugin::getWebDir(PLUGIN_NAME, true)."/front/config.php");
            }

        }else{
            return $this->generateForm($configEntity);
        }
    }

    /**
     * Update phpSaml configuration
     *
     * @param int   $id of configuration to update
     * @param array $postData $_POST data from form
     * @return void -
     */
    public function updateSamlConfig($postData) : string
    {
        // Validate the configuration;
        $configEntity = new ConfigEntity(-1, ['template' => 'post', 'postData' => $postData]);
        if($configEntity->isValid()){
            $config = new SamlConfig();
            if($config->canUpdate()       &&
               $config->update($postData) ){
                Session::addMessageAfterRedirect(__('Configuration updates succesfully', PLUGIN_NAME));
                Html::redirect(Plugin::getWebDir(PLUGIN_NAME, true).PLUGIN_GLPISAML_CONF_FORM.'?id='.$postData['id']);
            } else {
                Session::addMessageAfterRedirect(__('Not allowed or error updating SAML configuration!', PLUGIN_NAME));
                Html::redirect(Plugin::getWebDir(PLUGIN_NAME, true).PLUGIN_GLPISAML_CONF_FORM.'?id='.$postData['id']);
            }
        }else{
            return $this->generateForm($configEntity);
        }
    }

    /**
     * Add new phpSaml configuration
     *
     * @param array $postData $_POST data from form
     * @return void -
     */
    public function deleteSamlConfig($postData) : void
    {
        $config = new SamlConfig();
        if($config->canPurge()  &&
           $config->delete($postData)){
            Session::addMessageAfterRedirect(__('Configuration deleted succesfully', PLUGIN_NAME));
            Html::redirect(Plugin::getWebDir(PLUGIN_NAME, true)."/front/config.php");
        } else {
            Session::addMessageAfterRedirect(__('Not allowed or error deleting SAML configuration!', PLUGIN_NAME));
            Html::redirect(Plugin::getWebDir(PLUGIN_NAME, true).PLUGIN_GLPISAML_CONF_FORM.'?id='.$postData['id']);
        }
    }

    /**
     * Figures out what form to show
     *
     * @param integer $id       ID the configuration item to show
     * @param array   $options  Options
     */
    public function showForm(int $id, array $options = []): string
    {
        if($id === -1 || $id > 0){
            // Generate form using a template
            return $this->generateForm(new ConfigEntity($id, $options));
        }else{
            // Invalid redirect back to origin
            Session::addMessageAfterRedirect(__('Invalid request, redirecting back', PLUGIN_NAME));
            Html::back();
        }
    }

     /**
     * Figures out if there are errors in one of the tabs and displays a
     * warning sign if an error is found
     *
     * @param array $fields     from ConfigEntity->getFields()
     */
    private function getTabWarnings(array $fields): array
    {
        // What fields are in what tab
        $tabFields = ['general_warning'     => [configEntity::NAME,
                                                configEntity::CONF_DOMAIN,
                                                configEntity::CONF_ICON,
                                                configEntity::COMMENT,
                                                configEntity::IS_ACTIVE,
                                                configEntity::DEBUG],
                      'transit_warning'     => [configEntity::COMPRESS_REQ,
                                                configEntity::COMPRESS_RES,
                                                configEntity::PROXIED,
                                                configEntity::XML_VALIDATION,
                                                configEntity::DEST_VALIDATION,
                                                configEntity::LOWERCASE_URL],
                      'provider_warning'    => [configEntity::SP_CERTIFICATE,
                                                configEntity::SP_KEY,
                                                configEntity::SP_NAME_FORMAT],
                      'idp_warning'         => [configEntity::IDP_ENTITY_ID,
                                                configEntity::IDP_SSO_URL,
                                                configEntity::IDP_SLO_URL,
                                                configEntity::IDP_CERTIFICATE,
                                                configEntity::AUTHN_CONTEXT,
                                                configEntity::AUTHN_COMPARE],
                      'security_warning'    => [configEntity::ENFORCE_SSO,
                                                configEntity::STRICT,
                                                configEntity::USER_JIT,
                                                configEntity::ENCRYPT_NAMEID,
                                                configEntity::SIGN_AUTHN,
                                                configEntity::SIGN_SLO_REQ,
                                                configEntity::SIGN_SLO_RES]];
        // Parse config fields
        foreach($tabFields as $tab => $entityFields){
            foreach($entityFields as $field) {
                if(!empty($fields[$field]['errors'])){
                    $warnings[$tab] = '⚠️';
                }
            }
        }
        // Return warnings if any.
        return (is_array($warnings)) ? $warnings : [];
    }

    private function generateForm(ConfigEntity $configEntity)
    {
        $fields = $configEntity->getFields();
        // Get warnings tabs
        $tplVars  = [];
        $tplVars = array_merge($tplVars, $this->getTabWarnings($fields));
        // Define static field translations
        $tplVars = array_merge($tplVars, [
            'submit'                    =>  __('Save', PLUGIN_NAME),
            'delete'                    =>  __('Delete', PLUGIN_NAME),
            'close_form'                =>  Html::closeForm(false),
            'glpi_rootdoc'              =>  Plugin::getWebDir(PLUGIN_NAME, true).'/front/config.form.php',
            'title'                     =>  __('IDP configuration', PLUGIN_NAME),
            'header_general'            =>  __('General', PLUGIN_NAME),
            'header_security'           =>  __('Security', PLUGIN_NAME),
            'header_provider'           =>  __('Service provider', PLUGIN_NAME),
            'header_idp'                =>  __('Identity provider', PLUGIN_NAME),
            'header_logging'            =>  __('Logging', PLUGIN_NAME),
            'header_transit'            =>  __('Transit', PLUGIN_NAME),
            'available'                 =>  __('Available', 'phpsaml'),
            'selected'                  =>  __('Selected', 'phpsaml'),
            'inputfields'               =>  $fields,
            'inputOptionsBool'          =>  [ 1                             => __('Yes', PLUGIN_NAME),
                                              0                             => __('No', PLUGIN_NAME)],
            'inputOptionsNameFormat'    =>  ['unspecified'                  => __('Unspecified', PLUGIN_NAME),
                                             'emailAddress'                 => __('Email Address', PLUGIN_NAME),
                                             'transient'                    => __('Transient', PLUGIN_NAME),
                                             'persistent'                   => __('Persistent', PLUGIN_NAME)],
            'inputOptionsAuthnContext'  =>  ['PasswordProtectedTransport'   => __('PasswordProtectedTransport', PLUGIN_NAME),
                                             'Password'                     => __('Password', PLUGIN_NAME),
                                             'X509'                         => __('X509', PLUGIN_NAME)],
            'inputOptionsAuthnCompare'  =>  ['exact'                        => __('Exact', PLUGIN_NAME),
                                             'minimum'                      => __('Minimum', PLUGIN_NAME),
                                             'maximum'                      => __('Maximum', PLUGIN_NAME),
                                             'better'                       => __('Better', PLUGIN_NAME)],
        ]);

        //echo "<pre>";
        //var_dump($tplVars);
        
        // Render twig template
        $loader = new \Twig\Loader\FilesystemLoader(PLUGIN_GLPISAML_TPLDIR);
        $twig = new \Twig\Environment($loader);
        $template = $twig->load('configForm.html.twig');
        return $template->render($tplVars);
    }
}
