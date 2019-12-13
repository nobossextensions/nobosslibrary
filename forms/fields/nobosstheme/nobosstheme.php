<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('_JEXEC') or die;

jimport('noboss.util.url');
jimport('noboss.util.curl');
jimport( 'joomla.form.form' );
jimport( 'joomla.application.component.modelform' );

/**
 * Classe de campo personalizado para exibição de temas.
 */
class NobossNobosstheme {
    /**
     * Carrega uma modal para a escolha de temas e exemplos de um modulo
     */
    public static function loadModuleSample() {
        $app = JFactory::getApplication();
        $post = $app->input->post;
        // pega o idioma vindo da requisicao ajax
        $langCode = $post->get('lang');

        // Instancia objeto de linguagem
        $lang = JFactory::getLanguage();
        // seta o idioma de acordo com o configurado
        $lang->setLanguage($langCode);
        // Carrega arquivo tradução da library no boss
        $lang->load('lib_noboss', JPATH_SITE.'/libraries/noboss');

        // Token da extensão
        $extensionToken = $post->get('token'); 
        // Pega o nome da extensão
        $extension = $post->get('extensionName', '');
        // Pega o nome do tema escolhido
        $model = $post->get('model', 'model1');
        // Pega o modelo da extensão
        $sampleId = $post->get('sampleId', "demo_{$extension}_{$model}_default");
        // Pega o nome dos forms que serao gerados
        $itemsFormName = $post->get('itemsFormName');
        // Pega as modais adicionais que devem ser geradas
        $addModals = $post->get('addModals');
        // Pega nome dos fields que devem ser gerados
        $fieldsNames = $post->get("fieldsNames");
        // Pega a a tag de linguagem atual
        $language = $lang->get('tag');
        // pega o nome do subform 'principal', que deve ser gerado pelo loadmode selecionado
        $mainSubform = $post->getString('loadModeSubform');
        
        $url = NoBossUtilUrl::getUrlNbExtensions().'/index.php?option=com_nbextensoes&task=externalthemes.getSample&format=raw';

        // Cria o objeto que será retornado
        $values = new stdClass();
        $values->success = 0;

        // Configura dados do POST.
        $dataPost = array(
            'token'     => $extensionToken,
            'sampleId'  => $sampleId,
            'model'  => $model,
            'language'  => $language,
            'itemsFormName' => $itemsFormName,
            'addModals' => $addModals,
            'fieldsNames' => $fieldsNames,
            'mainSubform' => $mainSubform
        );

        $dataPost = http_build_query($dataPost);

        $fullResponse = NoBossUtilCurl::request('POST', $url, $dataPost, null, 20, null, null, true);

        // Verifica se código de erro da curl é de "timeout".
        if($fullResponse->data->errorno == 28){
            $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_CONNECTION_TIMEOUT');
            exit(json_encode($values));
        }

        $response = $fullResponse->data->body;

        // exit(json_encode($response));

        // Verifica se a resposta não é falsa, ou seja, não tem internet ou não foi possível se comunicar com o servidor
        if (empty($fullResponse->data) || $response == false || empty($response)){
            $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_NOT_CONNECTION');
            exit(json_encode($values));
        }
        
        // Verifica se não deu erro de exemplo não encontrado
        if (trim($response) == "SAMPLE_NOT_FOUND"){
            $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_NOT_FOUND');
            exit(json_encode($values));
        }
        
        try{
            // Decodifica a resposta do servidor
            $response = json_decode($response, true);
        } catch (Exception $e){
            $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_JSON_PARSE_SERVER');
            exit(json_encode($values));
        }

        // Nenhum conteudo retornado em response
        if (empty($response)){
            $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_NO_RESPONSE_FROM_SERVER');
            exit(json_encode($values));
        }

        // Verifica se o token é invalido ou o periodo de suporte expirou
        if(array_key_exists('valid_token', $response)){
            // Varifica se o token existe
            if($response['valid_token'] == 0){
                $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TOKEN_NOT_VALID');
            // Verifica se o plano inclui o modelo
            } else if ($response['in_plan'] == 0){
                $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TOKEN_PLAN_NOT_INLCUDED');
            // Caso seja custom, verifica se está no periodo do suporte
            } else if ($response['inside_support_updates'] == 0){
                $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TOKEN_SUPPORT_UPDATES_EXPIRATED');
            }
            $values->data = "INVALID_TOKEN";
            exit(json_encode($values));
        }

        if ($values) {
            // Nome da extensao foi definido no xml
            if ($extension){
                // Carrega arquivo tradução da extensao em que a modal está sendo chamada
                $lang->load("mod_noboss{$extension}", JPATH_ROOT."/modules/mod_noboss{$extension}");
            }      

            // guarda a referencia do array de itens de subform
            $items = $response['items'];
            $addFields = $response['fields'];

            // Carrega o xml da extensão
            $xml = simplexml_load_file(JPATH_ROOT."/modules/mod_noboss{$extension}/mod_noboss{$extension}.xml");
            $fields = $xml->config->fields;

            // Caso exista ao menos um field adicional continua
            if(!empty($addFields)){
                foreach ($response['fields'] as $fieldName => $value) {
                    $xmlField = $fields->xpath('//field[@name="'.$fieldName.'"]');
                    $field = $xmlField[0];
                    $newXml = "<form>".$field->asXML()."</form>";

                    try{
                        $form = JForm::getInstance($fieldName, $newXml, array('control' => 'jform[params]'), true);
                        // Adiciona o caminho para os campos personalizados da library
                        $form->addFieldPath(JPATH_SITE.'/libraries/noboss/forms/fields');
                        // verifica se a extensao tem campo personalizado proprio
                        if(is_dir(JPATH_SITE."/modules/mod_noboss{$extension}/fields")){
                            $form->addFieldPath(JPATH_SITE."/modules/mod_noboss{$extension}/fields");
                        }
                    } catch (Exception $e){
                        $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_XML_INVALID');
                        exit(json_encode($values));
                    }

                    // // se tiver valor seta no campo
                    // if(!empty($value)){
                        // Seta os valores do field
                        $form->setValue($fieldName, null, ($addFields[$fieldName]));
                    // }
                    
                    // adiciona na resposta o html do field
                    $response['fields'][$fieldName] = $form->getField($fieldName)->renderField();
                }
            }
            
            // Caso exista ao menos um subform continua
            if(!empty($items)){
                // armazena o numero de subform a serem gerados
                $subformsCount = count($items);

                foreach($response['items'] as $subformName => $val){

                    $xmlSubform = $fields->xpath('//field[@name="'.$subformName.'" and starts-with(@type,"nobosssubform")]');
                    // procura por todos os subforms ou nobosssubforms com o nome da iteracao atual
                    $subform = $xmlSubform[0];             
                    
                    // Monta o xml que será usado pelo getinstance
                    $newXml = "<form>".$subform->asXML()."</form>";
                    try{
                        // Instancia o formulário
                        $form = JForm::getInstance($subformName, $newXml, array('control' => 'jform[params]'), true);
                        // Adiciona o caminho para os campos personalizados da library
                        $form->addFieldPath(JPATH_SITE.'/libraries/noboss/forms/fields');
                        // verifica se a extensao tem campo personalizado proprio
                        if(is_dir(JPATH_SITE."/modules/mod_noboss{$extension}/fields")){
                            $form->addFieldPath(JPATH_SITE."/modules/mod_noboss{$extension}/fields");
                        }
                    } catch (Exception $e){
                        $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_XML_INVALID');
                        exit(json_encode($values));
                    }
                                    
                    // Seta os valores do subform
                    $form->setValue($subformName, null, ($items[$subformName]));
                    
                    // adiciona na resposta o html do subform
                    $response['items'][$subformName] = $form->getField($subformName)->renderField();
                }
            }

            $values->success = 1;
            $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_SUCCESS');
            // Renderiza o subform
            $values->data = $response;
        }
        // Retorna em formato json
        exit(json_encode($values));
    }	
    
