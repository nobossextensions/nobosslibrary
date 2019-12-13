<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

class JFormFieldNoBossRepeatable extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = "nobossrepeatable";

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{

		$input = JFactory::getApplication()->input;
		$id = $input->get->get("id");

		$load = JText::_('COM_NBFORMMAIL_REPEATABLE_CARREGAR_CAMPOS');
		$add = JText::_('COM_NBFORMMAIL_REPEATABLE_ADICIONAR_CAMPO');

		if($id != null) {
			$html = <<<HTML
			<div class="btn-field-geren">
				<input class="btn btn-success" type="button" name="noboss_loader" id="noboss_loader" value="$load">
			</div>
			<div class="fields"></div>
HTML;
		}
		else {
			$html = <<<HTML
			<div class="btn-field-geren">
				<input class="btn btn-success" type="button" name="noboss_repeater" id="noboss_repeater" value="$add">
			</div>
			<div class="fields"></div>
HTML;
		}

		return $html;
	}
}
