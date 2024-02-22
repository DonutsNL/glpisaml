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
                Html::back();
            } else {
                Session::addMessageAfterRedirect(__('Not allowed or error updating SAML configuration!', PLUGIN_NAME));
                Html::back();
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
            Html::back();
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
     * Generate configuration form based on provided ConfigEntity
     *
     * @param ConfigEntity $configEntity      ID of the item
     * @return string   $htmlForm             raw html form
     */
    private function generateForm(ConfigEntity $configEntity): string
    {
        // Read the template file containing the HTML template;
        if (file_exists(self::HTML_TEMPLATE_FILE)) {
            $htmlForm = file_get_contents(self::HTML_TEMPLATE_FILE);
        }else{
            Session::addMessageAfterRedirect(__("Failed to open setup HTML template got permission?", PLUGIN_NAME));
            Html::back();
        }

        // Populate tplArray ubased on reevaluated ConfigEntity fields
        $tplArray = [];
        foreach($configEntity->getFields() as $configArray){
            $items = $configArray[ConfigItem::EVAL];
            $tplField = strtoupper($configArray[ConfigItem::FIELD]);
            $tplArray = $this->getSelectableFormElements($configArray, $tplArray);

            // Handle evaluation errors
            if(isset($items[ConfigItem::ERRORS]) && !is_null($items[ConfigItem::ERRORS])){
                $tplArray = array_merge($tplArray, ['{{'.$tplField."_ERROR}}"   =>  $items[ConfigItem::ERRORS],]);
            }

            // Fill template elements
            $tplArray = array_merge($tplArray, [
                '{{'.$tplField.'_FIELD}}'   =>  $configArray[ConfigItem::FIELD],
                '{{'.$tplField.'_TITLE}}'   =>  $items[ConfigItem::FORMTITLE],
                '{{'.$tplField.'_LABEL}}'   =>  $items[ConfigItem::FORMLABEL],
                '{{'.$tplField.'_VALUE}}'   =>  $items[ConfigItem::VALUE],
            ]);
        }

        // Get Generic field translation
        $tplArray = array_merge($tplArray, $this->getGenericFormTranslations());

        // Handle global errors
        $emsgs = '';
        if(!$configEntity->isValid()){
            foreach($configEntity->getErrorMessages() as $message){
                $emsgs .= ($message != 'UNDEFINED') ? $message.'<br>' : '';
            }
            $tplArray = array_merge($tplArray, ['{{ERRORS}}'    =>   $emsgs]);
        }
      
        if ($htmlForm = str_replace(array_keys($tplArray), array_values($tplArray), $htmlForm)) {
            // Clean any remaining placeholders like {{ERRORS}}
            $htmlForm = preg_replace('/{{.*}}/', '', $htmlForm);
        }

        return $htmlForm;
    }

    /**
     * Returns the generic fields used in the form template
     * including their translations if available.
     *
     * @return array   - Generic form fields with their translations
     */
    private function getSelectableFormElements(array $configArray, array $tplArray): array
    {
        $items = $configArray[ConfigItem::EVAL];
        $tplField = strtoupper($configArray[ConfigItem::FIELD]);
        // Start with if datatype is a boolean, generate selectable options
        if($configArray[ConfigItem::TYPE] == 'tinyint') {
            $options = [ 1 => __('Yes', PLUGIN_NAME),
                         0 => __('No', PLUGIN_NAME)];
        }
        // If fieldname is NameFormat
        if($items[ConfigItem::FIELD] == ConfigEntity::SP_NAME_FORMAT){
            // Generate the options array
            $options = ['unspecified'  => __('Unspecified', PLUGIN_NAME),
                        'emailAddress' => __('Email Address', PLUGIN_NAME),
                        'transient'    => __('Transient', PLUGIN_NAME),
                        'persistent'   => __('Persistent', PLUGIN_NAME)];
        }
        // If fieldname is AuthN context
        if($items[ConfigItem::FIELD] == ConfigEntity::AUTHN_CONTEXT){
            $options = ['PasswordProtectedTransport'  => __('PasswordProtectedTransport', PLUGIN_NAME),
                        'Password'                    => __('Password', PLUGIN_NAME),
                        'X509'                        => __('X509', PLUGIN_NAME)];
        }
        // If fieldname is AuthN comparison
        if($items[ConfigItem::FIELD] == ConfigEntity::AUTHN_COMPARE){
            $options = ['exact'  => __('Exact', PLUGIN_NAME),
                        'minimum'=> __('Minimum', PLUGIN_NAME),
                        'maximum'=> __('Maximum', PLUGIN_NAME),
                        'better' => __('Better', PLUGIN_NAME)];
        }
        // Generate our selects if required
        if(isset($options) && is_array($options)) {
            $tplArray['{{'.$tplField.'_SELECT}}'] = '';
            foreach ($options as $value => $label) {
                $selected = ($value == $items[ConfigItem::VALUE]) ? 'selected' : '';
                $tplArray['{{'.$tplField.'_SELECT}}'] .= "<option value='$value' $selected>$label</option>";
            }
        }
        return $tplArray;
    }

    /**
     * Returns the generic fields used in the form template
     * including their translations if available.
     *
     * @return array   - Generic form fields with their translations
     */
    private function getGenericFormTranslations(): array
    {
        return [
            '{{SUBMIT}}'                    =>  __('Save', PLUGIN_NAME),
            '{{DELETE}}'                    =>  __('Delete', PLUGIN_NAME),
            '{{CLOSE_FORM}}'                =>  Html::closeForm(false),
            '{{GLPI_ROOTDOC}}'              =>  Plugin::getWebDir(PLUGIN_NAME, true).'/front/config.form.php',
            '{{TITLE}}'                     =>  __('IDP configuration', PLUGIN_NAME),
            '{{HEADER_GENERAL}}'            =>  __('General configuration items', PLUGIN_NAME),
            '{{SECURITY_HEADER}}'           =>  __('Security configuration', PLUGIN_NAME),
            '{{HEADER_PROVIDER}}'           =>  __('Service provider details', PLUGIN_NAME),
            '{{HEADER_PROVIDER_CONFIG}}'    =>  __('Identity provider details', PLUGIN_NAME),
            '{{HEADER_SECURITY}}'           =>  __('Security options', PLUGIN_NAME),
            '{{AVAILABLE}}'                 =>  __('Available', 'phpsaml'),
            '{{SELECTED}}'                  =>  __('Selected', 'phpsaml'),
        ];
    }
}
