<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

class JFormFieldNobossbinaryfile extends JFormField {
    /**
	 * O tipo do campo do formulário.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = "nobossbinaryfile";

	/**
     * Método que pega o HTML para inserção do campo.
     * @return string Retorna uma string com HTML do campo.
     */
  	protected function getInput(){

        // Flag que verifica se já o foi informado valor para o campo.
        $hasValue = !empty($this->value);

        // Pega o documento.
        $doc = JFactory::getDocument();

        // Verifica se tem valor.
        if($hasValue){

            // Pega valor do campo e realiza decode do JSON.
            $dataFile =  json_decode($this->value);

            // Trata caracteres especiais para inserção no atributo value do campo.
            $this->value = htmlspecialchars($this->value);

            // Configura mime type para o arquivo.
            $attributeMime = "mime='#mimeTypeFile#'";
            $mimeTypeFile = (empty($dataFile->mimeTypeFile)) ? "" : $dataFile->mimeTypeFile;
            $this->mimeTypeFile = str_replace("#mimeTypeFile#", $mimeTypeFile, $attributeMime);

            // Verifica se o mime type do arquivo possui image.
            if(strstr($mimeTypeFile, "image") != false){
                // Configura conteúdo src da tag img do campo.
                $attributeSrc = "data:#mimeTypeFile#;base64,#stringFile#";
                $attributeSrc = str_replace("#mimeTypeFile#", $mimeTypeFile, $attributeSrc );
                $stringFile = (empty($dataFile->stringFile)) ? "" : $dataFile->stringFile;
                $attributeSrc = str_replace("#stringFile#", $stringFile, $attributeSrc);
                $this->src = $attributeSrc;
            }
        }

        // Verifica se não tem registro e configura classe hidden do campo.
        $classHidden = ($hasValue) ? "" : " hidden";

        // Verifica se o parâmetro extension foi especificado no xml do campo.
        if ($this->getAttribute('extension')) {
            // Monta parte da URL conforme parâmetros.
            $params = "&extension=" . $this->getAttribute('extension');
        } else {
            // Verifica se existem os atributos max_width e max_height no xml do campo.
            if ($this->getAttribute('max_width') && $this->getAttribute('max_height')) {
                // Monta parte da URL conforme os parâmetros.
                $params = "&restrict_dimensions=".$this->getAttribute('restrict_dimensions').
                          "&max_width=".$this->getAttribute('max_width').
                          "&max_height=".$this->getAttribute('max_height');
            }
        }

        // Pega o label do campo.
        $translatedLabel =  JText::_($this->getAttribute('label'));

        // Pega o label do botão de upload.
        $translatedLabelUploadButton = JText::_($this->getAttribute('label_upload_button'));

        // Pega o label do link visualização do arquivo.
        $translatedLabelViewFile = JText::_($this->getAttribute('label_view_file'));

        // Pega o label do link para excluir o arquivo.
        $translatedLabelDeleteImage = JText::_($this->getAttribute('label_delete_file'));

        // URL para requisição do campo.
        $url = "index.php?option=com_nobossajax&library=noboss.file.binaryfromfile&method=getBinaryFromFile&format=json".$params;

        // Carrega bilbioteca de leitura de arquivos binários da No Boss.
        JLoader::register('NobossBinaryfromfile', JPATH_LIBRARIES . "/noboss/file/binaryfromfile.php");

        // Cria um objeto com a classe que trata leitura binária.
        $nobossfilebinaryfile = new NobossBinaryfromfile();

        // Pega parâmetros do contexto do campo.
        $params = $nobossfilebinaryfile->getParams($this->getAttribute('extension'));

        $paramFileExtensionsGranted = $params->get("file_upload_extensions_granted");
        // Verifica se o valor do parâmetro de extensões permitidas é vazio e atribui um valor default.
        $extensionGranted = (empty($paramFileExtensionsGranted)) ? "*" : implode(",", $paramFileExtensionsGranted);
        // Verifica se o valor do parâmetro de limite de tamanho de arquivo é vazio
        $sizeLimitUploadFileInBytes = $params->get("size_limit_upload_file");
        $attributeMaxFileSize = (empty($sizeLimitUploadFileInBytes)) ? "" : " data-max-size=" . $sizeLimitUploadFileInBytes;

        // Cria objeto para armazenar informações de contexto do campo.
        $dataParamsField = new stdClass();
        $dataParamsField->msg_error_max_file_size = JText::_($this->getAttribute("msg_error_max_file_size"));
        $dataParamsField->msg_error_extension_file_granted = JText::_($this->getAttribute("msg_error_extension_file_granted"));

        // Transforma os dados para json.
        $jsonDataParamsField = json_encode($dataParamsField);

        // Trata caracteres especiais para inserção no atributo value do campo.
        $valueDataParamsField = htmlspecialchars($jsonDataParamsField);

        // Monta o HTML do campo.
        $html =
            <<<HTML
            <div class="file-upload-context" id="{$this->getAttribute('id')}">
                <div class="file-upload upload-button">
HTML;

        // Pega contexto da aplicação joomla.
        $app = JFactory::getApplication();

        // Verifica se o contexto onde o campo foi chamado não é uma área administrativa.
        if(!$app->isAdmin()){
            // Carrega estilo CSS para o campo na área de site .
            $doc->addStyleSheet(JURI::root().'libraries/noboss/forms/fields/assets/stylesheets/css/'.$this->type.'.min.css');

            // Cria elemento com botão de upload.
            $html .=
                <<<HTML
                    <span>{$translatedLabelUploadButton}</span>
HTML;
        }

        $html .=
            <<<HTML
                    <input name="upload_{$this->getAttribute('name')}" accept="{$extensionGranted}"{$attributeMaxFileSize} class="upload {$this->getAttribute('class')}" type="file" aria-label="{$translatedLabel}" data-id="upload-binary" data-params-field="{$valueDataParamsField}" data-url="{$url}">
                    <input type="hidden" name="{$this->getAttribute('name')}" value="{$this->value}" {$this->mimeTypeFile} data-id="upload-binary-hidden">
                </div>
                <span data-id="file-options" class="options-file{$classHidden}">
                    <span class="icon-arrow-right-3"></span><a href="#" class="simple-link tooltip-trigger--active" data-id="seeFile">$translatedLabelViewFile</a>
                    <span class="icon-arrow-right-3"></span><a href="#" class="simple-link" data-id="deleteFile">$translatedLabelDeleteImage</a>
                </span>
                <div class="tooltip-image hidden" style="max-width: 50%;">
                    <img name="{$this->getAttribute('name')}" class="thumb" src="{$this->src}" data-id="upload-binary-img">
                </div>
            </div>
HTML;
            // Carrega javascript para funcionamento do campo
            $doc->addScript(JURI::root().'libraries/noboss/forms/fields/assets/js/min/'.$this->type.'.min.js');

            return $html;
        }
}
