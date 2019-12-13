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
 * Classe de campo personalizado para listagem da fonte e estilos de fonte.
 */
class NobossNobossfontlist {


    /**
	 * Carrega uma lista dos estilos disponíveis para uma determinada fonte
	 */
	public static function loadFontStyles() {
		// Função de tratamento dos nomes retornados pelo glob
		function cleanName($v){
			$v = basename($v);
			$v = explode('.', $v);
			
			$fileName = explode('_', $v[0]);

			// Removel o fim da string, que é o tipo de estilo
			$fileName = array_pop($fileName);

			return $fileName;
		}
		
		$app = JFactory::getApplication();
		$get = $app->input->get;

        // Obtem o nome da fonte
		$fontName = $get->get('fontName', '', "STRING");
		$fontNameArray =  explode('.', $fontName);
		$fileNameArray = explode('_', $fontNameArray[0]);
		$fileName = $fileNameArray[0];

		// Pega os arquivos que começam com o nome da fonte escolhida
		$styledFontsArray = array_map('cleanName', glob(JPATH_SITE.'/libraries/noboss/forms/fields/assets/fonts-stylized/{'.$fileName.'}_*.*', GLOB_BRACE));
		array_unshift($styledFontsArray, 'Regular');

		// Retorna um json com os itens
		exit(json_encode($styledFontsArray));
	}
	
}
