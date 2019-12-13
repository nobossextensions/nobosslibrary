<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2019 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

class JFormFieldNobossmultipleinputs extends JFormField
{
    /**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
    protected $type = "nobossmultipleinputs";
    
    /**
   * Method to get the field input markup
   */
  	protected function getInput(){
        $html = "";

        // Valida se o usuário colocou um valor default no campo pai, algo que não pode e remove o valor
        if($this->value == $this->default){
            $this->value = '';
        }

        // Percorre os campos internos deste campo personalizado
        for($i = 0; $i < count($this->element->nbfield); $i++){
            // Para cada campo abre uma div com estilo inline-block
            $html .= "<div class='nobossmultipleinputs__input'>";
            // Verifica se já existe um valor e seta no value
            $value = '';
            // Unidade do campo
            $unit = JText::_($this->element->attributes()->unit);
            // Se já existe um valor na posição daquele campo
            if(isset($this->value[$i]) && !empty($this->value[$i])) {
                if(!empty($unit) && (substr($this->value[$i], -2) == $unit)){
                    $value = $this->value[$i]; 
                }else{
                    $value = $this->value[$i].$unit; 
                }
            }else{
                // Se o default do elemento não estiver vazio
                if(!empty($this->element->nbfield[$i]->attributes()->default)){
                    $value = JText::_($this->element->nbfield[$i]->attributes()->default);
                // Se o default do elemento estiver vazio e o default geral não estiver vazio 
                } else if(!empty($this->default) && empty($value)){
                    $value = JText::_($this->default);
                // Caso ambos os default estejam vazios
                } else {
                    $value = '0';
                }
            }
            // Verifica se existe alguma label definida, e insere antes do campo
            if(isset($this->element->nbfield->attributes()->label) && !empty($this->element->nbfield->attributes()->label)){
                $html .= "<label>".JText::_($this->element->nbfield[$i]->attributes()->label)."</label>";
            }
            $type = "text";
            // Verifica um tipo definido e seta ele, caso contrário define como text
            if(isset($this->element->nbfield->attributes()->type) && !empty($this->element->nbfield->attributes()->type)){
                $type = $this->element->nbfield->attributes()->type;
            }

            $dateElements = array();

            // Percorre atributos do sub input
            foreach($this->element->nbfield[$i]->attributes() as $indiceAtt => $valueAtt){
                // Atributo inicia com 'data-': adiciona atributo junto ao input a ser carregado
                if (substr($indiceAtt, 0, 5) == 'data-'){
                    $dateElements[] = "{$indiceAtt}='{$valueAtt}'";
                }
                // Item 'checked' definido (campo checkbox)
                if($indiceAtt == 'checked'){
                    $dateElements[] = "{$indiceAtt}='{$valueAtt}'";
                }
            }
     
            // Definida unidade para o campo: sinaliza maskara
            if(isset($unit) && !empty($unit)){
                $dateElements[] = "data-mask='{$unit}'";
            }
          
            $html .= "<input class='nobossmultipleinputs--active-mask' data-id='nobossmultipleinputs--active-mask' ".implode(' ', $dateElements)." type='{$type}' name='{$this->name}[]' value='{$value}' />\n";

            $html .= "</div>";
        }

        
        $doc = JFactory::getDocument();
        //Verifica se já tem uma basenameUrl
        if (!strpos($doc->_script["text/javascript"], "baseNameUrl")) {
            //Adiciona basenameurl
            $doc->addScriptDeclaration('var baseNameUrl =  "'.JUri::root().'";');
        }
        $doc->addScript(JURI::root().'libraries/noboss/forms/fields/assets/js/min/nobossmultipleinputs.min.js');
        $doc->addStylesheet(JURI::base()."../libraries/noboss/forms/fields/assets/stylesheets/css/nobossmultipleinputs.min.css");

        // Retorna o html do field gerado
        return $html;
    }
}
