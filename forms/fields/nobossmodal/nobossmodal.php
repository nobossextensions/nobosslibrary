<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('_JEXEC') or die;

/**
 * Classe de campo personalizado para exibir uma modal.
 */
class NobossNobossmodal {


    /**
	 * Método carrega o php da modal recebendo uma requisição ajax
	 */
	public static function loadModal() {
		jimport('noboss.util.loadextensionassets');

		$app = JFactory::getApplication();
		$post = $app->input->post;
        // Obtem os dados que vieram no post e seta como uma variavel
        $data = json_decode($post->get('data', '', "STRING"), true);
        // Obtem o nome da modal
		$modalName = $post->get('modalName', '', "STRING");
		// Obtem o caminho do xml que será buscado
		$xmlPath = $post->get('xmlPath', '', 'STRING');

		// Instancia o formulário
		try{
			$form = JForm::getInstance($modalName, JPATH_ROOT.'/'.$xmlPath, array('control' => $modalName));
		} catch (Exception $e){
			exit($e);
		}
		// Obtem nome da extensao
		$extension = $form->getAttribute('extension');
      	// Instancia objeto da classe para obter o diretorio da extensao
		$assetsObject = new NoBossUtilLoadExtensionAssets($extension);
		// Obtem diretorio da extensao
		$extensionPath = $assetsObject->getDirectoryExtension($form->getAttribute('admin') == 'true');
		// Instancia objete de linguagem
		$lang = JFactory::getLanguage();
		// Carrega arquivo tradução da library no boss
		$lang->load('lib_noboss', JPATH_SITE.'/libraries/noboss');
		// Nome da extensao foi definido no xml		
		if ($extension){
			// Carrega arquivo tradução da extensao em que a modal está sendo chamada
			$lang->load($extension, $extensionPath);
		}

		// Obtem os fieldsets do formulario
		$fieldsets = $form->getFieldsets();
		// Existem dados a serem carregados no formulario
		if (!empty($data)){
			// Percorre todos os fieldsets do formulário
			foreach ($fieldsets as $key => &$fieldset){
				// Percorre os campos do fieldset para carregar os dados
				foreach ($form->getFieldset($key) as $field) {
					$tmpArray = array();
					preg_match('/'.$modalName.'\[(.*?)\]/', $field->name, $tmpArray);
					$fieldName = $tmpArray[1];

					if (isset($data[$fieldName])){
						$form->setValue($fieldName, null, $data[$fieldName]);
					}
				}
			}
		}
		// Renderiza a modal
		exit(include(__DIR__.'/nobossmodallayout.php'));
    }
}
