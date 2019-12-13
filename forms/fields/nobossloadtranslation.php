<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

jimport('noboss.util.loadextensionassets');

class JFormFieldNobossLoadtranslation extends JFormField
{
  /**
   * The form field type.
   *
   * @var    string
   * @since  1.0
   */
  protected $type = "nobossloadtranslation";

    /**
     * Metodo que carrega o arquivo de traducao para extensao especificada. 
     * Caso o nome da extensao nao seja especificada, carrega traducao da library da no boss
     */
    protected function getInput(){
      // Nome da extensao
      $extensionName = $this->getAttribute('extension', 'lib_noboss');
      // Instancia objeto da classe para obter o diretorio da extensao
      $assetsObject = new NoBossUtilLoadExtensionAssets($extensionName);
      // Obtem diretorio da extensao
      $extensionPath = $assetsObject->getDirectoryExtension($this->getAttribute('admin')=='true');
      // Carrega arquivo traducao para extensao especificada
      JFactory::getLanguage()->load($extensionName, $extensionPath);
      // pega documento
      $doc = JFactory::getDocument();
      // seta estilo do campo para que nenhum espaçamento seja exibido
      $doc->addStyleDeclaration(
        ".control-group.{$this->type} {
            display: none;
        }"
      );


    }
}
