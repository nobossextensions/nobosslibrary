<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2019 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

class JFormFieldNobossmodalmodules extends JFormFieldList {

    protected $type = "nobossmodalmodules";

    /**
     * Method to get a list of options for a list input.
     *
     * @return   array   An array of JHtml options.
     */
    protected function getOptions() {
        $options = array();
        $db	= JFactory::getDbo();
        $query =  $db->getQuery(true);
        $query->select('id, title, module, position, published');
        $query->from('#__modules AS m');
        $query->where("m.client_id = 0");
        $query->where("published IN ('0', '1')");
       
        // Ordena priorizando a exibicao dos modulos da no boss
        $query->order("module like 'mod_noboss%' DESC, module like 'mod_nb%' DESC, published DESC, module, ordering");

        // Set the query
        $db->setQuery($query);

        if (!($modules = $db->loadObjectList())) {
            JError::raiseWarning(500, JText::sprintf('LIB_NOBOSS_FIELD_NOBOSSMODULES_ERROR_LOAD', $db->getErrorMsg()));
            return false;
        }

        // Percorre resultados para montar opcoes do select
        foreach($modules as $module){
            $title = $module->title . ' (' . $module->module . ')';
            if($module->published == '0'){
                $title .= JText::_('LIB_NOBOSS_FIELD_NOBOSSMODULES_NOT_PUBLISHED');
            }

            $options[] = JHtml::_('select.option', $module->id, $title);
        }

        // Merge any additional options in the XML definition.
        $options = array_merge(parent::getOptions(), $options);
        
        return $options;

    }


    protected function getInput() {
        $modalName = 'modal_' . $this->id;

        // JS que eh executado apos usuario clicar no botao para abrir a modal
		JFactory::getDocument()->addScriptDeclaration('
			jQuery(function($) {
 				$("#' . $modalName . '").on("shown.bs.modal", function() {
					var moduleID = $("#' . $this->id . '").val();
					var url = "' . JURI::base() . 'index.php?option=com_modules&view=module&task=module.edit&layout=modal&tmpl=component&id=" + moduleID;
                    $("#' . $modalName . ' iframe").attr("src", url);
				})
			});
		');
		
		$options = [
			'title'       => JText::_('LIB_NOBOSS_FIELD_NOBOSSMODULES_BUTTON_EDIT_MODULE'),
			'url'         => '#',
			'height'      => '400px',
			'width'       => '800px',
			'backdrop'    => 'static',
			'bodyHeight'  => '70',
			'modalWidth'  => '70',
			'footer'      => '<button type="button" class="btn btn-secondary" data-button-module-close data-dismiss="modal" aria-hidden="true">'
					. JText::_('LIB_NOBOSS_FIELD_NOBOSSMODULES_MODAL_BUTTON_CLOSE') . '</button>                                      
					<button type="button" class="btn btn-primary" aria-hidden="true"
					<button type="button" class="btn btn-success" aria-hidden="true"
					onclick="jQuery(\'#' . $modalName . ' iframe\').contents().find(\'#applyBtn\').click();">'
					. JText::_('LIB_NOBOSS_FIELD_NOBOSSMODULES_MODAL_BUTTON_SAVE') . '</button>',
		];

		echo JHtml::_('bootstrap.renderModal', $modalName, $options);

		return parent::getInput() . 
			'<a class="btn btn-secondary editModule" data-toggle="modal" href="#'. $modalName .'">
				<span class="icon-edit"></span> ' . JText::_('LIB_NOBOSS_FIELD_NOBOSSMODULES_BUTTON_EDIT_MODULE') . '
        	</a>';
    }
}
