<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('JPATH_PLATFORM') or die;

class NoBossUtilLoadExtensionAssets {
    
    // Variavel que armazena o nome da extensao com prefixo
    var $extensionName = '';

    // Variavel que armazena o diretorio da extensao
    var $directoryExtension = '';

    // Variavel que armazena o prefixo da extensao para códigos inline
    var $prefixCode = '';

    /**
     * Metodo construtor
     *
     * @param	string		$extensionName	    nome da extensao (ex: mod_nobossbanners)
     * @param	string		$prefixCode	        prefixo da extensao para codigos inline (opcional)
     */
    public function __construct($extensionName, $prefixCode = ''){
        // Seta o nome da extensao
        $this->extensionName = $extensionName;
        // Seta o prefixo da extensao
        $this->prefixCode = $prefixCode;
	}

    /**
     * Carrega arquivo e codigos inline JS
     *
     * @param	boolean		$loadFile				flag para informar se arquivo da extensao deve ser carregado
     * @param	array		$optionsInlineCode		dados de codigos a serem inseridos inline (opcional) - principais valores: 'prefix' e 'code'
     * @param	boolean		$loadJquery				flag para informar se a biblioteca jquery deve ser adicionada
     * @param   boolean     $loadBaseNameUrl        flag para declarar ou nao a variavel baseNameUrl
     * 
     * @return	void
     */
    public function loadJs($loadFile = true, $optionsInlineCode = array(), $loadJquery = true, $loadBaseNameUrl = true){
        $doc = JFactory::getDocument();
        
        // Setado para carregar o arquivo de JS da extensao
        if ($loadFile){
            // Nome da extensao nao foi definido
            if ($this->extensionName == ''){
                return false;
            }

            // Verifica se o site eh projeto da No Boss
            $nobossProject = JFactory::getConfig()->get("noboss_project", '0');

            // Nao eh projeto da No Boss
            if (!isset($nobossProject) || $nobossProject == 0) {

                // Ainda nao ha a variavel 'baseNameUrl' declarada no JS
                if ($loadBaseNameUrl && (!($doc->_script) || !($doc->_script["text/javascript"]) || (!strpos($doc->_script["text/javascript"], "baseNameUrl")))) {
                    // Adiciona variavel 'baseNameUrl'
                    $doc->addScriptDeclaration('var baseNameUrl =  "'.JUri::root().'";');
                }

                // Setado para carregar jquery
                if ($loadJquery){
                    // Carrega framework jquery adicionado pelo Joomla
                    JHtml::_('jquery.framework');
                }

                // Obtem o diretorio da extensao desde o diretorio raiz do site
                if ($this->getDirectoryExtension()){
                    // Monta a url do arquivo a ser adicionado
                    $urlFile = JURI::base() . $this->directoryExtension . "assets/site/js/{$this->extensionName}.min.js";

                    // Adiciona os scripts e/ou css pertencentes a extensão de acordo com a versão do joomla
                    if (version_compare(JVERSION, "4.0", '<')) {
                        $doc->addScript($urlFile, 'text/javascript', true);
                    }else {
                        $doc->addScript($urlFile, array(), array('defer' => 'defer'));
                    }
                }
            }
        }

        // Prefixo nao informado como parametro: obtem o definido na classe
        $optionsInlineCode['prefix'] = !empty($optionsInlineCode['prefix'])?: $this->prefixCode;
        
        // Ha codigo inline a ser inserido
        if (!empty($optionsInlineCode) && !empty($optionsInlineCode['prefix']) && !empty($optionsInlineCode['code'])){
            // Atualiza codigo com prefixo informado
            if ($optionsInlineCode['code'] = $this->addContextInJs($optionsInlineCode['code'], $optionsInlineCode['prefix'])){
                // Adiciona codigo inline na pagina
                $doc->addScriptDeclaration($optionsInlineCode['code']);
            }
        }
    }

    /**
     * Carrega arquivo e codigos inline CSS
     *
     * @param	boolean		$loadFile				flag para informar se arquivo da extensao deve ser carregado
     * @param	array		$optionsInlineCode		dados de codigos a serem inseridos inline (opcional) - principais valores: 'prefix' e 'code'
     *
     * @return	void
     */
    public function loadCss($loadFile = true, $optionsInlineCode = array()){
        $doc = JFactory::getDocument();
        // Setado para carregar o arquivo de CSS da extensao
        if ($loadFile){
            // Nome da extensao nao foi definido
            if ($this->extensionName == ''){
                return false;
            }
            
            // Obtem o diretorio da extensao desde o diretorio raiz do site
            if ($this->getDirectoryExtension()){
                // Monta a url do arquivo a ser adicionado
                $urlFile = JURI::base() . $this->directoryExtension . "assets/site/css/{$this->extensionName}.min.css";
                // Adiciona o arquivo CSS
                $doc->addStylesheet($urlFile);
            }
        }
        
        // Ha codigo inline a ser inserido
        if (!empty($optionsInlineCode) && !empty($this->prefixCode) && !empty($optionsInlineCode['code'])){
            // Insere codigo inline, caso esteja definido
            $this->addStyleWithPrefix($optionsInlineCode['code'], $this->prefixCode);
        }
    }

