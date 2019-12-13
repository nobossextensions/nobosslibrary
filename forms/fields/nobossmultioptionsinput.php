<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2019 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldNobossmultioptionsinput extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = "nobossmultioptionsinput";

	/**
   * Method to get the field input markup
   */
  	protected function getInput(){

		$html = '<select id="' . $this->id . '" name="' . $this->name . '[]" data-id="multi-options-input" multiple="multiple">';

		//Verifica se o valor não está vazio
		if (!empty($this->value)) {
			//Verifica se o campo está no formato JSON, se estiver transforma em array
			if($this->validate_json($this->value)){
				$this->value = json_decode($this->value);
			}
			//Percorre os valores inserindo uma option para cada um deles
			foreach ($this->value as $item) {
				$html .= '<option value="'.$item.'" selected="selected">'.$item.'</option>';
			}
		}
		// Fecha o select
		$html .= '</select>';

		$doc = JFactory::getDocument();
		
        $doc->addScript(JURI::root()."libraries/noboss/forms/fields/assets/js/min/nobossmultioptionsinput.min.js");
        
	  	return $html;
  	}

  	// Faz uma verificação para ver se a string é um json
  	private function validate_json($str=NULL) {
	    if (is_string($str)) {
	        @json_decode($str);
	        return (json_last_error() === JSON_ERROR_NONE);
	    }
	    return false;
	}
}
