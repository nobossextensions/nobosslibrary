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
JFormHelper::loadFieldClass('subform');

class JFormFieldNobosssubform extends JFormFieldSubform
{
    /**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
    protected $type = "nobosssubform";

    protected function getInput(){
        // guarda se o subform eh multiplo
        $isMultiple = ($this->multiple) ? "true" : "false";

        // seta o layout default do nbsubform caso nenhum tenha sido especificado
        if(empty($this->element['layout'])){
            if($isMultiple === "true"){
                $this->layout = "noboss.form.field.subform.collapse";
           }else{
               $this->layout = "noboss.form.field.subform.single";
           }
        }

        // passa constantes para o js
        JText::script('LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONFIRM_CANCEL_BUTTON');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONFIRM_CONFIRM_BUTTON');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSSUBFORM_ALERT_MESSAGE_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSSUBFORM_ALERT_MESSAGE_CONTENT');

        // define o texto do botao de acordo com parametro do xml ou constante da library
        $btnText = $this->element['button_text'] ? $this->element['button_text'] : JText::_('LIB_NOBOSS_FIELD_NOBOSSSUBFORM_RESET_BUTTON_DEFAULT_TEXT');
        //parametro com o identificar do campo que servira de titulo do subform
        $this->identifier = $this->element['identifier'];

        $this->htmlButtons = "";
        $this->resetButton = "";
        
        if($this->layout === 'noboss.form.field.subform.collapse'){
            //parametro com as opcoes do botao para o collapse
            $collapseButtons = $this->element['collapsebuttons'];

            if($this->element['show_reset'] == "false"){
                $showStyle = "display: none";
                $dataShow = "false";
            }else{
                // guarda se o botao deve ser iniciado visivel ou escondido
                $showStyle = "display: inline-block";
                $dataShow = "true";
            }
            
            if($collapseButtons !== "none"){
    
                //abre a tag que envolve os botoes
                $this->htmlButtons .= "<div class='noboss-collapse-actions'>";

                if(!isset($collapseButtons)){
                    $collapseButtons = "grow,shrink";
                }else{
                    $collapseButtons = trim($collapseButtons);
                }

                //se for o botao de exapndir
                if(strpos($collapseButtons, "grow") !== false){
                    //adiciona o html correspondente
                    $this->htmlButtons .= "  <a data-toggle='grow' class='noboss-collapse-button btn' data-id='noboss-collapse-button'><span class='material-icon'>fullscreen</span><span class='noboss-collapse-button__text'>" . JTEXT::_('LIB_NOBOSS_FIELD_NOBOSSSUBFORM_COLLAPSE_BUTTON_EXPAND_TEXT') . "</span></a>";
                }
                //se for o botao de collapse
                if(strpos($collapseButtons, "shrink") !== false){
                    //adiciona o html
                    $this->htmlButtons .= " <a data-toggle='shrink' class='noboss-collapse-button btn' data-id='noboss-collapse-button'><span class='material-icon'>fullscreen_exit</span><span class='noboss-collapse-button__text'>" . JTEXT::_('LIB_NOBOSS_FIELD_NOBOSSSUBFORM_COLLAPSE_BUTTON_COLLAPSE_TEXT') ."</span></a>";
                }
                //fecha a tag que envolve os botoes
                $this->htmlButtons .= "</div>";
            }
    
            // monta html, adicionando o botao de reset
            $this->resetButton .= "<a class='btn btn-reset' data-min='{$this->element['min']}' data-show='{$dataShow}' data-name='{$this->element['name']}' data-multiple='{$isMultiple}' data-id='noboss-subform-reset' style='{$showStyle};'>{$btnText}</a>";
        }
        // se o multiplo nao foi habilitado, forca os parametros a serem multiplos para que seja possivel realizar os eventos de delete e add
        // os botoes sao escondidos via js
        if($isMultiple == "false"){
            $this->multiple = true;
            $this->buttons['remove'] = true;
            $this->buttons['add'] = true;
            $this->min = 1;
            $this->max = 2;
        }
        // concatena com o html da classe pai
        $html = parent::getInput();

        // exibe o subform
        return $html;

    }
}