    /**
     * Adiciona uma declaracao de estilo colocando prefixo da extensao
     *
     * @param   String 	$code 		Código CSS
     * @param   String 	$prefix 	Prefixo a ser adicionado (opcional)
     *
     * @return  void
     */
    public function addStyleWithPrefix($code, $prefix = ''){
        // Prefixo nao informado como parametro: obtem o definido na classe
        if (empty($prefix)){
           $prefix = $this->prefixCode;
        }

        $doc = JFactory::getDocument();

        // Ha codigo inline a ser inserido
        if ($code && $prefix != ''){
            // Atualiza codigo com prefixo informado
            if ($code = $this->addPrefixInCss($code, $prefix)){
                // Adiciona codigo inline na pagina
                $doc->addStyleDeclaration($code);
            }
        }
        else{
            return false;
        }
    }

    /**
     * Carrega arquivo css para uma familia de icones especificada
     *
     * @param	string		$aliasFamily		alias da familiar a ser adicionada
     *
     * @return	mixed       Adiciona arquivo em caso de sucesso ou retorna false em caso de erro
     */
    public function loadFamilyIcons($aliasFamily){
        $doc = JFactory::getDocument();

        // Verifica familia solicitada para definir a url
        switch($aliasFamily){
            case 'font-awesome':
                $url = "https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css";
                break;
            case 'material-design':
                $url = "https://fonts.googleapis.com/icon?family=Material+Icons";
                break;
            case 'simple-line':
                $url = "https://cdnjs.cloudflare.com/ajax/libs/simple-line-icons/2.4.1/css/simple-line-icons.css";
                break;
            case 'linear-icons':
                $url = "https://cdn.linearicons.com/free/1.0.0/icon-font.min.css";
                break;
            default:
                $url = "";
                break;
        }
        
        // Adiciona url do arquivo css
        if ($url != ''){
            $doc->addStyleSheet($url);
            return;
        }

        return false;
    }

    /**
     * Adiciona o contexto da extensao nos blocos de JS
     *
     * @param   String 	$code 		Código JS
     * @param   String 	$prefix 	Prefixo (contexto) a ser adicionado
     *
     * @return  mixed 	Codigo com conexto adicionado ou false em caso de erro ou falta de informacoes
     */
    private function addContextInJs($code, $prefix){
        
        // Dados nao informados ou em branco
        if (empty($code) || empty($prefix)){
            return false;
        }

        // adiciona declaracao de $ como jQuery no codigo
        $code = "var $ = jQuery;\n" . $code;
        
        // Classe utilizada no elemento HTML mais externo da extensao
        $outerClass = '.' . substr($this->extensionName, 4);

        // regex para seletores usando 'jQuery' ou '$' com aspas duplas ou simples
        $pattern = '/(\$|jQuery)(\(\s*)(\'|\")(.*?)(\'|\")(\s*\))/';

        // para cada padrao encontrado no codigo, executa um callback
        $code = preg_replace_callback(
            $pattern, 
            function($found) use ($outerClass, $prefix){
                // separa os seletores informados em itens de array
                $selectors = explode(" ", $found[4]);
                // verifica se o ultimo seletor é igual a classe mais externa da extensao 
                if(end($selectors) == $outerClass){
                    // subtitui os seletores informados para o module-id da extensao
                    return str_replace($found[4], $prefix, $found[0]);
                }
                // verifica se algum dos seletores eh a classe da ext
                elseif(in_array($outerClass, $selectors)){                
                    // regex para pegar todos os seletores ate a classe da ext
                    $r = '/(?<=\\\'|\\")(.*?'.$outerClass.')+\s/';
        
                    // remove ocorrencias do padrao $r e adiciona o contexto da section da ext como parametro do seletor
                    return preg_replace($r, '', str_replace($found[6], ", '{$prefix}')", $found[0]));
                }
                // se o padrao encontrado nao atender as condicoes acima, retorna sem nenhum tratamento
                else{
                    return $found[0];          
                }
            },
            $code
        );

        // retorna o codigo tratado com os contextos adicionados
        return $code;
    }
 
