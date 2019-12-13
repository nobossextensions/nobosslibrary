<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

jimport('joomla.form.formfield');
JFormHelper::loadFieldClass('number');

class JFormFieldNobosslimitfilesize extends JFormFieldNumber
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = "nobosslimitfilesize";

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		// Valida e parâmetro "min" especificado para o campo.
		$this->min = 1;

		// Importa biblioteca da Noboss para conversão de grandezas.
 		jimport("noboss.file.sizescale");
 		// Pega valor da configuração de "limite de upload de arquivos" do PHP.ini.
 		$uploadMaxSize =  ini_get('upload_max_filesize');

 		// Remove espaços do valor.
		$uploadMaxSize = trim($uploadMaxSize);

		// Pega o valor removendo o último caracter da configuração.
		$valueUploadMaxSize = substr($uploadMaxSize, 0, -1);

		// Pega último caractere da configuração que corresponde a grandeza de entrada.
		$scaleInput = strtolower($uploadMaxSize[strlen($uploadMaxSize)-1]);

   		// Limite de upload em bytes.
		$this->max = NoBossFileSizeScale::convertScale($valueUploadMaxSize, $scaleInput, 'b');

		// Chama função getInput da classe pai.
		return parent::getInput();
	}
}
