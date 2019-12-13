<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2020 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Importa classe de instalacao da library
jimport('noboss.util.installscript');

// Classe de instalação da
class NobossInstallerScript{
    /**
    * A versão minima do php para instalar essa extensão
    * @var   string
    */
    protected $minPHPVersion = '5.3.17';
    /**
    * A versão minima do Joomla para instalar essa extensão
    * @var   string
    */
    protected $minJoomlaVersion = '3.5.0';
    /**
    * A versão minima recomendada do Joomla (nao impede de instalar)
    * @var   string
    */
    protected $recommendedJoomlaVersion = '3.7.1';
    /**
     * Nome da pasta da library e name registrado no xml
     *
     * @var   string
     */
    protected $libraryFolder = 'noboss';
    protected $libraryName = 'noboss';

    /**
     * Evento pre-fligt do Joomla.Esse método é executado antes de qualquer outro, sendo instalação ou update
     * Esse é o unico momento onde pode cancelar a instalação
     *
     * @param   string     $type   Tipo de intalações (install, update, discover_install)
     * @param   JInstaller $parent Parent object
     *
     * @return  boolean  true caso deva ser instalada, false para cancelar a intalação
     */
    function preflight($type, $parent){
        // Verifica se atende a versao minima do PHP
        if (!empty($this->minPHPVersion)){
            // Verifica se existe a função no servidor
            if (function_exists('phpversion')){
                $version = phpversion();
            }else{
                $version = '5.0.0';
            }
            // Valida a versão minima do php
            if (!version_compare($version, $this->minPHPVersion, 'ge')){
                $msg = JText::printf("SCRIPT_INSTALLATION_MSG_PHP_VERSION", $this->minPHPVersion, $version);
                JLog::add($msg, JLog::WARNING, 'jerror');
                throw new RuntimeException($msg, 500);
            }
        }
        // Verifica se atende a versao minima do Joomla
        if (!empty($this->minJoomlaVersion) && !version_compare(JVERSION, $this->minJoomlaVersion, 'ge')){
            $jVersion = JVERSION;
            $msg = JText::printf("SCRIPT_INSTALLATION_MSG_JOOMLA_VERSION", $this->minJoomlaVersion, $jVersion);
            JLog::add($msg, JLog::WARNING, 'jerror');
            throw new RuntimeException($msg, 500);
        }

        // Verificar se esta acima da versao recomendada do Joomla (nao impede a instalacao)
        if (!empty($this->recommendedJoomlaVersion) && !version_compare(JVERSION, $this->recommendedJoomlaVersion, 'ge')){
            $jVersion = JVERSION;
            JLog::add("To ensure the best performance of No Boss Extensions solutions, upgrade your Joomla to a version equal to or greater than ".$this->recommendedJoomlaVersion, JLog::WARNING, 'jerror');
        }

        // Atualizacao de versao inferior a ja existente
        if (!$this->isUpdate($parent)){
            $msg = JText::_('LIB_NOBOSS_SCRIPT_INSTALLATION_LIBRARY_NEWER_VERSION');
            JLog::add($msg, JLog::WARNING, 'jerror');
            throw new RuntimeException($msg, 500);
        }

        // Pasta layouts nao foi encontrada
        if (!JFolder::exists(__DIR__ . '/layouts')) {
            $msg = 'Installing or updating the No Boss Library extension did not find the '.__DIR__ . '/layouts';
            JLog::add($msg, JLog::WARNING);
            throw new RuntimeException($msg, 500);
        }

        // Diretorios que precisam permissao de escrita
        $writingDirs = array();
        $writingDirs[] = JPATH_SITE.'/libraries/';
        $writingDirs[] = JPATH_SITE.'/layouts/';
        $writingDirs[] = JPATH_SITE.'/layouts/noboss/';
        $writingDirs[] = JPATH_SITE.'/components/';
        $writingDirs[] = JPATH_SITE.'/administrator/components/';
        $writingDirs[] = JPATH_SITE.'/plugins/system/';

        // Percorre todos diretorios para tentar alterar a permissao e gerar alerta de erro caso as permissoes nao possam ser alteradas
        foreach ($writingDirs as $dir){
            // Confirma se diretorio existe
            if(JFolder::exists($dir)){
               // Altera permissao
                @chmod($dir, 0775);
                // Diretorio esta sem permissao de escrita mesmo tentando alterar
                if (!is_writable($dir)) {
                    $msg = "The No Boss extension can not be installed or updated due to lack of write permissions in the '{$dir}' directory. Raise the permissions for the directory and internal files recursively and then try to install the extension again.";
                    JLog::add($msg, JLog::WARNING, 'error');
                    throw new RuntimeException($msg, 500);
                }
            }
        }
    }

