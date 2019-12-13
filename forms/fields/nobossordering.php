<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('JPATH_BASE') or die;

// Carrega class de campos de listas do Joomla.
JFormHelper::loadFieldClass('sql');

/**
 * Lista de de ordering.
 */
class JFormFieldNobossordering extends JFormFieldSQL {
	/**
	 * O tipo do campo.
	 *
	 * @var        string
	 */
	public $type = 'nobossordering';

	/**
	 * Método que monta options do select de ordering
	 *
	 * @return  array  Lista de opções para o campo.
	 */
	protected function getOptions()	{


		// Verifica se deve converter claúsulas NOW().
		if(isset($this->element['use_php_date'])){
			// Pega data atual no PHP.
			$dateNow = new DateTime();
			$dateNowSql = $dateNow->format('Y-m-d H:i:s');
			// Substitui claúsulas "NOW()" do SQL por datas do PHP.
			$this->query = str_replace('NOW()', '"'.$dateNowSql.'"', $this->query);
		}

		// Pega valores da consulta SQL.
		$options = parent::getOptions();

		// Se foi definido "primeiro option".
		if(isset($this->element['first_option'])){
			$values = explode(',', $this->element['first_option']);
			// Junta adiciona option no início.
			$options = array_merge(
				array(array('value' => $values[0], 'text' => JText::_($values[1]))),
				$options
			);
		}

		// Se foi definido "útlimo option".
		if(isset($this->element['last_option'])){
			$values = explode(',', $this->element['last_option']);
			// Junta adiciona option no fim.
			$options = array_merge(
				$options,
				array(array('value' => $values[0], 'text' => JText::_($values[1])))
			);
		}

		return $options;
	}

	/**
	 * Renderiza o campo.
	 *
	 * @return  string O HTML do campo.
	 */
	protected function getInput()	{

		if ($this->form->getValue($this->element['primary_key'], 0) == 0)	{
				return '<span class="readonly">' . JText::_($this->element['msg_new_item']) . '</span>';
		}	else {
		 	return parent::getInput();
		}
	}
}
