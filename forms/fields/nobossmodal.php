<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("_JEXEC") or die('Restricted access');

class JFormFieldNobossmodal extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.2
     */
    protected $type = "nobossmodal";
    /**
   * Method to get the field input markup
   */
    protected function getInput(){
        // Obtem valor
        $json = htmlspecialchars($this->value);
        // Obtem texto label
        $label = JText::_($this->getAttribute('label'));
        // Texto para botão de abrir a modal
        $buttonOpenModal = $this->getAttribute('button');
        // Se o texto do botao nao estiver definido, pega o label
        $buttonOpenModal = empty($buttonOpenModal) ? $label : JText::_($buttonOpenModal);

        // Caminho para o arquivo xml
        $xmlPath = $this->getAttribute('formsource');
        $fullXmlPath = JPATH_ROOT."/".$xmlPath;
        
        $attrNotFoundMessage = $this->getAttribute('not_found_message');

        $html = "";
        // Caso não exista o XML, devolve um input vazio
        if(file_exists($fullXmlPath)){
            $html .= "<a data-id='noboss-modal' class='btn'>
                        {$buttonOpenModal}
                      </a>";
        } else if (!empty($attrNotFoundMessage)){
            $html = JText::_($attrNotFoundMessage);
        }
        $html .= "<input type='hidden' data-formsource='{$xmlPath}' name='{$this->name}' data-id='noboss-modal-input-hidden'  data-modal-name='{$this->getAttribute('name')}' value='{$json}'/>";

        $doc = JFactory::getDocument();

        $doc->addScript(JURI::base()."../libraries/noboss/forms/fields/assets/js/min/nobossmodal.min.js");
        $doc->addStylesheet(JURI::base()."../libraries/noboss/forms/fields/assets/stylesheets/css/nobossmodal.min.css");
        // adiciona ao js a versao do joomla
        $doc->addScriptOptions('nobossmodal', array(
            'lowerJVersion' => version_compare(JVERSION, '3.7.3', '<'),
            'higherJVersion' => version_compare(JVERSION, '3.8.0', '>=')
        ));
        // Verifica se já tem o objeto com as constantes de tradução
        if (@!strpos($doc->_script["text/javascript"], ".nobossmodal")) {
            // Adiciona as constantes de trasução
            $doc->addScriptDeclaration(
                '
                if(!translationConstants){
                    var translationConstants = {};  
                }
                translationConstants.nobossmodal = {};
                
                translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CANCEL_LABEL = "'. JText::_('LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CANCEL_LABEL').'";
                translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CANCEL_DESC = "'. JText::_("LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CANCEL_DESC").'";
                translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_RESET_LABEL = "'. JText::_('LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_RESET_LABEL').'";
                translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_RESET_DESC = "'. JText::_("LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_RESET_DESC").'";
                translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONFIRM_CANCEL_BUTTON = "'. JText::_("LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONFIRM_CANCEL_BUTTON").'";
                translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONFIRM_CONFIRM_BUTTON = "'. JText::_("LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONFIRM_CONFIRM_BUTTON").'";
                translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONNECTION_ERROR_TITLE = "'. JText::_("LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONNECTION_ERROR_TITLE").'";
                translationConstants.nobossmodal.LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONNECTION_ERROR_CONTENT = "'. JText::_("LIB_NOBOSS_FIELD_NOBOSSMODAL_MODAL_CONNECTION_ERROR_CONTENT").'";
                '
            );
        }

        $doc->addStyleSheet("https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css");

          return $html;
    }
}
