<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2019 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

class JFormFieldNobossclockpicker extends JFormField {

    /**
	 * The form field type.
	 *
	 * @var    string
	 */
	protected $type = "nobossclockpicker";

	/**
     * Method to get the field input markup
     */
  	protected function getInput() {
        
        // AAtributos que podem ser definidos no xml
		$this->hint      = $this->getAttribute('hint', '00:00');
		$this->class     = $this->getAttribute('class', 'input-mini');
		$this->placement = $this->getAttribute('placement', 'top');
		$this->align     = $this->getAttribute('align', 'left');
		$this->autoclose = $this->getAttribute('autoclose', 'true');
		$this->default   = $this->getAttribute('default', 'now');
		$this->donetext  = $this->getAttribute('donetext', 'Apply');

		// Adicionar css e js do plugin jquery
        JHtml::_('jquery.framework');
        $doc = JFactory::getDocument();
        $doc->addScript(JURI::base()."../libraries/noboss/assets/plugins/js/min/jquery-clockpicker.min.js");
        $doc->addScript(JURI::base()."../libraries/noboss/assets/plugins/js/min/jquery.mask.min.js");
        $doc->addStylesheet(JURI::base()."../libraries/noboss/assets/plugins/stylesheets/css/jquery-clockpicker.min.css");

        // Gera id unico para o campo
        $uniqId = "time_".uniqid();

        // Ativa clockpicker e marcara no campo
        $doc->addScriptDeclaration("
            jQuery(function($) {
                $('[data-id-clockpicker=\"{$uniqId}\"]').clockpicker();
                $('[data-id-clockpicker=\"{$uniqId}\"] input').mask('{$this->hint}');
            });
        ");

        // Joomla esta na versao 4
        if (version_compare(JVERSION, '4.0', 'ge')){
            $doc->addStyleDeclaration('
                .clockpicker-popover {
                    font-size: .93rem;
                }
            ');	
        }
        // Joomla esta na versao abaixo de 4
        else{
            $doc->addStyleDeclaration('
                .clockpicker-align-left.popover > .arrow {
                    left: 25px;
                }
            ');
        }

		return '
			<div class="input-group input-append clockpicker" data-id-clockpicker="'.$uniqId.'" data-donetext="' . $this->donetext . '" data-default="' . $this->default . '" data-placement="' . $this->placement . '" data-align="' . $this->align . '" data-autoclose="' . $this->autoclose . '">
				<input class="' . $this->class . ' form-control" placeholder="' . $this->hint . '" name="' . $this->name . '" type="text" class="form-control" value="' . $this->value . '">
				
				<span class="input-group-addon input-group-append">
					<span class="btn btn-secondary">
						<span class="icon-clock"></span>
					</span>
				</span>
			</div>';
  	}
}
