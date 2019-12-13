<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2019 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

class JFormFieldNobosscsvimporter extends JFormField
{
    /**
	 * The form field type.
	 */
    protected $type = "nobosscsvimporter";
    
    /**
     * Method to get the field input markup
     */
  	protected function getInput(){
        $html = "";

        // Valida se o usuário colocou um valor default no campo pai, algo que não pode e remove o valor
        if($this->value == $this->default){
            $this->value = '';
        }

        $cols = array();

        // Percorre todas as colunas especificadas no xml
        for($i = 0; $i < count($this->element->col); $i++){
            $objCol = new \stdClass;
            // Seta o nome da coluna
            $objCol->alias = (String)$this->element->col[$i]->attributes()->alias;
            // Seta label da coluna no array
            $objCol->label = JText::_($this->element->col[$i]->attributes()->label);
            // Seta nome de funcao JS a executar da coluna no array (caso definido)
            $jsfunction = (String)$this->element->col[$i]->attributes()->jsfunction;
            if (isset($jsfunction) && !empty($jsfunction)){
                $objCol->jsfunction = $jsfunction;
            }
            // Seta opcoes validas para a coluna (caso definido)
            $validvalues = (String)$this->element->col[$i]->attributes()->validvalues;
            if (isset($validvalues) && !empty($validvalues)){
                $objCol->validvalues = $validvalues;
            }
            // Armazena objeto no array
            $cols[] = $objCol;
        }

        $html .= "<button class='csvimportercols-btn-upload'>".JText::_('LIB_NOBOSS_FIELD_NOBOSSCSVIMPORTER_SELECT_FILE_BUTTON')."</button>";

        // Input para upload do arquivo (coloca dados da coluna vindo do xml)
        $html .= "<span class='csvimportercols-input-file'><input type='file' data-csvimporter='file' accept='.csv,.txt' data-csvimportercols='".json_encode($cols)."' /></span>";

        // Input hidden onde sao salvos os valores importados pelo arquivo
        $html .= "<input type='hidden' data-csvimporter='jsonvalue' name='".$this->name."' value='".$this->value."' />";

        $html .= "";

        // Ha valores salvos
        if(!empty($this->value)){
            $displayResult = 'block';
        }
        else{
            $displayResult = 'none';
        }

        // Abre table para apresentacao dos dados importados
        // TODO: permitir via parametros xml definir se a tabela deve ser exibida (aqui e via js) e permitir definir tb a altura máxima e largura máxima da tabela
        $table = "<div style='position: relative; max-height: 450px; overflow: scroll;  display: ".$displayResult.";'>
                        <span style='font-weight: bold; font-size: 14px; padding-bottom: 15px;display: block;'>".JText::_('LIB_NOBOSS_FIELD_NOBOSSCSVIMPORTER_IMPORTED_DATA_TITLE')."</span>
                        <table id='csvimporter-result' class='table table-striped'>";


        // Ha valores salvos
        if (!empty($this->value)){
            // Abre <tr> do cabecalho
            $table .= '<thead><tr>';

            // Percorre todas as colunas especificadas no xml
            foreach($cols as $colHead){
                // Exibe <th> com label da coluna
                $table .= "<th>{$colHead->label}</th>";
            }

            // Fecha <tr> do cabecalho
            $table .= '</tr></thead>';

            // Decodifica json em array de objetos
            $lines = json_decode($this->value);

            // Percorre cada linha
            foreach($lines as $line){
                // Abre <tr> da linha
                $table .= '<tr>';

                // Percorre todas as colunas especificadas no xml
                foreach($cols as $colHead){
                    // Coluna possui valor para linha atual
                    if (isset($line->{$colHead->alias})){
                        // Exibe <td> com label da coluna
                        $table .= "<td>".urldecode($line->{$colHead->alias})."</td>";
                    }
                    // Coluna nao possui valor
                    else{
                        // Exibe td vazio para nao quebrar tabela
                        $table .= "<td></td>";
                    }
                }

                // Fecha </tr> da linha
                $table .= '<tr>';
            }
        }

        // Fecha table para apresentacao dos dados importados
        $table .= "</table></div>";

        $html .= $table;
        
        $doc = JFactory::getDocument();
        //Verifica se já tem uma basenameUrl
        if (!strpos($doc->_script["text/javascript"], "baseNameUrl")) {
            //Adiciona basenameurl
            $doc->addScriptDeclaration('var baseNameUrl =  "'.JUri::root().'";');
        }

        // Define constantes para o JS
        JText::script('LIB_NOBOSS_FIELD_NOBOSSCSVIMPORTER_ERROR_INVALID_EXTENSION_FILE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSCSVIMPORTER_BADLY_FORMATTED_FUNCTION');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSCSVIMPORTER_UNABLE_UPLOAD_DATA_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSCSVIMPORTER_ERROR_READING_FILE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSCSVIMPORTER_INCORRECTLY_FORMATTED_DATA');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSCSVIMPORTER_DATA_BEING_MIGRATED');

        $doc->addScript(JURI::root().'libraries/noboss/forms/fields/assets/js/min/nobosscsvimporter.min.js');
        $doc->addStylesheet(JURI::base()."../libraries/noboss/forms/fields/assets/stylesheets/css/nobosscsvimporter.min.css");

        // Retorna o html do field gerado
        return $html;
    }
}
