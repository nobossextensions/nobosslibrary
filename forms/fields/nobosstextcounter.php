<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('textarea');

class JFormFieldNobosstextcounter extends JFormFieldTextarea
{
    /**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
    protected $type = "nobosstextcounter";

    /**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
    protected function getInput(){
        $html = parent::getInput();
        $showCharacters = $this->element->attributes()->showcharacters;
        $limit = $this->element->attributes()->limit;
        $autoResizeText = $this->element->attributes()->autoresizetext;

        //verifica se o parametro para mostrar o número de caracteres está setado
        if($showCharacters != null){
            //caso setado, adiciona o texto para cada caso
            if($showCharacters == "remaining"){
                $text = JText::_("LIB_NOBOSS_FIELD_NOBOSSTEXTCOUNTER_REMAINING_CHARACTERS");
            }else if ($showCharacters == "typed"){
                $text = JText::_("LIB_NOBOSS_FIELD_NOBOSSTEXTCOUNTER_TYPED_CHARACTERS");
            //caso seja um valor invalido
            }else{
                $showCharacters = "";
                $text = "";
            }
            //e o valor do parametro no elemento html
            $showCharacters = "data-showcharacters='{$showCharacters}'";
            //classe do contador
            $counterClass = "";
        //caso não, deixa as variaveis vazias
        }else{
            $showCharacters = "";
            $text = "";
            //classe do contador
            $counterClass = "hidden";
        }

        if($autoResizeText == true){
            $autoResizeText = "data-autoresizetext='true'";
        }else{
            $autoResizeText = '';
        }

        //monta o elemento html que mostra a contagem de caracteres
        $html .= "<div class='nobosstextcounter-wrapper' data-limit='{$limit}' {$showCharacters} {$autoResizeText}>";
        $html .= "<span class='{$counterClass}'>{$text}:</span>";
        $html .= "<span class='nobosstextcounter'></span>";
        $html .= "</div>";

        //adiciona os arquivos
        $doc = JFactory::getDocument();
		$doc->addScript(JURI::root()."libraries/noboss/forms/fields/assets/js/min/nobosstextcounter.min.js");
		$doc->addStylesheet(JURI::root()."libraries/noboss/forms/fields/assets/stylesheets/css/nobosstextcounter.min.css");

        // Verifica se já tem o objeto com as constantes de tradução
        if (@!strpos($doc->_script["text/javascript"], ".textcounter")) {
            // Adiciona as constantes de trasução
            $doc->addScriptDeclaration(
                '
                if(!translationConstants){
                    var translationConstants = {};  
                }
                translationConstants.textcounter = {};
                
                translationConstants.textcounter.LIB_NOBOSS_FIELD_NOBOSSTEXTCOUNTER_CHARACTERS_LIMIT_REACHED = "'. JText::_("LIB_NOBOSS_FIELD_NOBOSSTEXTCOUNTER_CHARACTERS_LIMIT_REACHED").'";
                '
            );
        }
        return $html;
    }

}