    /**
     * Evento executado após a desinstalação da library
     *
     * @param Object $parent Parent object
     */
    public function uninstall($parent){
        if(JFolder::exists(JPATH_SITE.'/layouts/noboss')){
            JFolder::delete(JPATH_SITE.'/layouts/noboss');
        }
    }

    /**
     * Evento pos-fligt do Joomla.Esse método é executado depois do install/update/uninstall
     *
     * @param   string     $type   Tipo de intalações (install, update, discover_install)
     * @param   JInstaller $parent Parent object
     */
    public function postflight($type, $parent){
        try{
             // Faz a cópia da pasta de layouts
            $this->installLayoutsFolder();
        } catch (Exception $e){
            // Criado apenas para evitar que o Joomla jogue erro 500 que deixa a library removida caso tenha algum erro nao tratado
        }
    }

    /**
    * Verifica se já existe uma library instalada,
    * coso tenha, faz uma comparação de versoes e retorna se deve instalar ou não
    *
    * @param   JInstallerAdapterLibrary $parent The parent object
    *
    * @return  Booelean True para fazer a instalação, false para não fazer
    */
    private function isUpdate($parent){
        $oldRelease = $this->getParam('version');
        // Verifica se não foi somente removido o diretório ao invés de desinstalar.
        if (!JFolder::exists(JPATH_LIBRARIES . '/'.$this->libraryFolder)){
            return true;
        }
        //pega aversão antiga
        $oldRelease = $this->getParam('version');
        // Caso não ache a library no banco, instala
        if(empty($oldRelease)){
            return true;
        }
        //Caso a versão a ser  instalada seja menor que a atual
        return version_compare( $parent->manifest->version, $oldRelease, '>' );
    }

    /**
    * Busca variáveis do  arquivo de manifesto da extensão instalada  atualmente
    *
    * @param   string $var nome da variável
    *
    * @return  mixed 	O valor da  variavel requerida
    */
    function getParam($name) {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select("a.manifest_cache")
            ->from("#__extensions as a")
            ->where("element = '{$this->libraryName}'");

        $db->setQuery($query);
        // Decoda o json
        $manifest = json_decode( $db->loadResult(), true );
        // Retorna a variável buscada
        return $manifest[$name];
    }

    /**
     * Cria uma pasta dentro de layouts e transfera os arquivos de layout para lá
     *
     * @return void
     */
    private function installLayoutsFolder(){
        $dirLayoutLibrary = JPATH_SITE.'/libraries/noboss/layouts/';
        $dirLayout = JPATH_SITE.'/layouts/noboss/';

        // Confirma se a pasta layouts esta criada dentro da library
        if(JFolder::exists($dirLayoutLibrary)){
            // Altera permissao da pasta de layouts dentro da library antes de copiar
            @chmod($dirLayoutLibrary, 0775);

            try{
                // Copia pasta layouts da library da no boss para a pasta layouts correta
                JFolder::copy($dirLayoutLibrary, $dirLayout, '', true, true);
                // Altera permissao da pasta layouts inserida no site do usuario
                @chmod($dirLayout, 0775);
	            // Remove a pasta layouts da library
	            JFolder::delete($dirLayoutLibrary);
            } catch (Exception $e){
                JLog::add('Installing or updating the extension In the Boss Library could not create the directory "layouts/noboss"', JLog::WARNING, 'jerror');
            }
        }
    }

}
