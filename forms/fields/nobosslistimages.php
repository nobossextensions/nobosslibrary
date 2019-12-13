<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined("JPATH_PLATFORM") or die;

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');
JFormHelper::loadFieldClass('filelist');
class JFormFieldNobosslistimages extends JFormFieldFileList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  3.2
	 */
	protected $type = "nobosslistimages";
	/**
   * Method to get the field input markup
   */
  	protected function getInput(){
			$html = array();
			// Pega o caminho original, setado pelo usuário
			$pathRaw = $this->directory;

			//monta o caminho da imagem no sistema
			if (!is_dir($pathRaw)){
				$path = JPATH_ROOT . '/' . $pathRaw;
			}else{
				$path = $pathRaw;
			}
			// Pega os arquivos em um determinado caminho
			$files = JFolder::files($path, $this->filter);


			$html[] = '<div>';
			// Build the options list from the list of files.
			if (is_array($files)){
				foreach ($files as $file){
					$html[] = '<img style="max-height:20px" src="'.JURI::root().$pathRaw.'/'.$file.'" />';
					$file = JFile::stripExt($file);
					$html[] = '<span>'.$file.'</span>';
				}
			}
			$html[] = '</div>';

		  return implode($html);
  	}
}
