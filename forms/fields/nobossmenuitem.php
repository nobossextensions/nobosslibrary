<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

use Joomla\CMS\Form\Field\MenuitemField;

// verifica versao do joomla no site atual para saber como estender o Editor do Joomla, pois antes da 3.8 a classe Editor fica em local diferente
if(version_compare(JVERSION, '3.8.0', '>=')){

    class JFormFieldNobossmenuitem extends MenuitemField{
        /**
         * The form field type.
         *
         * @var    string
         */
        public $type = "nobossmenuitem";

        /**
         * Method to get the field input markup for the editor area
         *
         * @return  string  The field input markup.
         *
         */
        protected function getInput(){
            // Busca no banco o total de items de menu cadastrados 
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('count(*)')
                ->from('#__menu');
            $db->setQuery($query);
            $total = $db->loadResult();

            // Armazena maximo de itens aceitaveis
            $limiteItens = 700;

            // Se tiver mais do que xx itens, impede de usar o campo para não ter problema de performance
            if($total > $limiteItens){
                return JText::sprintf("LIB_NOBOSS_FIELD_NOBOSSITEMMENU_MESSAGE_LIMIT", $limiteItens);
            }
            else{
                return parent::getInput();
            }
        }
    }
    

}else{
    require JPATH_LIBRARIES.'/cms/form/field/menuitem.php';

    class JFormFieldNobossmenuitem extends JFormFieldMenuitem
    {
        /**
         * The form field type.
         *
         * @var    string
         */
        public $type = "nobossmenuitem";

        /**
         * Method to get the field input markup for the editor area
         *
         * @return  string  The field input markup.
         *
         */
        protected function getInput(){
            // Busca no banco o total de items de menu cadastrados 
            $db = JFactory::getDBO();
            $query = $db->getQuery(true);
            $query->select('count(*)')
                ->from('#__menu');
            $db->setQuery($query);
            $total = $db->loadResult();

            // Armazena maximo de itens aceitaveis
            $limiteItens = 700;

            // Se tiver mais do que xx itens, impede de usar o campo para não ter problema de performance
            if($total > $limiteItens){
                return JText::sprintf("LIB_NOBOSS_FIELD_NOBOSSITEMMENU_MESSAGE_LIMIT", $limiteItens);
            }
            else{
                return parent::getInput();
            }
        }
    }
    

}

