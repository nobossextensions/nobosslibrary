<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

class JFormFieldNobossrequestjscss extends JFormField
{
  /**
   * The form field type.
   *
   * @var    string
   * @since  3.2
   */
  protected $type = "nobossrequestjscss";

  /**
     * Method to get the field input markup
     */
    protected function getInput(){
      $doc = JFactory::getDocument();

      // Caminho do arquivo CSS ou JS desde o diretório raiz do projeto
      $file     = $this->getAttribute('file');
      // Tipo do arquivo que pode ser 'css' ou 'js'
      $fileType   = $this->getAttribute('filetype');
      // Informa se deve ser concatenada a url do site na requisicao ('internal' ou 'external')
      $prefixUrlSite  = $this->getAttribute('prefixurlsite', 'internal');

      // Parametros file não informado
      if (!isset($file) || $file==''){
        return false;
      }

      // Concatenar url do site na requisicao
      if ($prefixUrlSite == 'internal'){
        // Requisicao eh de JS
        if($fileType=='js'){
          //Verifica se já tem uma basenameUrl definido no site
          if (@!strpos($doc->_script["text/javascript"], "baseNameUrl")) {
            //Adiciona basenameurl
            $doc->addScriptDeclaration('var baseNameUrl =  "'.JUri::root().'";');
          }
        }

        $file = JURI::root() . $file;
      }

      // Requiseção CSS
      if ($fileType=='css'){
        $doc->addStyleSheet($file);
      }
      // Requisição JS
      else if ($fileType=='js'){
        $doc->addScript($file);
      }
      // Parametro fileType não definido ou definido incorretamente
      else{
        return false;
      }
    }
}
