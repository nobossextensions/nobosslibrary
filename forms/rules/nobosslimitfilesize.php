<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('JPATH_PLATFORM') or die;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       3.5
 */
class JFormRuleNobosslimitfilesize extends JFormRuleNumber
{
	/**
	 * Method to test the range for a number value using min and max attributes.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   JRegistry         $input    An optional JRegistry object with the entire data set to validate against the entire form.
	 * @param   JForm             $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   3.5
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, JRegistry $input = null, JForm $form = null)
	{

		// Verifica se o valor não é 0.
        if ((int)$value <= 0) {
        	// Adiciona mensagem.
        	$element->addAttribute("message", JText::_($element['error_less_then_zero']));
            return false;
        }

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

   		// Limite de upload em kilobytes.
		$postMaxSizeInbytes = NoBossFileSizeScale::convertScale($valueUploadMaxSize, $scaleInput, 'b');

        // Verifica se o valor informado não ultrapassa o limite da configuração de upload do PHP.ini
        if ((int)$value > $postMaxSizeInbytes) {
        	// Adiciona mensagem.
        	$message = JText::_($element['error_bigger_then_config']);
        	$message = str_replace("#upload_max_filesize_bytes#", $postMaxSizeInbytes, $message);
        	$element->addAttribute("message", $message);

            return false;
        }

        return true;

	}
}
