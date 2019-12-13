<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2019 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('JPATH_PLATFORM') or die;

class NoBossUtilInstallscript{

    /**
     * Recebe o token do usuário e manda a url do site atual para o servidor da noboss
     *
     * @param String $token token da licença do usuário
     * @return void
     */
    public static function saveAuthorizedUrl($token){
        // importa a curl da library
        jimport('noboss.util.curl');
        // Inicializa a curl
        $ch = curl_init();
        $url = self::getUrlNbExtensions(). '/index.php?option=com_nbextensoes&task=externallicenses.changeAuthorizedUrl&format=raw';
        $dataPost = array(
            'token' => $token,
            'newUrl' => base64_encode(str_replace(array('https://www.', 'http://www.', 'https://', 'http://'), '', JURI::root())),
            'overwrite' => false
        );
        NoBossUtilCurl::request('POST', $url, $dataPost, null, 10, null, null);
    }

    /**
     * Verifica se extensao pode ser atualizada a partir de um token
     *
     * @param   String      $token      Token da extensao
     * @return 	Mixed       Boolean 1 se update estiver em dia, 0 se nao estiver em dia ou 'INVALID_TOKEN' se nao localizado
     */
    public static function updateLicenseIsValid($token){
        // importa a curl da library
        jimport('noboss.util.curl');
        // Inicializa a curl
        $url = self::getUrlNbExtensions(). 'index.php?option=com_nbextensoes&task=externallicenses.updateLicenseIsValid&format=raw';
        $dataPost = array(
            'token' => $token
        );

        // Realiza requisicao curl
        $result = NoBossUtilCurl::request('POST', $url, $dataPost, null, 20, null, null, true);

        return $result->data->body;
   }

