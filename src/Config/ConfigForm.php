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
class ConfigForm
{

    private const TEMPLATE_FILE = '/configForm.html';
    private const GIT_ATOM_URL  = 'https://github.com/donutsnl/GLPISaml/releases.atom'; //NOSONAR - WIP

    /**
     * Inits ConfigForm and decides what to do based
     * on whats provded by config.form.php
     *
     * @param array $postData $_POST data from form
     * @return void -
     */
    public function __construct(int $id, array $post)
    {
        if( $id === -1 ){
            // Show form for new entry;
            $options['template'] = (isset($_GET['template']) && ctype_alpha($_GET['template'])) ? $_GET['template'] : 'default';
            print $this->showForm($id, $options);
        } else {
            // Process provided data
            if( isset($post['add']) ){
                // Do add
            } elseif( isset($post['update']) ){
                // Do update
            } elseif( isset($post['delete']) ){
                // Do delete
            } else {
                // Show requested configuration
                if(empty($post)){
                    print $this->showForm($id);
                }else{
                    Session::addMessageAfterRedirect(__('Invalid post header!', PLUGIN_NAME));
                    Html::back();
                }
            }
        }
    }

    /**
     * Add new phpSaml configuration
     *
     * @param array $postData $_POST data from form
     * @return void -
     */
    public function addSamlConfig($postData) : void
    {
        $config = new SamlConfig();
        if($id = $config->add($postData)) {
            Html::redirect(Plugin::getWebDir(PLUGIN_NAME, true)."/front/config.form.php?id=$id");
        } else {
            Session::addMessageAfterRedirect(__('Error: Unable to add new GlpiSaml configuration!', PLUGIN_NAME));
            Html::redirect(Plugin::getWebDir(PLUGIN_NAME, true)."/front/config.php");
        }
    }

    /**
     * Update phpSaml configuration
     *
     * @param int   $id of configuration to update
     * @param array $postData $_POST data from form
     * @return void -
     */
    public function updateSamlConfig($postData) : void
    {
        $config = new SamlConfig();
        if($config->canUpdate()       &&
           $config->update($postData) ){
            Session::addMessageAfterRedirect(__('Configuration updates succesfully', PLUGIN_NAME));
            Html::back();
        } else {
            Session::addMessageAfterRedirect(__('Not allowed or error updating SAML configuration!', PLUGIN_NAME));
            Html::back();
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
           $config->delete($postData) ){
            Session::addMessageAfterRedirect(__('Configuration deleted succesfully', PLUGIN_NAME));
            Html::redirect(Plugin::getWebDir(PLUGIN_NAME, true)."/front/config.php");
        } else {
            Session::addMessageAfterRedirect(__('Not allowed or error deleting SAML configuration!', PLUGIN_NAME));
            Html::back();
        }
    }

    /**
     * Show configuration form
     *
     * @param integer $id      ID the configuration item to show
     * @param array   $options Options
     */
    public function showForm($id, array $options = []) : string
    {
        return $this->generateForm((new ConfigEntity($id, $options)));
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
    private function generateForm(ConfigEntity $configEntity){
        // Read the template file containing the HTML template;
        $path = PLUGIN_GLPISAML_TPLDIR.self::TEMPLATE_FILE;
        if (file_exists($path)) {
            $htmlForm = file_get_contents($path);
        }else{
            $htmlForm = 'empty :(';
        }
        echo "<pre>";
        var_dump($configEntity);
        return $htmlForm;
    }

}
