<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('textarea');

class JFormFieldNobosscustomjs extends JFormFieldTextarea
{
    /**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
    protected $type = "nobosscustomjs";

    /**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   3.2
	 */
    protected function getInput(){
        // seta o placeholder
        $this->hint = JText::_('NOBOSS_EXTENSIONS_JS_OVERWRITE_PLACEHOLDER');
        
        $this->description = str_replace('#class_name#', ".{$this->element['class_name']}", JText::_('NOBOSS_EXTENSIONS_JS_OVERWRITE_DESC'));

        return parent::getInput();
    }  

}
