<?php
/**
 *  ------------------------------------------------------------------------
 *  Derrick Smith - PHP SAML Plugin
 *  Copyright (C) 2014 by Derrick Smith
 *  ------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of PHP SAML Plugin project.
 *
 * PHP SAML Plugin is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PHP SAML Plugin is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with PHP SAML Plugin. If not, see <http://www.gnu.org/licenses/>.
 *
 * ------------------------------------------------------------------------
 *
 *  @package        PhpSaml - Excludes dropdown form UI
 *  @version        1.3.0
 *  @author         Derrick Smith
 *  @author         Chris Gralike
 *  @copyright      Copyright (c) 2018 by Derrick Smith
 *  @license        GPLv2+
 *  @since          1.3.0
 * ------------------------------------------------------------------------
 **/
use Glpi\Plugin;
use GlpiPlugin\Glpisaml\Exclude;

include '../../../inc/includes.php';                            //NOSONAR - Cant be included with USE.
$dropdown = new Exclude();
include GLPI_ROOT . "/front/dropdown.common.form.php";          //NOSONAR - Cant be included with USE.
