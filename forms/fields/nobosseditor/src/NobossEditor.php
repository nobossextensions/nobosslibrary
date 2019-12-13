<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Editor\Editor;

use Joomla\Registry\Registry;

// verifica versao do joomla no site atual para saber como estender o Editor do Joomla, pois antes da 3.8 a classe Editor fica em local diferente
if(version_compare(JVERSION, '3.8.0', '>=')){
	/**
	* Editor class to handle WYSIWYG editors
	*
	*/
	class NobossEditor extends Editor {
		
		
		/**
		* Editor instances container.
		*
		* @var    Editor[]
		* @since  2.5
		*/
		protected static $instances = array();
		
		/**
		* Array com editores publicados
		*
		* @var    array
		*/
		protected $editors;
		
		/**
		* Retorna uma instancia de um editor caso ainda nao exista
		*
		* @param   string  $editor  Editor a ser usado.
		*
		* @return  Editor Objeto Editor.
		*
		* @since   1.5
		*/
		public static function getInstance($editor = 'tinymce') {
			
			$signature = serialize($editor);
			
			if (empty(self::$instances[$signature]))
			{
				self::$instances[$signature] = new NobossEditor($editor);
			}
			
			return self::$instances[$signature];
		}
		
		
		/**
		* Carrega o editor
		*
		* @param   array  $config  Array associativo com parametros de configuracao do editor
		*
		* @return  mixed
		*
		* @since   1.5
		*/
		protected function _loadEditor($config = array()) {
			
			// Armazena os editores que estao publicados
			$this->editors = JPluginHelper::getPlugin('editors');
			
			// Se já houver um editor instanciado ou se nenhum editor estiver habilitado, sai da funcao
			if ($this->_editor !== null || empty($this->editors))
			{
				return;
			}		
			
			// Se nao for possivel instanciar o editor que foi especificado 
			if(!$plugin = \JPluginHelper::getPlugin('editors', $this->_name)){
				$i = 0;			
				// Tenta instanciar um editor com as demais opcoes possiveis
				do{
					// Guarda o nome do editor atual
					$this->_name = $this->editors[$i]->name;
					// Tenta instanciar o editor atual
					$plugin = \JPluginHelper::getPlugin('editors', $this->_name);
					
					$i++;
				}while(empty($plugin));
			}

			// se os parametros do plugin nao tenham sido instanciados como um objeto Registry
			if(!$plugin->params instanceof Registry){
				// torna ele em um obj Registry
				$params = new Registry($plugin->params);
				$params->loadArray($config);
				// seta esses parametros no objeto do plugin
				$plugin->params = $params;
			}
			
			// Monta o caminho para o plugin de editor
			$name = \JFilterInput::getInstance()->clean($this->_name, 'cmd');
			$path = JPATH_PLUGINS . '/editors/' . $name . '/' . $name . '.php';
			
			// Sai da funcao caso o arquivo do plugin nao seja encontrado
			if (!is_file($path))
			{
				\JLog::add(\JText::_('JLIB_HTML_EDITOR_CANNOT_LOAD'), \JLog::WARNING, 'jerror');
				return false;
			}
			
			// Require no arquivo do plugin
			require_once $path;

			// Nome da classe do plugin
			$name = 'PlgEditor' . $this->_name;		
			
			// Instancia o plugin
			$this->_editor = new $name($this, (array) $plugin);
			
			// Verifica se o editor eh tinymce e faz as personalizacoes possiveis
			if($this->_name == 'tinymce'){
				// Seta configuracoes de toolbar para cada preset existente
				foreach($plugin->params->get('configuration.toolbars') as $preset){
					$preset->toolbar1 = $config['toolbar'];
				}			
				// Define a altura do editor
				$plugin->params->set('html_height', $config['editor_height']);
			}

			// Inicia o plugin
			$this->initialise();		
		}
		
    }
    
}else{
    require JPATH_LIBRARIES . '/cms/editor/editor.php';
	class NobossEditor extends JEditor {
	
		/**
		* Editor instances container.
		*
		* @var    Editor[]
		* @since  2.5
		*/
		protected static $instances = array();
		
		/**
		* Array com editores publicados
		*
		* @var    array
		*/
		protected $editors;
		
		/**
		* Retorna uma instancia de um editor caso ainda nao exista
		*
		* @param   string  $editor  Editor a ser usado.
		*
		* @return  Editor Objeto Editor.
		*
		* @since   1.5
		*/
		public static function getInstance($editor = 'tinymce') {
			
			$signature = serialize($editor);
			
			if (empty(self::$instances[$signature]))
			{
				self::$instances[$signature] = new NobossEditor($editor);
			}
			
			return self::$instances[$signature];
		}
		
		
		/**
		* Carrega o editor
		*
		* @param   array  $config  Array associativo com parametros de configuracao do editor
		*
		* @return  mixed
		*
		* @since   1.5
		*/
		protected function _loadEditor($config = array()) {
			
			// Armazena os editores que estao publicados
			$this->editors = JPluginHelper::getPlugin('editors');
			
			// Se já houver um editor instanciado ou se nenhum editor estiver habilitado, sai da funcao
			if ($this->_editor !== null || empty($this->editors))
			{
				return;
			}		
			
			// Se nao for possivel instanciar o editor que foi especificado 
			if(!$plugin = \JPluginHelper::getPlugin('editors', $this->_name)){
				$i = 0;			
				// Tenta instanciar um editor com as demais opcoes possiveis
				do{
					// Guarda o nome do editor atual
					$this->_name = $this->editors[$i]->name;
					// Tenta instanciar o editor atual
					$plugin = \JPluginHelper::getPlugin('editors', $this->_name);
					
					$i++;
				}while(empty($plugin));
			}

			// se os parametros do plugin nao tenham sido instanciados como um objeto Registry
			if(!$plugin->params instanceof Registry){
				// torna ele em um obj Registry
				$params = new Registry($plugin->params);
				$params->loadArray($config);
				// seta esses parametros no objeto do plugin
				$plugin->params = $params;
			}
			
			// Monta o caminho para o plugin de editor
			$name = \JFilterInput::getInstance()->clean($this->_name, 'cmd');
			$path = JPATH_PLUGINS . '/editors/' . $name . '/' . $name . '.php';
			
			// Sai da funcao caso o arquivo do plugin nao seja encontrado
			if (!is_file($path))
			{
				\JLog::add(\JText::_('JLIB_HTML_EDITOR_CANNOT_LOAD'), \JLog::WARNING, 'jerror');
				return false;
			}
			
			// Require no arquivo do plugin
			require_once $path;

			// Nome da classe do plugin
			$name = 'PlgEditor' . $this->_name;		
			
			// Instancia o plugin
			$this->_editor = new $name($this, (array) $plugin);
			
			// Verifica se o editor eh tinymce e faz as personalizacoes possiveis
			if($this->_name == 'tinymce'){
				// Seta configuracoes de toolbar para cada preset existente
				foreach($plugin->params->get('configuration.toolbars') as $preset){
					$preset->toolbar1 = $config['toolbar'];
				}			
				// Define a altura do editor
				$plugin->params->set('html_height', $config['editor_height']);
			}

			// Inicia o plugin
			$this->initialise();		
		}
		
	}

}
