<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

class JFormFieldNbbotaomultiplicador extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = "nbbotaomultiplicador";

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		$excluir = JText::_("COM_NBFORMMAIL_EXCLUIR");
		$class = "delete hide" . (($this->__get("class") != "") ? " " . $this->__get("class") : "");
		$html =
		<<<HTML
			<div>
				<button name='{$this->getAttribute("name")}' class='repeat-fields' data-name='{$this->getAttribute("campos_multiplicar")}'>
					{$this->getAttribute('label')} <span class='fa fa-plus-circle'></span>
				</button>
			</div>
			<div class='{$class}'>
				<button class='delete-field' data-id='0' data-name='{$this->getAttribute("campos_multiplicar")}'>
					{$excluir} <span class='fa fa-minus-circle'></span>
				</button>
			</div>
HTML;
		return $html;
	}

	protected function getLabel() {
		return;
	}
}
