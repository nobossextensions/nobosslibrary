<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.comgi
 * @copyright		Copyright (C) 2019 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("_JEXEC") or die('Restricted access');

jimport('joomla.form.helper');
jimport('noboss.util.curl');
jimport('noboss.util.url');
jimport('noboss.forms.fields.nobosslicense.nobosslicensemodel');

JFormHelper::loadFieldClass('hidden');

class JFormFieldNobosslicense extends JFormFieldHidden
{
    /**
     * The form field type.
     *
     * @var    string
     */
    protected $type = "nobosslicense";

    protected function getLabel(){
        $this->view_license_info = (string) $this->element['view_license_info'];
        $this->view_license_info = $this->view_license_info == '' ? true : (bool)$this->view_license_info;
        // Caso view_license_info seja verdadeiro, esconde a label
        if($this->view_license_info){
            parent::getLabel();
        }
    }
    
    protected function getInput(){
        // Pega documento.
        $doc = JFactory::getDocument();

        $formDataModule = $this->form->getData()->get('module');

        $template = $this->form->getData()->get('template');

        // Pega o nome do componente da url caso esteja nas configurações globais
        $componentNameGlobalConfig = JFactory::getApplication()->input->get->get('component');
        // Pega o nome da extensao 
        if (!empty($componentNameGlobalConfig)) {
            // pega o nome de nas configuraçoes globais
            $this->extensionName = $componentNameGlobalConfig;
        // Caso seja um template 
        } else if (!empty($template)) {
            // Pega a variavel template do form
            $this->extensionName = $this->form->getData()->get('template');
        }
        else if (empty($formDataModule)){
            // pega o nome de componentes
            $formNameArray = explode('.', $this->form->getName());
            $this->extensionName = $formNameArray[0];
        } 
        else {
            // Pega o nome em casos de modulo
            $this->extensionName = $formDataModule;
        }

        // Configura name e id para carregar campo hidden do plano
        $this->name = str_replace($this->element['name'], 'extension_plan', $this->name);
        $this->id = 'extension_plan';

        // Obtem o token e plano da base local
        $tokenPlanArray = NobossModelNobosslicense::getLicenseTokenAndPlan($this->extensionName);

        // Cria propriedades no contexto para uso posterior
        $this->token = array_key_exists("token", $tokenPlanArray) ? $tokenPlanArray['token'] : '';
        $this->plan = array_key_exists("plan", $tokenPlanArray) ? $tokenPlanArray['plan'] : '0';
        $this->inside_support_updates_expiration = '';
        $this->inside_support_technical_expiration = '';
        $this->state = '';
        $this->update_site_id = array_key_exists("update_site_id", $tokenPlanArray) ? $tokenPlanArray['update_site_id'] : '';

        $this->view_license_info = (string) $this->element['view_license_info'];
        $this->modal_display_messages = (string) $this->element['modal_display_messages'];
        $this->modal_display_notice_license = (string) $this->element['modal_display_notice_license'];
        // Cria valores default
        $this->view_license_info = $this->view_license_info == '' ? true : (bool)$this->view_license_info;
        $this->modal_display_messages = $this->modal_display_messages == '' ? true : (bool)$this->modal_display_messages;
        $this->modal_display_notice_license = $this->modal_display_notice_license == '' ? true : (bool)$this->modal_display_notice_license;

        $flags = new StdClass();
        $flags->modal_display_messages = $this->modal_display_messages;
        $flags->modal_display_notice_license = $this->modal_display_notice_license;

        $html = '';
        
        // Token ou id do plano nao definidos na base local
        if(!empty($this->token) || !empty($this->plan)){
            // Busca as informações da licença, mandando o token e o plano atual do usuário
            $this->licenseInfo = $this->getLicenseInfo($this->token, $this->plan, $this->modal_display_messages);
            // Valida se teve sucesso em recuperar as informações do servidor principal
            if(!empty($this->licenseInfo) && $this->licenseInfo->success){
                // Verifica se o token enviado não era inválido
                if(!empty($this->licenseInfo->data) && $this->licenseInfo->data != 'INVALID_TOKEN'){
                    $this->licenseInfoData = json_decode($this->licenseInfo->data);
                    $this->inside_support_updates_expiration = $this->licenseInfoData->inside_support_updates_expiration;
                    $this->inside_support_technical_expiration = $this->licenseInfoData->inside_support_technical_expiration;
                    $this->updates_near_to_expire = $this->licenseInfoData->days_to_expire_support_updates < 7 && $this->licenseInfoData->days_to_expire_support_updates > 0;
                    $this->has_parent_license = !empty($this->licenseInfoData->id_parent_license);
                    $this->state = $this->licenseInfoData->state;

                    // Insere em uma variavel se a url está autorizada ou não
                    $isAuthorizedUrl = $this->checkAuthorizedUrl($this->licenseInfoData->authorized_url, $this->licenseInfoData->token);
                    // Adiciona os parametros para se mandado para o js posteriormente
                    $this->licenseInfoData->view_license_info = $this->view_license_info;
                    $this->licenseInfoData->isAuthorizedUrl = $isAuthorizedUrl;
                    $this->licenseInfoData->authorized_url = $this->licenseInfoData->authorized_url;
                    $this->licenseInfoData->id_local_plan = $this->plan;

                    $this->license_has_errors = !$this->inside_support_updates_expiration || !$this->licenseInfoData->state || !$isAuthorizedUrl;
                    $flags->license_has_errors = $this->license_has_errors;
                    $flags->has_parent_license = $this->has_parent_license;

                    // Salva numa em uma variavel para passar para o js depois
                    $dataValue = $this->licenseInfoData;
                    // Verifica se deve exibir as informações da licença
                    if($this->view_license_info){
                        // Inclui o html da modal de tema, escondida
                        ob_start();
                        require("nobosslicense/nobosslicenselayout.php");
                        $html .= ob_get_clean();
                    }
                    JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UNAUTHORIZED_URL_DESC', $this->licenseInfoData->authorized_url, NoBossUtilUrl::getUrlNbExtensions(), $this->licenseInfoData->id_license, array('script' => true));            
                } else {
                    $dataValue = 'INVALID_TOKEN';
                }
            } else {
                $dataValue = 'CONNECTION_ERROR';
            }
        } else {
            $dataValue = 'TOKEN_OR_PLAN_NOT_FOUND';
        
        }
        // Adiciona as variáveis ao js
        $doc->addScriptOptions('nobosslicense', array(
            'data' => $dataValue,
            'flags' => $flags
        ));

        // Renderiza campo hidden para salvar o id do plano do usuario
        $html .= "<input type='hidden' id='extension_plan' name='extension_plan' value='{$this->plan}'>";
        $html .= "<input type='hidden' id='license_token' name='license_token' value='{$this->token}'>";
        $html .= "<input type='hidden' id='license_update_support_period' name='license_update_support_period' value='{$this->inside_support_updates_expiration}'>";
        $html .= "<input type='hidden' id='license_state' name='license_state' value='{$this->state}'>";
        $html .= "<input type='hidden' id='update_site_id' name='update_site_id' value='{$this->update_site_id}'>"; // Campo para atualizar o plano no banco depois de um update

        // Adiciona as constantes de tradução para o JS
        JText::sprintf('NOBOSS_EXTENSIONS_URL_SITE', NoBossUtilUrl::getUrlNbExtensions(), array('script' => true));
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_CHANGE_LICENSE_URL');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_AUTHORIZED_URL');

