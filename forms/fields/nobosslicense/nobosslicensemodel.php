<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('_JEXEC') or die;

/**
 * Classe de model para o campo nobosslicense
 */

class NobossModelNobosslicense {

    public static function updateUserLocalPlan($updateSiteId, $extraQuery) {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        // Monta a string de query que será salva no banco
        $extraQueryString = htmlentities(http_build_query($extraQuery));
        
        $query->update("#__update_sites AS a")
		->set("a.extra_query = '{$extraQueryString}'")
		->where("a.update_site_id = '{$updateSiteId}'");
        
		try{
            $db->setQuery($query);
            $result = $db->execute();
		}catch(Exception $e){
			return false;
        }
        return $result;
    }

    /**
     * Busca pelo token e plano da licença no banco
     *
     * @param mixed $name
     * 
     * @return String Token da licença
     */

    public static function getLicenseTokenAndPlan($extensionElement){
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        
        // Busca pela linha da extensão no banco para saber se não está em nenhum pacote
        $query->select("a.package_id")
        ->from('#__extensions as a')
        ->where("a.element = '{$extensionElement}'")
        ->limit(1);
        $db->setQuery($query);
        $packageId = $db->loadResult();

        // Busca pelas indormações da licença no banco através do token
        $query = $db->getQuery(true);
        $query->select("c.extra_query, c.update_site_id")
        ->from('#__extensions as a')
        ->join('INNER', '#__update_sites_extensions AS b ON b.extension_id = a.extension_id')
        ->join('INNER', '#__update_sites AS c ON c.update_site_id = b.update_site_id')
        ->limit(1);
        
        // Caso esteja em um pacote, passa a usar o id do pacote do pacote para buscar o token
        if(!empty($packageId)){
            // Muda a busca pelo id do pacote do pacote
            $query->where("a.extension_id = '{$packageId}'");
        } else {
            $query->where("a.element = '{$extensionElement}'");
        }

        try{
            $db->setQuery($query);
            $result = $db->loadObject();
            if(empty($result)){
                throw new Exception();
            }
        }catch(Exception $e){
            return array();
        }
        // Decoda as entidades html
        $result->extra_query = html_entity_decode($result->extra_query);
        $paramsArray = array();
        parse_str($result->extra_query, $paramsArray);
        // Adiciona o id para a row para atualização do plano
        $paramsArray['update_site_id'] = $result->update_site_id;
        return $paramsArray;
    }
}
