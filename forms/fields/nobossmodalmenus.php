<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2019 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

// Carrega arquivo do field original do Joomla que eh estendido
require_once JPATH_ADMINISTRATOR.'/components/com_menus/models/fields/modal/menu.php';

class JFormFieldNobossmodalmenus extends JFormFieldModal_Menu {

    protected $type = "nobossmodalmenus";
}
