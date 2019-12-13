<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('JPATH_PLATFORM') or die;

class NoBossUtilLog {
    /**
     * Funcao que obtem o IP do usuario
     *
     * @return 	String 	Ip do usuario
     */
	public static function getIp() {
		$ip = "";
		if (getenv("HTTP_CLIENT_IP")){
			$ip = getenv("HTTP_CLIENT_IP");
		}else if(getenv("HTTP_X_FORWARDED_FOR")) {
			$ip = getenv("HTTP_X_FORWARDED_FOR");
		}else if(getenv("REMOTE_ADDR")) {
			$ip = getenv("REMOTE_ADDR");
		}else {
			$ip = "UNKNOWN";
		}
		return $ip;
    }
    
    /**
     * Funcao que obtem informacoes do browser do usuario
     *
     * @return 	String 	   Informacoes do browser concatenadas
     */
    public static function getBrowserInfo(){
        return $_SERVER['HTTP_USER_AGENT'];
    }

}