    /**
     * Adiciona um prefixo em todos os blocos de CSS
     *
     * @param   String 	$code 		Código CSS
     * @param   String 	$prefix 	Prefixo a ser adicionado (opcional)
     *
     * @return  mixed 	Codigo com prefixo adicionado ou false em caso de erro ou falta de informacoes
     */
    private function addPrefixInCss($code, $prefix = ''){
        // Prefixo nao informado como parametro: obtem o definido na classe
        if (empty($prefix)){
           $prefix = $this->prefixCode;
        }

        // Classe externa utilizada no elemento HTML mais externo da extensao
        $outerClass = substr($this->extensionName, 4);
       
        // Dados nao informados ou em branco
        if ($code == '' || $prefix == ''){
            return false;
        }

        // divide os trechos de css pelo }
        $parts = explode('}', $code);
        // Percorre cada trecho
        foreach ($parts as &$part) {
            $part = trim($part);
                        
            // Verifica se não é um trecho vazio
            if (empty($part)) continue;

            // Valida a existiencia de um @media e evita que seja colocado prefixo nele
            $mediaPart = '';
            if ($part[0] == "@") {
                // Separa o media
                $tmp = explode('{', $part);
                // Guarda em uma variavel temporaia
                $mediaPart = $tmp[0];
                // remove a posição do array
                unset($tmp[0]);
                $part = implode('{', $tmp);
            }
      
            if(!function_exists('replace_function')){
                // Função de replace cahmade pelo preg replace callback no método de addPrefixInCss
                function replace_function($s) {
                    return str_replace(",", "##", $s[0]);
                }     
            }
            // Substitui as vírgulas dentro de parentesis por ## para que o prefixer não de errado em casos como rgba(0,0,0,0)            
            $part = preg_replace_callback("|\((.*)\)|", "replace_function", $part);

            // Separa as subpartes por virgula
            $subParts = explode(',', $part);

            // Adiciona o prefixo para cada subtrecho
            foreach ($subParts as &$subPart) {
                // caso possua classes
                if(!empty($subPart)){
                    // Pega os seletores
                    $subPart = trim($subPart);

                    $outerClassWithDot = '.'.$outerClass;
                    // Posicao da classe do modulo nos seletores
                    $outerClassPosition = strpos($outerClassWithDot, $subPart);
                    $outerClassLength =  strlen($outerClassWithDot);
                    // Pega o primeiro char depois da classe do modulo
                    $firstCharAfterOuterClass = substr($subPart,  $outerClassPosition + $outerClassLength, 1);

                    /*
                     * O proximo caracter apos a classe principal eh '.' (ex: .nobossfaq.container{...}) OU;
                     * O proximo caracter apos a classe principal eh ' ' (ex: .nobossfaq .container{...}) OU;
                     * Somente a classe principal esta declarada (ex: .nobossfaq{...})
                     */
                    if ($firstCharAfterOuterClass === '.' || $firstCharAfterOuterClass === ' ' || (stripos($subPart, $outerClass) == 1)){
                        /* Significa que a primeira classe seletora eh a classe do modulo, ou seja, o mesmo elemento que 
                        possui o module id, entao eles precisam ficar juntos */
                        $subPart = $prefix . $subPart;    
                    }
                    else{
                        /* A primeira classe entao nao eh a mesma que a classe do modulo e o seletor
                         externo passa a ser o id do modulo */
                        $subPart = $prefix . ' ' . $subPart;
                    }
                }
            }     
            
            // Remonta o trecho
            $part = implode(', ', $subParts);
            
            // Dá replace de volta nos ## dentro de parenteses para vírgula
            $part = str_replace('##', ',', $part);

            // Readiciona a tag @media
            if(!empty($mediaPart)){
                $part = $mediaPart.'{'.$part;
            }
        }
        // Remonta a string de css
        return trim(implode("}\n", $parts));
    }

    /**
     * Obtem o diretorio de uma extensao a partir do nome
     *
     * @param   boolean   $admin        Flag para informar se extensao eh admin ou site
     *
     * @return	mixed 		String com o diretorio ou false caso o tipo de extensao nao seja identificada
     */
    public function getDirectoryExtension($admin = ''){
        // Se variavel nao for setada pelo usuario pega informacao do acesso do usuario
       /* if ($admin == ''){
           @NBTODO: desenvolver forma de saber se deve carregar o caminho do diretorio no administrator ou do site (o metodo abaixo nao funciona)
            $app = JFactory::getApplication();
            $admin = $app->isClient('admin'); 
        }*/
    
        // Caminho base
        $basePath = ($admin) ? JPATH_ADMINISTRATOR : JPATH_SITE;
    
        // Obtem o prefixo da extensao para localizar diretorio
        $extensionPrefix = substr($this->extensionName, 0, 3);
    
        // Monta diretorio da extensao conforme o tipo
        switch ($extensionPrefix) {
            // Extensao eh um modulo
            case 'mod':
            $directoryExtension = "modules/{$this->extensionName}/";
            break;
            // Extensao eh um componente
            case 'com':
            $directoryExtension = "components/{$this->extensionName}/";
            break;
            // Extensao eh uma library
            case 'lib':
            $directoryExtension = "libraries/".substr($this->extensionName, 4)."/";
                break;
            // Tipo de extensao nao identificada
            default:
                return false;
                break;
        }
        $this->directoryExtension = $directoryExtension;
        // Monta caminho do diretorio completo
        $directoryExtension = "{$basePath}/{$directoryExtension}";
    
        return $directoryExtension;
    }
}
