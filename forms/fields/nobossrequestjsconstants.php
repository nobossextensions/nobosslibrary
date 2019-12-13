<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

class JFormFieldNobossrequestjsconstants extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.2
    */
    protected $type = "nobossrequestjsconstants";
    
    /**
     * Method to get the field input markup
    */
    protected function getInput(){
        // pega o documento
        $doc = JFactory::getDocument();
        // pega as constantes que devem ser passadas para o js
        $constants = array_map('trim', explode(",", $this->getAttribute('constants')));
        // percorre cada constante
        foreach ($constants as $constant) {
            // adiciona ela ao js
            JText::script($constant);
        }
    }
}
