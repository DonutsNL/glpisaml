<?php
/**
 *  ------------------------------------------------------------------------
 *  GlpiSAML
 *  GlpiSAML is heavily influenced by the initial work of Derrick Smith's
 *  GlpiSAML. This project's intend is to address some structural issues
 *  caused by the gradual development of GLPI. It intends to use more of the
 *  GLPI core objects and php8/composer namespaces.
 *
 *  Copyright (C) 2024 by Chris Gralike
 *  ------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GlpiSAML project.
 * GlpiSAML plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GlpiSAML is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with GlpiSAML. If not, see <http://www.gnu.org/licenses/>.
 *
 * ------------------------------------------------------------------------
 *
 *  @package    PhpSaml
 *  @version    1.0.0
 *  @author     Chris Gralike
 *  @copyright  Copyright (c) 2023 by Chris Gralike
 *  @license    GPLv2+
 *  @see        https://github.com/DonutsNL/GlpiSAML/readme.md
 *  @link       https://github.com/DonutsNL/GlpiSAML
 *  @since      1.0.0
 * ------------------------------------------------------------------------
 **/

use HTML;
use Plugin;
use GlpiPlugin\Glpisaml\Config as samlConfig;

include_once '../../../inc/includes.php';               //NO SONAR - Cannot be included with USE keyword

// Check if plugin is activated...
$plugin = new Plugin();
if($plugin->isInstalled(PLUGIN_NAME) ||
   $plugin->isActivated(PLUGIN_NAME) ){
    if (samlConfig::canCreate()) {
        Html::header(__(PLUGIN_NAME), $_SERVER['PHP_SELF'], 'plugins', samlConfig::class);
        Search::show(samlConfig::class);
        Html::footer();
    }else{
        Html::displayRightError();
    }
    
}else{
    Html::displayNotFoundError();
}
