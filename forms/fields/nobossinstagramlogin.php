<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("_JEXEC") or die('Restricted access');

jimport('noboss.util.url');

class JFormFieldNobossInstagramlogin extends JFormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.2
     */
    protected $type = "nobossinstagramlogin";
    /**
   * Method to get the field input markup
   */
    protected function getInput(){
        // Verifica qual botão deve aparecer e qual deve ficar escondido
        $logoffBtn = 'style="display:none;"';
        $loginBtn = '';
        if (!empty($this->value)) {
            $logoffBtn = '';
            $loginBtn = 'style="display:none;"';
        }
        
        $html = "<a data-id='instagram-logoff-btn' class='btn btn-instagram' ".$logoffBtn.">
                    <span class='fa fa-instagram login-icon'></span>
                    ". JText::_('LIB_NOBOSS_FIELD_NOBOSSINSTAGRAMLOGIN_BTN_LOGOFF_LABEL') ."
                </a>";
        $html .= "<a data-id='instagram-login-btn' class='btn btn-instagram' ".$loginBtn.">
                    <span class='fa fa-instagram login-icon'></span>
                    ". JText::_('LIB_NOBOSS_FIELD_NOBOSSINSTAGRAMLOGIN_BTN_LOGIN_LABEL') ."
                </a>";

        // Decoda o valor json salvo
        if (!empty($this->value)){
            $hideLoggedInfo = "";
            try{
                $decodedValue = json_decode($this->value);
                if(empty($decodedValue)){
                    throw new Exception;
                }
                $userName = $decodedValue->user->username;
            } catch (Exception $e){
                $userUrl = "";
                $userName = '';
                $hideLoggedInfo = "display:none;";
            }
            $userUrl = "https://www.instagram.com/{$userName}/";
        } else {
            $userUrl = "";
            $userName = "";
            $hideLoggedInfo = "display:none;";
        }

        $html .= "<p class='instagram-logged' data-id='instagram-logged' style='{$hideLoggedInfo}'>Usuário logado: <a href='{$userUrl}' target='_blank'><span class='instagram-logged-user' data-id='instagram-logged-user'>@{$userName}</span></a></p>";

        $html .= "<input type='hidden' data-id='instagram-input-hidden' name='{$this->name}' value='{$this->value}'/>";

        $doc = JFactory::getDocument();

        JText::sprintf('NOBOSS_EXTENSIONS_URL_SITE', NoBossUtilUrl::getUrlNbExtensions(), array('script' => true));

        $doc->addScript(JURI::base()."../libraries/noboss/forms/fields/assets/js/min/nobossinstagramlogin.min.js");
        $doc->addStylesheet(JURI::base()."../libraries/noboss/forms/fields/assets/stylesheets/css/nobossinstagramlogin.min.css");
        $doc->addStyleSheet("https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css");
        return $html;
    }
}
