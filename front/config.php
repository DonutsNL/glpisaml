<?php
/**
 *  ------------------------------------------------------------------------
 *  PhpSaml2
 *  PhpSaml2 is heavily influenced by the initial work of Derrick Smith's
 *  PhpSaml. This project's intend is to address some structural issues
 *  caused by the gradual development of GLPI. It intends to use more of the
 *  GLPI core objects and php8/composer namespaces.
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

use HTML;
use Plugin;
use Session;
use GlpiPlugin\Glpisaml\Config as samlConfig;

include_once '../../../inc/includes.php';               //NO SONAR - Cannot be included with USE keyword

// Check if plugin is activated...
$plugin = new Plugin();
if(!$plugin->isInstalled(PLUGIN_NAME) ||
   !$plugin->isActivated(PLUGIN_NAME) ){
    Html::displayNotFoundError();
}

if (samlConfig::canCreate()) {
    Html::header(__(PLUGIN_NAME), $_SERVER['PHP_SELF'], 'plugins', samlConfig::class);
    $p = [
    'start'      => 0,      // start with first item (index 0)
    'is_deleted' => 0,      // item is not deleted
    'sort'       => 1,      // sort by name
    'order'      => 'DESC' , // sort direction
    'reset'      => 'reset',// reset search flag
    'criteria'   => [
        [
            'field'      => 80,        // field index in search options
            'searchtype' => 'equals',  // type of search
            'value'      => 0,         // value to search
        ],
    ],
    ];
    print "<pre>";
    print_r(Search::getDatas(samlConfig::class, $p));
    //Html::footer();
 } else {
    //View is not granted.
    Html::displayRightError();
 }