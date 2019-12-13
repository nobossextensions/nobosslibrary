<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

use Joomla\CMS\Form\Field\EditorField;

require_once 'nobosseditor/src/NobossEditor.php';

// verifica versao do joomla no site atual para saber como estender o Editor do Joomla, pois antes da 3.8 a classe Editor fica em local diferente
if(version_compare(JVERSION, '3.8.0', '>=')){

    class JFormFieldNobosseditor extends EditorField{
        /**
         * The form field type.
         *
         * @var    string
         */
        public $type = "nobosseditor";

        /**
         * Method to get the field input markup for the editor area
         *
         * @return  string  The field input markup.
         *
         */
        protected function getInput(){
            $doc = JFactory::getDocument();

            // Adiciona arquivo JS
            $doc->addScript(JURI::base()."../libraries/noboss/forms/fields/assets/js/min/nobosseditor.min.js");
            
            // Lista de editores aprovados / compativeis
            $editorsAppoved = array('tinymce', 'codemirror', 'none', 'jce');

            // Se o tipo de editor nao foi especificado, seta o editor default que eh o tinymce
            $editorType = is_null($this->element['editor_type']) ? 'tinymce' : (string) $this->element['editor_type'];

            // Busca pelo editor setado no xml
            $editorPlugin = JPluginHelper::getPlugin('editors', $editorType);

            // Editor nao localizado ou nao publicado
            if(empty($editorPlugin)){
                // Percorre editores publicados para ver qual esta disponível
                foreach ($editorsAppoved as $editorPublished){
                    // Busca pelo editor aprovado para ver se
                    if ($editorType = JPluginHelper::getPlugin('editors', $editorPublished)){
                        break;
                    }
                }

               // JFactory::getLanguage()->load('lib_noboss', JPATH_SITE.'libraries/noboss/');

                // Nenhum dos editores autorizados esta disponivel: exibe alerta na tela para usuario
                if (empty($editorType)){
                    return "<div class='alert alert-notice'>
                                <button type='button' class='close' data-dismiss='alert'>×</button>
                                <span>None of the extension-compatible publishers are enabled on the site. Install or enable the plugin from one of the editors listed below: TinyMCE, CodeMirror EditorNone</span>
                            </div>";
                }
            }

            // Instancia um objeto NobossEditor
            $editor = NobossEditor::getInstance($editorType);
        
            // Armazena se o campo eh apenas de leitura ou desabilitado
            $readonly = $this->readonly || $this->disabled;
            
            // Se foi passado opcoes para exibir, prioriza esses parametros e seta eles na toolbar
            if(!empty($this->element['toolbar_show'])){
                $toolbar = explode(',', $this->element['toolbar_show']);
            }elseif(empty($this->element['toolbar_show']) && !empty($this->element['toolbar_hide'])){
                // Se nao foi passada nenhuma opcao para exibir e foi passada para esconder, filtra as opcoes e seta elas na toolbar
                $toolbar = self::filterOptions(
                    explode(',', $this->element['toolbar_hide'])
                );
            }else{
                // Se nao foi passado valor para nenhum dos dois parametros, seta todas as opcoes na toolbar
                $toolbar = self::filterOptions();
            }
            
            // Armazena a altura do editor
            $editorHeight = $this->height;

            // Armazena a largura do editor, ja adicionando a unidade de medida que eh necessaria
            $editorWidth = $this->width;

            // Array com parametros do editor
            $params = array(
                'syntax' => (string) $this->element['syntax'],
                'readonly' => $readonly,
                'toolbar' => $toolbar,
                'editor_height' => $editorHeight
            );
            
            // Construção do html da area do editor informando a largura
            $html = "<div style='max-width: {$editorWidth};' class='nb-editor'>";

            // Concatena o retorno do metodo display no html que vai ser renderizado
            $html .= $editor->display(
                $this->name,
                htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8'),
                $this->width,
                $this->height,
                $this->columns,
                $this->rows,
                $this->buttons ? (is_array($this->buttons) ? array_merge($this->buttons, $this->hide) : $this->hide) : false,
                $this->id,
                $this->asset,
                $this->form->getValue($this->authorField),
                $params
            );

            // Fecha o html do editor
            $html .= "</div>";

            // Renderiza o editor
            return $html;
        }
        
        /**
         * Metodo para filtrar quais opcoes devem ser exibidas no editor
         *
         * @param 	array 	$optionsToHide Array com opcoes que não devem ser exibidas
         *
         * @return  array	Retorna array com as opcoes que devem ser exibidas
         */
        private function filterOptions($optionsToHide = array()){
            
            // Array default de opcoes do editor
            $defaultOptions = array(
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'alignleft', 'aligncenter', 'alignright', 'alignjustify', '|',
                'styleselect', '|',
                'formatselect', 'fontselect', 'fontsizeselect', '|',
                'searchreplace', '|',
                'bullist', 'numlist', '|',
                'outdent', 'indent', '|',
                'undo', 'redo', '|',
                'link', 'unlink', 'anchor', 'image', '|',
                'code', '|',
                'forecolor', 'backcolor', '|',
                'fullscreen', '|',
                'table', '|',
                'subscript', 'superscript', '|',
                'charmap', 'emoticons', 'media', 'hr', 'ltr', 'rtl', '|',
                'cut', 'copy', 'paste', 'pastetext', '|',
                'visualchars', 'visualblocks', 'nonbreaking', 'blockquote', 'template', '|',
                'print', 'preview', 'codesample', 'insertdatetime', 'removeformat',
            );

            // Se nao foi passado nenhuma opcao para esconder, retorna array default
            if(!$optionsToHide){
                return $defaultOptions;
            }

            // Caso contrario, retorna as opcoes que devem ser exibidas
            return array_diff($defaultOptions, $optionsToHide);
        }
    }
    

}else{
    require JPATH_LIBRARIES.'/cms/form/field/editor.php';

    class JFormFieldNobosseditor extends JFormFieldEditor
    {
        /**
         * The form field type.
         *
         * @var    string
         */
        public $type = "nobosseditor";

        /**
         * Method to get the field input markup for the editor area
         *
         * @return  string  The field input markup.
         *
         */
        protected function getInput(){
            $doc = JFactory::getDocument();

            // Adiciona arquivo JS
            $doc->addScript(JURI::base()."../libraries/noboss/forms/fields/assets/js/min/nobosseditor.min.js");

            // Se o tipo de editor nao foi especificado, seta o editor default que eh o tinymce
            $editorType = is_null($this->element['editor_type']) ? 'tinymce' : (string) $this->element['editor_type'];

            $editorPlugin = JPluginHelper::getPlugin('editors', $editorType);

            // Garante que o tinymce tente ser carregado caso o editor desejado não estiver habilitado
            if(empty($editorPlugin)){
                $editorType = 'tinymce';
            }
            
            // Instancia um objeto NobossEditor
            $editor = NobossEditor::getInstance($editorType);
        
            // Armazena se o campo eh apenas de leitura ou desabilitado
            $readonly = $this->readonly || $this->disabled;
            
            // Se foi passado opcoes para exibir, prioriza esses parametros e seta eles na toolbar
            if(!empty($this->element['toolbar_show'])){
                $toolbar = explode(',', $this->element['toolbar_show']);
            }elseif(empty($this->element['toolbar_show']) && !empty($this->element['toolbar_hide'])){
                // Se nao foi passada nenhuma opcao para exibir e foi passada para esconder, filtra as opcoes e seta elas na toolbar
                $toolbar = self::filterOptions(
                    explode(',', $this->element['toolbar_hide'])
                );
            }else{
                // Se nao foi passado valor para nenhum dos dois parametros, seta todas as opcoes na toolbar
                $toolbar = self::filterOptions();
            }
            
            // Armazena a altura do editor
            $editorHeight = $this->height;

            // Armazena a largura do editor, ja adicionando a unidade de medida que eh necessaria
            $editorWidth = $this->width;

            // Array com parametros do editor
            $params = array(
                'syntax' => (string) $this->element['syntax'],
                'readonly' => $readonly,
                'toolbar' => $toolbar,
                'editor_height' => $editorHeight
            );
            
            // Construção do html da area do editor informando a largura
            $html = "<div style='max-width: {$editorWidth}; class='nb-editor'>";

            // Concatena o retorno do metodo display no html que vai ser renderizado
            $html .= $editor->display(
                $this->name,
                htmlspecialchars($this->value, ENT_COMPAT, 'UTF-8'),
                $this->width,
                $this->height,
                $this->columns,
                $this->rows,
                $this->buttons ? (is_array($this->buttons) ? array_merge($this->buttons, $this->hide) : $this->hide) : false,
                $this->id,
                $this->asset,
                $this->form->getValue($this->authorField),
                $params
            );

            // Fecha o html do editor
            $html .= "</div>";

            // Renderiza o editor
            return $html;
        }
        
        /**
         * Metodo para filtrar quais opcoes devem ser exibidas no editor
         *
         * @param 	array 	$optionsToHide Array com opcoes que não devem ser exibidas
         *
         * @return  array	Retorna array com as opcoes que devem ser exibidas
         */
        private function filterOptions($optionsToHide = array()){
            
            // Array default de opcoes do editor
            $defaultOptions = array(
                'bold', 'italic', 'underline', 'strikethrough', '|',
                'alignleft', 'aligncenter', 'alignright', 'alignjustify', '|',
                'styleselect', '|',
                'formatselect', 'fontselect', 'fontsizeselect', '|',
                'searchreplace', '|',
                'bullist', 'numlist', '|',
                'outdent', 'indent', '|',
                'undo', 'redo', '|',
                'link', 'unlink', 'anchor', 'image', '|',
                'code', '|',
                'forecolor', 'backcolor', '|',
                'fullscreen', '|',
                'table', '|',
                'subscript', 'superscript', '|',
                'charmap', 'emoticons', 'media', 'hr', 'ltr', 'rtl', '|',
                'cut', 'copy', 'paste', 'pastetext', '|',
                'visualchars', 'visualblocks', 'nonbreaking', 'blockquote', 'template', '|',
                'print', 'preview', 'codesample', 'insertdatetime', 'removeformat',
            );

            // Se nao foi passado nenhuma opcao para esconder, retorna array default
            if(!$optionsToHide){
                return $defaultOptions;
            }

            // Caso contrario, retorna as opcoes que devem ser exibidas
            return array_diff($defaultOptions, $optionsToHide);
        }
    }
    

}

