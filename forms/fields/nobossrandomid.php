<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('JPATH_PLATFORM') or die;

// jimport('joomla.form.formfield');
// JFormHelper::loadFieldClass('hidden');

class JFormFieldNobossrandomid extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'nobossrandomid';

    protected $layout = 'joomla.form.field.hidden';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
    protected function getInput() {
		$valueGet = $this->__get('value');

		// Verifica se deve gerar um novo id
		$value = empty($valueGet) ? uniqid() : $valueGet;
		// Seta o valor
		$this->__set('value', $value);

        $html =  "<input type='hidden' 
                        name='{$this->name}'
                        id='{$this->id}'
                        value='{$value}'
                    />";


		return $html;
    }
    protected function getLabel(){
        return;
    }
}
