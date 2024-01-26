<?php
/**
 *  ------------------------------------------------------------------------
 *  PhpSaml2
 *  PhpSaml2 is heavily influenced by the initial work of Derrick Smith's
 *  PhpSaml. This project's intend is to address some structural issues
 *  caused by the gradual development of GLPI. It intends to use more of the
 *  GLPI core samlConfigs and php8/composer namespaces.
 *
 *  Copyright (C) 2024 by Chris Gralike
 *  ------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of PhpSaml2 project.
 * PhpSaml2 plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PhpSaml2 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with PhpSaml2. If not, see <http://www.gnu.org/licenses/>.
 *
 * ------------------------------------------------------------------------
 *
 *  @package    PhpSaml2
 *  @version    1.0.0
 *  @author     Chris Gralike
 *  @copyright  Copyright (c) 2023 by Chris Gralike
 *  @license    GPLv2+
 *  @see        https://github.com/DonutsNL/phpSaml2/readme.md
 *  @link       https://github.com/DonutsNL/phpSaml2
 *  @since      1.0.0
 * ------------------------------------------------------------------------
 **/
use Html;
use Plugin;
use GlpiPlugin\Glpisaml\Config\ConfigForm;

include_once '../../../inc/includes.php';                       //NOSONAR - Cannot be included with USE


Html::header(__('PHP SAML', PLUGIN_NAME), $_SERVER['PHP_SELF'], Config::class, "Idp configuration");

$plugin = new Plugin();
if(!$plugin->isInstalled(PLUGIN_NAME) ||
   !$plugin->isActivated(PLUGIN_NAME) ||
   !class_exists(ConfigForm::class)      ){
    Html::displayNotFoundError();
} else {
    $ConfigForm = new ConfigForm();
}

if (isset($_POST['add'])) {
    $ConfigForm->check(-1, CREATE, $_POST);
    $newid = $ConfigForm->add($_POST);
    //Redirect to newly created samlConfig form
    Html::redirect("{$CFG_GLPI['root_doc']}/plugins/front/Config.form.php?id=$newid");
 } else if (isset($_POST['update'])) {
    //Check UPDATE ACL
    $samlConfig->check($_POST['id'], UPDATE);
    //Do samlConfig update
    $samlConfig->update($_POST);
    //Redirect to samlConfig form
    Html::back();
 } else if (isset($_POST['delete'])) {
    //Check DELETE ACL
    $ConfigForm->check($_POST['id'], DELETE);
    //Put samlConfig in dustbin
    $ConfigForm->delete($_POST);
    //Redirect to samlConfigs list
    $ConfigForm->redirectToList();
 } else if (isset($_POST['purge'])) {
    //Check PURGE ACL
    $ConfigForm->check($_POST['id'], PURGE);
    //Do samlConfig purge
    $ConfigForm->delete($_POST, 1);
    //Redirect to samlConfigs list
    Html::redirect("{$CFG_GLPI['root_doc']}/plugins/front/Config.php");
 } else {
    //per default, display samlConfig
    $id = (isset($_GET['id'])) ? $_GET['id'] : 0;
    $withtemplate = (isset($_GET['withtemplate']) ? $_GET['withtemplate'] : 0);
    $ConfigForm->showForm( $id,['withtemplate' => $withtemplate]);
 }
 Html::footer();
