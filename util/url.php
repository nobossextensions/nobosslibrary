<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2019 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('JPATH_PLATFORM') or die;

class NoBossUtilUrl {
	/**
	*	Função que recebe um item de menu e retorna a URL para ele (ou para um associado) mantendo consistências de idioma
	* analisando qual idioma padrão/idioma atual e idioma do item de menu.
	*
	* @param 	int 		$idMenu 				ID do item de menu para o qual se deseja a URL
	* @param 	boolean		$fullUrl (optional) 	Boolean informando se deve ser retornada a url completa ou apenas o route do item de menu
	* @param 	string		$tagLanguage 			tag de idioma que deve ser informado na url (quando usuario quiser fixar uma)
	*
	* @return 	mixed 		URL para o item de menu (ou associado) passado via parâmetro ou false caso não encontrado
	**/
	public static function getUrlItemMenu($idMenu, $fullUrl = true, $tagLanguage = '') {
		//Pega parâmetros do componente "com_language"
		$paramsLanguage = JComponentHelper::getParams("com_languages");
		//Pega aplicação geral
		$app = JFactory::getApplication();
		//Pega menu
		$menu = $app->getMenu('site');
		//Pega o item de menu referente ao ID passado por parâmetro
		$menuItem = $menu->getItem($idMenu);
		//Se não conseguir pegar o item de menu, retorna falso
		if(!$menuItem) {
			return false;
		}
		//Armazena idioma default do front-end.
		$defaultLanguage = $paramsLanguage->get("site");

		// Informado para a funcao uma tag de linguagem a ser utilizada
		if ($tagLanguage != ''){
			// Foca como languagem corrente a tag informada
			$currentLanguage = $tagLanguage;
		}
		// Utilizar idioma da navegacao no site
		else{
			// Armazena tag do idioma atual
			$currentLanguage = JFactory::getLanguage()->getTag();
		}

		//Pega linguagem do item de menu
		$menuItemLanguage = $menuItem->language;

		// Declara variavel que ira armazenar a url
		$URL = '';

		// Idioma atual for igual o idioma do item de menu
		if($currentLanguage == $menuItemLanguage) {
			// Item de menu nao eh home: armazena alias do menu como url
			if (!$menuItem->home){
				$URL = $menuItem->route;
			}
		} else {
			//Pega itens de menu associados
			$associatedMenuItens = JLanguageAssociations::getAssociations('com_menus', '#__menu', 'com_menus.item', $idMenu, 'id', 'alias', null);
			//Se existir um item de menu associado ao idioma entra no if
			if(isset($associatedMenuItens[$currentLanguage])) {
				//Armazena o objeto que contém o idioma do item de menu associado ao idioma + string "id:alias"
				$currentLanguageMenuItemEquivalent = $associatedMenuItens[$currentLanguage];
				//Armazena o ID do item de menu associado ao idioma quebrando a string "id:alias"
				$currentLanguageMenuItemEquivalentID = explode(":", $currentLanguageMenuItemEquivalent->id);
				$currentLanguageMenuItemEquivalentID = $currentLanguageMenuItemEquivalentID[0];
				//Armazena na variável agora o objeto completo do item de menu associado ao idioma
				$currentLanguageMenuItemEquivalent = $menu->getItem($currentLanguageMenuItemEquivalentID);
				// Item de menu nao eh home: armazena alias do menu como url
				if (!$currentLanguageMenuItemEquivalent->home){
					//Armazena o route do item de menu associado ao idioma 
					$URL = $currentLanguageMenuItemEquivalent->route;
				}
				//Sobrescreve o idioma do menu usando o language do equivalente
				$menuItemLanguage = $currentLanguageMenuItemEquivalent->language;
			} 
			// Nao existe item de menu associado
			else {
				// Item de menu nao eh home: armazena alias do menu como url
				if (!$menuItem->home){
					$URL = $menuItem->route;
				}
			}
		}

		//Verifica se o idioma default é diferente do idioma do menu que está pegando URL para inserir o sef na URL
		if($defaultLanguage != $menuItemLanguage) {

			// Item de menu esta habilitado para todos idiomas: pega idioma atual para colocar na url
			if ($menuItemLanguage == "*"){
				$menuItemLanguage = $currentLanguage;
			}

			//Pega idiomas
			$languages = JLanguageHelper::getLanguages('lang_code');
			$SEF = $languages[$menuItemLanguage]->sef;

			//E depois concatena com a URL montada anteriormente através do route
			$URL = $SEF . "/" . $URL;
		}
		
		// Setado para retornar url completa
		if($fullUrl) {
			// Retira barra do inicio da url armazenada ateh entao, caso possua
			if($URL != '' && ($URL[0] == "/")){
				$URL = substr($URL, 1);
			}

			// Concatena url base do site no inicio
			$URL =  JURI::root() . $URL;
		}

		// Se a url terminar com uma barra no final, retira
		if (substr($URL, -1) == '/'){
			$URL = substr($URL, 0, -1);
		}

		return $URL;
	}

	/**
	*	Função que retorna a url base da plataforma No Boss Extensions para a realizacao de requisicoes
	*
	* 	@return 	String 		Url base da plataforma
	**/
	public static function getUrlNbExtensions(){
		// Objeto com dados do config
		$config = JFactory::getConfig();

        // Obtem a tag do idioma que esta sendo navegado
        $currentLanguage = JFactory::getLanguage()->getTag();
        $languages = JLanguageHelper::getLanguages('lang_code');
        $langSef = $languages[$currentLanguage]->sef;

        // TODO: qnd colocarmos o idioma ingles como default, eh necessario invester a ordem para setar 'pt' somente se usuario estiver em pt

        // Idioma que esta sendo navegado nao eh portugues brasil: forca para colocar idioma ingles na navegacao
        if($langSef != 'pt'){
            $langSef = '/en';
        }
        // Deixa sem tag de idioma para pegar portugues
        else{
            $langSef = '/';
        }

		// Obtem a url definida no config (caso exista)
		$urlNbExtensions = $config->get('url_nb_extensions');

        // Url refinida no config: retorna ela mesmo
		if (isset($urlNbExtensions) && !empty($urlNbExtensions)){
			return $urlNbExtensions.$langSef;
        }

		// Retorna url do ambiente de producao
        return 'https://www.nobossextensions.com'.$langSef;
	}
}
