<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2019 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("_JEXEC") or die('Restricted access');

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldNobossitemsloadmode extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
    protected $type = "nobossitemsloadmode";

    protected function getInput(){
        
        
        // Inicializa o campo
        $html = "<select id='itemsloadmode' name='{$this->name}' class='required' required aria-required='true'>";
        // Pega todos os options para colocar no array
        $options = $this->getOptions();
        
        // Caso não exista valor, ou seja, é uma criação, seta o primeiro item como selecionado
        $this->value = empty($this->value) ? reset($options)->value : $this->value;
        // Cria o html das options
        foreach($options as $option){
            $selected = $this->value == $option->value;

            $html .= "<option value='{$option->value}' ".( $selected ? 'selected="selected"' : '')." data-subform='{$option->subform}' data-plan='{$option->plan}' data-themes='{$option->themes}'>".JText::_($option->text)."</option>";

        }
        // Fecha o select
        $html .= "</select>";
        
        $html .= "<div style='margin-top:10px; display: none;' class='itemsloadmode_alert_msg' id='itemsloadmode_alert_msg'>".JText::sprintf("LIB_NOBOSS_FIELD_NOBOSSITEMSLOADMODE_NOT_INCLUDED_ALERT", JText::_("NOBOSS_EXTENSIONS_URL_SITE_CONTACT"))."</div>";
                
        // passa constantes para o js
        JText::script('LIB_NOBOSS_FIELD_NOBOSSITEMSLOADMODE_NOT_INCLUDED_LABEL');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSITEMSLOADMODE_NOT_INCLUDED_ALERT_TITLE');
        JText::script('NOBOSS_EXTENSIONS_URL_SITE_CONTACT');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSITEMSLOADMODE_OPT_UNAVAILABLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSITEMSLOADMODE_NO_OPTIONS_ALERT');
        
        // Pega documento.
        $doc = JFactory::getDocument();
        // Carrega os js e css
        // $doc->addStylesheet(JURI::base()."../libraries/noboss/forms/fields/assets/stylesheets/css/nobossitemsloadmode.min.css");
        $doc->addScript(JURI::base()."../libraries/noboss/forms/fields/assets/js/min/nobossitemsloadmode.min.js");
        // Carrega as constantes de tradução
        $this->loadTranslationConstants($doc);

        return $html;
    }

    protected function getOptions(){
        $fieldname = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
        // Percorre as options do
		foreach ($this->element->xpath('option') as $option){
			// Filter requirements
			if ($requires = explode(',', (string) $option['requires'])){

                $value = (string) $option['value'];
                $text  = trim((string) $option) != '' ? trim((string) $option) : $value;

                $disabled = (string) $option['disabled'];
                $disabled = ($disabled == 'true' || $disabled == 'disabled' || $disabled == '1');
                $disabled = $disabled || ($this->readonly && $value != $this->value);

                $checked = (string) $option['checked'];
                $checked = ($checked == 'true' || $checked == 'checked' || $checked == '1');

                $selected = (string) $option['selected'];
                $selected = ($selected == 'true' || $selected == 'selected' || $selected == '1');

                $tmp = array(
                        'value'     => $value,
                        'text'      => JText::alt($text, $fieldname),
                        'disable'   => $disabled,
                        'class'     => (string) $option['class'],
                        'selected'  => ($checked || $selected),
                        'checked'   => ($checked || $selected),
                        'plan'      => $option['plan'],
                        'themes'    => $option['themes'],
                        'subform'   => $option['subform']
                );
                // Add the option object to the result set.
                $options[] = (object) $tmp;
            }
		}
		reset($options);

        return $options;
    }

    /**
     * Inclui um objeto js com as constantes de tradução na página
     *
     * @param JDocument $doc objeto jdocument do joomla
     */
    private function loadTranslationConstants($doc){
        if(empty($doc)){
            $doc = JFactory::getDocument();
        }
        // Verifica se já tem o objeto com as constantes de tradução
		if (@!strpos($doc->_script["text/javascript"], ".{$this->type}")) {
			// Adiciona as constantes de trasução
            $doc->addScriptDeclaration(
                '
                if(!translationConstants){
                    var translationConstants = {};
                }
                translationConstants.'.$this->type.' = {};
                
                translationConstants.'.$this->type.'.LIB_NOBOSS_FIELD_NOBOSSTHEME_LAYOUT_CHOOSED_LABEL = "'. JText::_("LIB_NOBOSS_FIELD_NOBOSSTHEME_LAYOUT_CHOOSED_LABEL").'";
                '
            );
        }
    }
}