    /**
     * Recebe o nome de extensão e o valor da coluna extra_query e atualiza
     *
     * @param String $extensionName Name da extensão
     * @param String $extraQuery valor para a coluna extra_query
     */
    public static function updateExtraQuery($parent, $extraQuery){
        // Pega o nome e os servidores de update da extensão
        $extName = $parent->getName();
        $servers = (array) $parent->getManifest()->updateservers;

        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        // Para cada servidor de update
        foreach ($servers as $server) {
            // Busca o id od servidor de update
            $query = $db->getQuery(true);
            $query
            ->select('update_site_id')
            ->from('#__update_sites')
            ->where("location = '{$server}'");
            $db->setQuery($query);
            $id = $db->loadResult();

            // Caso tenha encontrado, apenas faz update do extra_query
            if(!empty($id)){
                $query = $db->getQuery(true);
                $query->update("#__update_sites")
                    ->set("extra_query = '{$extraQuery}'")
                    ->where("update_site_id = '{$id}'");
                $db->setQuery($query);
                $db->execute();
            // Caso não exista no banco, insere um novo registro
            } else {
                $query = $db->getQuery(true);
                $query->insert("#__update_sites")
                    ->columns('name, type, location, enabled, extra_query')
                    ->values("'{$extName}', 'extension', '{$server}', 1, '{$extraQuery}'");
                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    /**
    * Retorna o numero de extensões instaladas no site, não incluindo os extra.
    *
    *  @param		String		Library name of XML
    *
    *  @return		Array		Um array com as extensões dependentes da library
    */
    public static function getDependencies($extraExtensions){
        // Faz uma busca no  banco, na tabela extensions
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        // Array para as buscas
        $likeArray = array('com_noboss', 'plg_noboss', 'mod_noboss', 'tpl_noboss');
        //Monta a query para buscar na tabella extension
        $query->select("COUNT(1)")
        ->from("#__extensions")
        ->where("element NOT IN ('".implode("', '", $extraExtensions)."')");
        $val = '(';
        foreach($likeArray as $key => $like){
            if($key != 0){
                $val .= ' OR ';
            }
            $val .= "element LIKE '{$like}%'";
        }
        $val .= ')';
        $query->where($val);

        $db->setQuery($query);
        $result = $db->loadResult();

        return $result;
    }

    public static function getLibraryUpdateId($parent){
        // Pega o alias da library
        $libElement = $parent->getElement();

        // Faz uma busca no  banco, na tabela extensions
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        //Monta a query para buscar na tabella extension
        $query->select("b.update_id")
                ->from("#__extensions as a")
                ->join('INNER',"#__updates as b ON a.extension_id = b.extension_id")
                ->where("a.element = 'noboss'");
        $db->setQuery($query);
        $result = $db->loadResult();

        return (int) $result;
    }

    /**
     * Busca por todos os extras e desinstala um por um
     *
     * @return void
     */
    public static function uninstallExtras($extraExtensions){
        $extras = self::getExtrasList($extraExtensions);
        // Para cada extra, executa o processo de desinstalção
        foreach ($extras as $extra) {
            $tmpInstaller = new JInstaller;
            // Tenta fazer a desinstalação
            try{
                $result = $tmpInstaller->uninstall($extra->type, $extra->extension_id);
                if(!$result){
                    throw new RuntimeException();
                }
            } catch (Exception $e){
                // Verifica se existe a constante, caso não exista, monta um texto fixo
                if($msg = JText::sprintf('SCRIPT_EXTRAS_UNINSTALL_ERROR', $extra->element) == 'SCRIPT_EXTRAS_UNINSTALL_ERROR'){
                    $msg = "<p>Houve um erro durante a desinstalação do pacote: {$extra->element}</p>";
                }
                JLog::add($msg, JLog::WARNING, 'jerror');
            }
        }
    }

    /**
     * Busca no banco por todos os extras
     *
     * @return Array Retorna um array com informações dos extras
     */
    public static function getExtrasList($extraExtensions){
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        //Monta a query para buscar na tabela extension
        $query->select('extension_id, element, type')
        ->from($db->quoteName('#__extensions'))
        ->where("element IN('".implode("', '", $extraExtensions)."')");

        $db->setQuery($query);
        $result = $db->loadObjectList();
        return $result;
    }

    /**
     * Busca por todas as extensões que estão no mesmo pacote que o parametro
     *
     * @param int $extensionId Id da extensão que será buscado
     * @return array Array com os id das extensçoes que estão no mesmo pacote
     */
    public static function getPackageExtensions($extensionId) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        //Monta a query para buscar na tabela extension
        $query->select('a.extension_id, a.type')
        ->from('#__extensions AS a')
        ->where("a.package_id = (SELECT b.package_id FROM #__extensions AS b WHERE b.extension_id='{$extensionId}') AND a.package_id != 0 AND a.extension_id != '{$extensionId}'");

        $db->setQuery($query);
        $result = $db->loadObjectList();
        return $result;
    }

    /**
     * Desistala cada extensão passada no array
     *
     * @return void
     */
    public static function uninstallPackageExtensions($extensions){
        $tmpInstaller = new JInstaller;
        foreach ($extensions as $extension) {
            // Caso não seja o pacote em si
            if($extension->type != 'package'){
                // Tenta fazer a desinstalação
                $tmpInstaller->uninstall($extension->type, $extension->extension_id);
            } else {
                // Caso seja o pacote, deleta do banco
                $db = JFactory::getDbo();
                $query = $db->getQuery(true);
                //Monta a query para buscar na tabela extension
                $query
                    ->delete('#__extensions')
                    ->where("extension_id = {$extension->extension_id}");
        
                $db->setQuery($query);
                $db->execute();
            }
        }
    }

    /**
	*	Função que retorna a url base da plataforma No Boss Extensions para a realizacao de requisicoes
	*
	* 	@return 	String 		Url base da plataforma
	**/
	public static function getUrlNbExtensions(){
		// Objeto com dados do config
		$config = JFactory::getConfig();

        // Obtem a tag do idioma que esta sendo navegado
        $currentLanguage = JFactory::getLanguage()->getTag();
        $languages = JLanguageHelper::getLanguages('lang_code');
        $langSef = $languages[$currentLanguage]->sef;

        // TODO: qnd colocarmos o idioma ingles como default, eh necessario invester a ordem para setar 'pt' somente se usuario estiver em pt

        // Idioma que esta sendo navegado nao eh portugues brasil: forca para colocar idioma ingles na navegacao
        if($langSef != 'pt'){
            $langSef = '/en';
        }
        // Deixa sem tag de idioma para pegar portugues
        else{
            $langSef = '/';
        }

		// Obtem a url definida no config (caso exista)
		$urlNbExtensions = $config->get('url_nb_extensions');

        // Url refinida no config: retorna ela mesmo
		if (isset($urlNbExtensions) && !empty($urlNbExtensions)){
			return $urlNbExtensions.$langSef;
        }

		// Retorna url do ambiente de producao
        return 'https://www.nobossextensions.com'.$langSef;
	}
}
