<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('number');

class JFormFieldNobossorderrepeatable extends JFormFieldNumber
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = "nobossorderrepeatable";

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput()
	{
		$version = new JVersion();
		//Verifica se a versão é maior que 3.7
		if ($version->RELEASE >=  '3.7') {
			return;
		}
		return parent::getInput();
	}

	protected function getLabel()
	{
		$version = new JVersion();
		//Verifica se a versão é maior que 3.7
		if ($version->RELEASE >=  '3.7') {
			$this->description ='';
			return;
		}
		return parent::getLabel();
	}
}