        /* TODO: qnd token e/ou id do plano nao eh localizado na base local ou eh invalido, a constante abaixo eh exibida. 
                    * Podemos melhorar para ter um mini formulario que o usuario digite o ID do plano e o token para atualizar na base local.
                    * Para atualizar na base, podemos aproveitar a funcao ja existente:
                        NobossModelNobosslicense::updateUserLocalPlan($updateSiteId, $extra_query)
                    * Para conseguir o dados a serem inseridos, o usuario continuara tendo que entrar em contato para obte-los com a gente
         */
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INVALID_TOKEN_TITLE');
        JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INVALID_TOKEN_DESC', JText::_('NOBOSS_EXTENSIONS_URL_SITE_CONTACT'), array('script' => true));
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UNPUBLISHED_LICENSE_TITLE');
        JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UNPUBLISHED_LICENSE_DESC', JText::_('NOBOSS_EXTENSIONS_URL_SITE_CONTACT'), array('script' => true));
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UNAUTHORIZED_URL_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_ALERT_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_DESC');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_BUTTON_CONFIRM');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_BUTTON_CANCEL');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_UPDATE_ERROR_TITLE');
        JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_UPDATE_ERROR_DESC', NoBossUtilUrl::getUrlNbExtensions(), array('script' => true));
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_UPDATE_SUCESS_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_CHANGE_LICENSE_URL_UPDATE_SUCESS_DESC');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UNAUTHORIZED_URL_BUTTON_KEEP_URL');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UNAUTHORIZED_URL_BUTTON_UPDATE_URL');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_CONFIRM_ACTION_BUTTON_CANCEL');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_CONFIRM_ACTION_BUTTON_CONFIRM');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_AVAILABLE_DOWNLOAD_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_AVAILABLE_DOWNLOAD_DESC');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_CONFIRM_ACTION_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_CONFIRM_ACTION_DESC');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_AVAILABLE_DOWNLOAD_NOW_BUTTON');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_UPGRADE_AVAILABLE_DOWNLOAD_LATER_BUTTON');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INITIAL_CONNECTION_ERROR_TITLE');
        JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INITIAL_CONNECTION_ERROR_DESC', NoBossUtilUrl::getUrlNbExtensions(), array('script' => true));
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INSTALL_NEW_CONFIRM_ACTION_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INSTALL_NEW_CONFIRM_ACTION_DESC');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_INSTALL_NEW_CONFIRM_ACTION_BUTTON_CONFIRM');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRED_LICENSE_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRED_LICENSE_DESC');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRED_LICENSE_CLOSE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRED_LICENSE_RENEW');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRING_LICENSE_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRING_LICENSE_DESC');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRING_LICENSE_CLOSE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_EXPIRING_LICENSE_RENEW');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_ALERT_HAS_ERRORS_MODULE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_ALERT_HAS_ERRORS_MODULE_LINK');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_ALERT_HAS_ERRORS_COMPONENT');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_ALERT_HAS_ERRORS_COMPONENT_LINK');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_ALERT_HAS_ERRORS_GLOBAL_COMPONENT');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_ALERT_HAS_ERRORS_GLOBAL_COMPONENT_LINK');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_PAGE_REFRESH_ALERT_TITLE');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_MODAL_PAGE_REFRESH_ALERT_DESC');
        JText::script('LIB_NOBOSS_FIELD_NOBOSSLICENSE_NOTICE_GET_PAID_VERSION');

        // Carrega os js e css
        $doc->addStylesheet(JURI::base()."../libraries/noboss/forms/fields/assets/stylesheets/css/nobosslicense.min.css");
        $doc->addScript(JURI::base()."../libraries/noboss/forms/fields/assets/js/min/nobosslicense.min.js");

        // retorna o html do campo
        return $html;
    }



    /**
     * Verifica se a url atual é igual a url que está autorizada
     *
     * @param String $authorizedUrl Url autorizada para uso 
     * 
     * @return bool Retorna true caso esteja autorizado e false caso não esteja
     */
    private function checkAuthorizedUrl($authorizedUrl, $extensionToken){
        $siteUrl = str_replace(array('https://www.', 'http://www.', 'https://', 'http://'), '', JURI::root());
        // Verifica se existe alguma url registrada
        if(empty($authorizedUrl)){
            $url = NoBossUtilUrl::getUrlNbExtensions().'/index.php?option=com_nbextensoes&task=externallicenses.changeAuthorizedUrl&format=raw';
            $dataPost = array('token' => $extensionToken, 'newUrl' => base64_encode($siteUrl), 'overwrite' => false);
    
            // Realiza a requisição
            $savedUrl = NobossUtilCurl::request("POST", $url, $dataPost);
            return $savedUrl;
        }

        return $siteUrl == $authorizedUrl;
    }

    /**
     * Busca através de requisição as informações relacionadas a uma determinada licença
     *
     * @param String $extensionToken Token da licença que será buscado
     * @param Boolean $modalDisplayMessages Flag que informa se deve trazar as mensagens personalizadas da licença
     * 
     * @return Object Retorna um objeto com as informações da licença e o array de mensagens
     */
    private function getLicenseInfo($extensionToken, $plan = 0 , $modalDisplayMessages = true){
        $url = NoBossUtilUrl::getUrlNbExtensions().'/index.php?option=com_nbextensoes&task=externallicenses.getLicenseInfo&format=raw';
        $dataPost = array('token' => $extensionToken, 'plan' => $plan, 'modal_display_message' => $modalDisplayMessages);

        // Realiza a requisição
        $tokenInfo = NobossUtilCurl::request("GET", $url, $dataPost, null, 20);

        return $tokenInfo;
    }
}
