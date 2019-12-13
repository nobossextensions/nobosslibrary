<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2019 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

// Carrega classe do campo calendário.
JFormHelper::loadFieldClass('calendar');

/**
 * Campo de calendário da No Boss.
 */
class JFormFieldNobosscalendar extends JFormFieldCalendar {

	protected $type = "nobosscalendar";

  public function setup(SimpleXMLElement $element, $value, $group = null){

    $return = parent::setup($element, $value, $group);

    // Permite recebe uma constante de tradução para o parâmetro "format".
    $this->format = JText::_($this->format);

    $doc = JFactory::getDocument();

    // Gera id unico para o campo
    $this->uniqId = "time_".uniqid();

    // Setado markara
    if (!empty($this->format)){
        $doc->addScript(JURI::base()."../libraries/noboss/assets/plugins/js/min/jquery.mask.min.js");

        // Formato para mascara do campo
        $formatMask = str_replace('%Y', '0000', str_replace(array('%m', '%d', '%H', '%M', '%S'), '00', $this->format));

        // Ativa marcara no campo
        $doc->addScriptDeclaration("
            jQuery(function($) {
                $('[data-id=\"{$this->uniqId}\"] input').mask('{$formatMask}');
            });
        ");
    }

    return $return;
  }

  protected function getInput() {
    $html = parent::getInput();

    // Adiciona div externa com id
    $html = "<div data-id='{$this->uniqId}'>{$html}</div>";

    return $html;
  }
}
