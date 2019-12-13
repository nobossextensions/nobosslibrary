<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("_JEXEC") or die('Restricted access');

jimport('noboss.util.url');

class JFormFieldNobosstheme extends JFormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
    protected $type = "nobosstheme";
    
    private $rawExtensionName;
	/**
   * Method to get the field input markup
   */
  	protected function getInput(){
        
        // Obtem valor
        $jsonValue = json_decode(htmlspecialchars_decode($this->value));

        // Caso esteja vazio, cria um objeto para impedir erros
        if(empty($jsonValue)){
            $jsonValue = new stdClass();
            $jsonValue->theme = '';
            $jsonValue->sample = new stdClass();
            $jsonValue->sample->id = '';
            $jsonValue->sample->img = '';
        }
		// Obtem texto label
        $label = JText::_($this->getAttribute('label'));
        
        // Texto para botão de abrir a modal
        $buttonOpenModal = $this->getAttribute('button');
        // Se o texto do botao nao estiver definido, pega padrao da constante de traducao
        $buttonOpenModal = empty($buttonOpenModal) ? JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_BUTTON') : JText::_($buttonOpenModal);
        
        
        // Nome do modulo sem o prefixo
        if(!$this->rawExtensionName = str_replace('mod_noboss', '', $this->form->getData()->get('module'))){
            // Nome da extensao foi localizada no xml
            if (!empty($this->element['ext_name'])){
                // Define o nome da extensoa a partir do elemento no xml
                $this->rawExtensionName = $this->element['ext_name'];
            }
            else{
                $formNameArray = explode('.', substr($this->form->getName(), 10));
                $this->rawExtensionName = $formNameArray[0];
            }
        }

        // Pega documento.
        $doc = JFactory::getDocument();
        
        // adiciona ao js o nome da extensao e a versao do joomla
        $doc->addScriptOptions('nobosstheme', array(
            'extName' => $this->rawExtensionName,
            'lowerJVersion' => version_compare(JVERSION, '3.7.3', '<'),
            'langCode' => JFactory::getLanguage()->get('tag')
        ));

        // guarda os subforms que devem ser substituidos alem dos de itens
        $subforms = array_map('trim', explode(",", $this->element['subforms']));
        
        // guarda as modais que devem ser substituidas alem das de area externa e itens
        $modals = array_map('trim', explode(",", $this->element['modals']));

        // guarda as fields que devem ser substituidas
        $fields = array_map('trim', explode(",", $this->element['fields']));

        // verifica se foi especificado algum subform
        if($subforms){
            // transforma em json
            $subforms = json_encode($subforms);
            // Adiciona ao js
            $doc->addScriptOptions('nobosstheme', array(
                'subforms' => $subforms
            ));
        }

        // verifica se foi especificado modais adicionais
        if($modals){
            // transforma em json
            $modals = json_encode($modals);
            // Adiciona ao js
            $doc->addScriptOptions('nobosstheme', array(
                'modals' => $modals
            ));
        }

        // verifica se foi especificado fields
        if($fields){
            // transforma em json
            $fields = json_encode($fields);
            // Adiciona ao js
            $doc->addScriptOptions('nobosstheme', array(
                'fields' => $fields
            ));
        }
                
        // Cria o html que será jogado como o campo
		// $html =  "<a data-id='noboss-theme-button' class='btn'>{$buttonOpenModal}</a>";
        $html = "<span class='input-append'>
                    <input type='text' required='required' value='{$jsonValue->theme}' data-id='noboss-theme-selected' readonly='readonly' class='input-medium'>
                    <a role='button' class='btn btn-primary' data-id='noboss-theme-button'>
                        <span class='icon-list icon-white'></span>
                        {$buttonOpenModal}
                    </a>
                </span>";
 
        $html .= "<input type='hidden' data-id='theme-modal-input' data-language='".JFactory::getLanguage()->getTag()."' data-load-sample-data='{$this->getAttribute('loadsampledata', '1')}' data-modal-prefix='{$this->getAttribute('modalsprefix')}' name='{$this->name}' value='{$jsonValue->theme}' data-value='{$this->value}'/>";
        
        // Inclui o html da modal de tema, escondida
        ob_start();
        require("nobosstheme/nobossthemelayout.php");
        $html .= ob_get_clean();

        // Adiciona a constante para o js
        JText::sprintf('NOBOSS_EXTENSIONS_URL_SITE', NoBossUtilUrl::getUrlNbExtensions(), array('script' => true));
        JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TOKEN_SUPPORT_UPDATES_EXPIRATED', NoBossUtilUrl::getUrlNbExtensions(), array('script' => true));
        JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TOKEN_PLAN_NOT_INLCUDED', NoBossUtilUrl::getUrlNbExtensions(), array('script' => true));
        JText::script('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TOKEN_NOT_VALID');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_UNREACHABLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_JSON_PARSE_LOCAL');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_CANCEL_BUTTON');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_CONFIRM_BUTTON');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_RESET_VALUES_LABEL');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_RESET_VALUES_DESC');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_UNAVAILABLE_SAMPLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSTHEME_MODAL_NBSERVER_CONNECTION_ERROR');
        
        // Carrega os js e css
        $doc->addStylesheet(JURI::base()."../libraries/noboss/forms/fields/assets/stylesheets/css/nobosstheme.min.css");
        $doc->addScript(JURI::base()."../libraries/noboss/forms/fields/assets/js/min/nobosstheme.min.js");

        return $html;
    }
    
    /**
     * Retorna um array com todos os itens cadastrados como themes no xml
     *
     * @return void
     */
    protected function getThemes(){
        $fieldname = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $this->fieldname);
		$themes   = array();

        // Percorre as options do 
		foreach ($this->element->xpath('theme') as $option){
            $requires = (string) $option['requires'];

			// Filter requirements
			if ($requires = explode(',', $requires)){
                $value = (string) $option['value'];
                $text  = trim((string) $option) != '' ? trim((string) $option) : $value;

                // Pega o numero de colunas qeu a listagem deve ter
                $columns = empty($option['columns']) ? '1' : $option['columns'];

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
                        'columns'   => $columns,
                        'disable'   => $disabled,
                        'class'     => (string) $option['class'],
                        'selected'  => ($checked || $selected),
                        'checked'   => ($checked || $selected),
                        'plan'      => (array) $option['plan']
                );
                
                // Cria um objeto de exemplo default 
                $basicSample = new stdClass();
                $basicSample->title = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_EXAMPLE_BASIC');
                $basicSample->id = "demo_{$this->rawExtensionName}_{$value}_default";
                // Cria o array com os objetos de exemplo
                $tmp['samples'] = array(0 => $basicSample);

                // Add the option object to the result set.
                $themes[] = (object) $tmp;
            }
		}
		reset($themes);

        return $themes;
    }
}