    /**
     * Carrega uma modal para a escolha de temas e exemplos de um componente
     */
    public static function loadComponentSample() {
        $app = JFactory::getApplication();
        $post = $app->input->post;
        
        // Instancia objete de linguagem
        $lang = JFactory::getLanguage();
        // Carrega arquivo tradução da library no boss
        $lang->load('lib_noboss', JPATH_SITE.'/libraries/noboss');
        // Token da extensão
        $extensionToken = $post->get('token'); 
        // Pega o nome da extensão
        $extension = $post->get('extensionName', '');
        // Nome da extensao foi definido no xml
        if ($extension){
            // Carrega arquivo tradução da extensao em que a modal está sendo chamada
           $lang->load("com_noboss{$extension}", JPATH_ROOT."/administrator/components/com_noboss{$extension}");
        }
        // Pega o nome do tema escolhido
        $model = $post->get('model', 'model1');
        // Pega o modelo da extensão
        $sampleId = $post->get('sampleId', "demo_{$extension}_{$model}_default");
        // Pega a a tag de linguagem atual
        $language = JFactory::getLanguage()->get('tag');
        $url = NoBossUtilUrl::getUrlNbExtensions().'/index.php?option=com_nbextensoes&task=externalthemes.getSample&format=raw';
        // Cria o objeto que será retornado
        $values = new stdClass();
        $values->success = 0;

        // Configura dados do POST.
        $dataPost = array(
            'token'     => $extensionToken,
            'sampleId'  => $sampleId,
            'model'     => $model,
            'language'  => $language,
            'component' => true
        );

        // Faz uma requisição curl usando a library
        $fullResponse = NoBossUtilCurl::request('POST', $url, $dataPost, null, 20, null, null, true);

        // Verifica se código de erro da curl é de "timeout".
        if($fullResponse->data->errorno == 28){
            $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_CONNECTION_TIMEOUT');
            exit(json_encode($values));
        }

        $response = $fullResponse->data->body;

        // Verifica se a resposta não é falsa, ou seja, não tem internet ou não foi possível se comunicar com o servidor
        if (empty($fullResponse->data) || $response == false || empty($response)){
            $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_NOT_CONNECTION');
            exit(json_encode($values));
        }
        
        try{
            // Decodifica a resposta do servidor
            $response = json_decode($response, true);
        } catch (Exception $e){
            $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_JSON_PARSE_SERVER');
            exit(json_encode($values));
        }

        // Verifica se o token é invalido ou o periodo de suporte expirou
        if(array_key_exists('valid_token', $response)){
            // Varifica se o token existe
            if($response['valid_token'] == 0){
                $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TOKEN_NOT_VALID');
            // Verifica se o plano inclui o modelo
            } else if ($response['in_plan'] == 0){
                $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TOKEN_PLAN_NOT_INLCUDED');
            // Caso seja custom, verifica se está no periodo do suporte
            } else if ($response['inside_support_updates'] == 0){
                $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_TOKEN_SUPPORT_UPDATES_EXPIRATED');
            }
            $values->data = "INVALID_TOKEN";
            exit(json_encode($values));
        }
        

        // Carrega o xml da extensão
        $xml = simplexml_load_file(JPATH_ROOT."/administrator/components/com_noboss{$extension}/models/forms/group.xml");
        
        // Monta xml dos campos que devem ser gerados como dado de exemplo
        $xmlFields = "<form>";
        foreach ($response['fields'] as $key => $value) {
            $field = $xml->xpath('//field[@name="'.$key.'"]');
            $field = $field[0];
            $xmlFields .= $field->asXML();
        }
        $xmlFields .= "</form>";
        
        try{
            // Instancia o formulário
            $form = JForm::getInstance('sample_data', $xmlFields, array('control' => 'jform'), true);
            // Adiciona o caminho para os campos personalizados da library
            $form->addFieldPath(JPATH_SITE.'/libraries/noboss/forms/fields');
            // verifica se a extensao tem campo personalizado proprio
            if(is_dir(JPATH_ROOT."/administrator/components/com_noboss{$extension}/models/fields")){
                $form->addFieldPath(JPATH_ROOT."/administrator/components/com_noboss{$extension}/models/fields");
            }
        } catch (Exception $e){
            $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_ERROR_XML_INVALID');
            exit(json_encode($values));
        }
        // percorre cada campo de dado de exemplo
        foreach ($response['fields'] as $key => $value) {
            // pega o objeto form do campo atual
            $field = $form->getField($key);
            // seta o valor
            $field->setValue($value);
            // renderiza o html desse field na resposta da requisicao
            $response['fields'][$key] = $field->renderField();
        }

        if ($values) {
            
            // seta como sucesso a requisicao
            $values->success = 1;
            // mensagem de sucesso
            $values->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSTHEME_GENERATE_EXAMPLES_SUCCESS');
            // adiciona os json das modais no valor que sera retornado
            $values->data = $response;
        }
        // Retorna em formato json
        exit(json_encode($values));
    }	
}
