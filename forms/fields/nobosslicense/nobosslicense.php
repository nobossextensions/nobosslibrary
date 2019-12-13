<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('_JEXEC') or die;

jimport('noboss.util.curl');
jimport('noboss.util.url');
jimport('noboss.forms.fields.nobosslicense.nobosslicensemodel');

/**
 * Classe de campo personalizado para gerenciamento de licenças de extensões
 */
class NobossNobosslicense {

    /**
	 * Faz a requisição para o servidor principal para upgrade da extensao
	 */
	public static function upgradeLicensePlan() {
        $app = JFactory::getApplication();
        $post = $app->input->post;
        // Instancia objete de linguagem
        $lang = JFactory::getLanguage();
        // Carrega arquivo tradução da library no boss
        $lang->load('lib_noboss', JPATH_SITE.'/libraries/noboss');
        // Token da extensão
        $token = $post->post->get('token');
        // Id do plano da extensão
        $plan = $post->post->get('plan');
        // Id da coluda de update, usada para atualizar o plano depois de um update com sucesso
        $updateSiteId = $post->get('update_site_id');
        // Monta a url para onde será feita a requisição de validação do token
        $urlTokenValidate = NoBossUtilUrl::getUrlNbExtensions()."/index.php?option=com_nbextensoes&task=externallicenses.validateLicenseUpgrade&format=raw&token={$token}";
        // Dados que serão usados na querystring da requisição get
        $dataPost = array('token' => $token, 'plan' => $plan);
        // Simulacao do navegador
        $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36';
        // Monta a url para onde será feita a requisição de download
        $urlDownload = NoBossUtilUrl::getUrlNbExtensions()."/upgrade/extension";
        // Faz uma requisição para validar o token
        $isValidToken = NobossUtilCurl::request('GET', $urlTokenValidate, $dataPost, null, 20, $userAgent);
        $isValidToken->data = json_decode($isValidToken->data);
        $isValidToken->data = $isValidToken->data->tokenInfo;
        // Verifica se a requisição teve sucesso
        if(!$isValidToken->success){
            exit(json_encode($isValidToken));
        }
        // Verifica se o token é valido
        if (empty($isValidToken->data)) {
            $isValidToken->success = 0;
            $isValidToken->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_INVALID_TOKEN');
            exit(json_encode($isValidToken));
        }
        
        // Faz download do pacote para upgrade
        try{
            $fileName = JInstallerHelper::downloadPackage($urlDownload."?token={$token}&plan={$plan}", uniqid('upgrade_').'.zip');
            if(!$fileName){
                throw new Exception();
            }
        } catch (Exception $e) {
            $isValidToken->success = 0;
            $isValidToken->message = JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_MAIN_ERROR', NoBossUtilUrl::getUrlNbExtensions(), JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_DOWNLOAD_ERROR'));
            exit(json_encode($isValidToken));
        }

        // Unzipa o zip para uma pasta temporária
        try{
            $folderPath = JInstallerHelper::unpack(JFactory::getConfig()->get('tmp_path').'/'.$fileName, true);
            if(!$folderPath){
                throw new Exception();
            }
        } catch (Exception $e) {
            $isValidToken->success = 0;
            $isValidToken->message = JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_MAIN_ERROR', NoBossUtilUrl::getUrlNbExtensions(), JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_UNZIP_ERROR'));
            exit(json_encode($isValidToken));
        }
        // Realiza o update da extensão
        try{
            $tmpInstaller = new JInstaller();
            $updateResult = $tmpInstaller->update($folderPath['extractdir']);
            if(!$updateResult){
                throw new Exception();
            }
        } catch(Exception $e) {
            $isValidToken->success = 0;
            $isValidToken->message = JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_MAIN_ERROR', NoBossUtilUrl::getUrlNbExtensions(), JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_UPDATE_ERROR'));
            exit(json_encode($isValidToken));
        }

        // Monta um array com as parametros para atualizar a coluna extra_query da tabela #__update_sites
        $extra_query = array('token' => $isValidToken->data->token, 'plan' => $isValidToken->data->id_plan);

        // Atualiza o plano no banco de dados e valida para saber se não deu erro
        if(!NobossModelNobosslicense::updateUserLocalPlan($updateSiteId, $extra_query)){
            $isValidToken->success = 0;
            $isValidToken->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_UPDATE_LOCAL_PLAN_ERROR');
            exit(json_encode($isValidToken));
        }

        // Deu tudo certo
        $isValidToken->success = 1;
        $isValidToken->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_SUCCESS');
        exit(json_encode($isValidToken));
    }
    
    /**
	 * Faz a requisição para o servidor principal para instalação de uma nova extensão
	 */
	public static function installNewExtension() {
        $app = JFactory::getApplication();
        $post = $app->input->post;
        // Instancia objete de linguagem
        $lang = JFactory::getLanguage();
        // Carrega arquivo tradução da library no boss
        $lang->load('lib_noboss', JPATH_SITE.'/libraries/noboss');
        // Token da extensão
        $installUrl = $post->post->get('newExtUrl', array());

        $response = new stdClass();

        // Percorre cada url
        foreach ($installUrl as $url) {
            $url = base64_decode($url);
            // Faz download do pacote para upgrade
            try{
                $fileName = JInstallerHelper::downloadPackage($url, uniqid('upgrade_').'.zip');
                if(!$fileName){
                    throw new Exception();
                }
            } catch (Exception $e) {
                $response->success = 0;
                $response->message = JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_MAIN_ERROR', NoBossUtilUrl::getUrlNbExtensions(), JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_DOWNLOAD_ERROR'));
                exit(json_encode($response));
            }

            // Unzipa o zip para uma pasta temporária
            try{
                $folderPath = JInstallerHelper::unpack(JFactory::getConfig()->get('tmp_path').'/'.$fileName, true);
                if(!$folderPath){
                    throw new Exception();
                }
            } catch (Exception $e) {
                $response->success = 0;
                $response->message = JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_MAIN_ERROR', NoBossUtilUrl::getUrlNbExtensions(), JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_UNZIP_ERROR'));
                exit(json_encode($response));
            }
            // Realiza o update da extensão
            try{
                $tmpInstaller = new JInstaller();
                $updateResult = $tmpInstaller->install($folderPath['extractdir']);
                if(!$updateResult){
                    throw new Exception();
                }
            } catch(Exception $e) {
                $response->success = 0;
                $response->message = JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_MAIN_ERROR', NoBossUtilUrl::getUrlNbExtensions(), JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_UPDATE_ERROR'));
                exit(json_encode($response));
            }
        }
        // Deu tudo certo
        $response->success = 1;
        $response->message = JText::_('LIB_NOBOSS_FIELD_NOBOSSLICENSE_UPGRADE_PLAN_SUCCESS');
        exit(json_encode($response));
    }

}
