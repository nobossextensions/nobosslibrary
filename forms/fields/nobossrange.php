<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('range');

/**
 * Form Field class for the Joomla Platform.
 * Provides a horizontal scroll bar to specify a value in a range.
 *
 * @link   http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since  3.2
 */
class JFormFieldNobossrange extends JFormFieldRange
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = 'nobossrange';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
	protected function getInput() {
        // Verifica se o min e max não está null e define valores default
        $this->min = empty($this->min) ? 0 : $this->min;
        $this->max = empty($this->max) ? 100 : $this->max;

        // Adiciona a classe nobossrange no campo range
        $this->class = "nobossrange";

        $html = parent::getInput();
        // Cria um campo number que fica ao lado do range
        $html .= "<input class='nobossrange--input' type='number' name='{$this->fieldname}' value='{$this->value}' min='{$this->min}' max='{$this->max}' step='{$this->step}'/>";

        // Adiciona o js e css do campo personalizado na pagina
        $doc = JFactory::getDocument();
		$doc->addStylesheet(JURI::root()."libraries/noboss/forms/fields/assets/stylesheets/css/nobossrange.min.css");
        $doc->addScript(JURI::root()."libraries/noboss/forms/fields/assets/js/min/nobossrange.min.js");
        
        return $html;
	}
}
