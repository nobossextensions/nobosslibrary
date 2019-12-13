<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('JPATH_PLATFORM') or die;

class NoBossUtilComponentExampleData {
    /**
     * Funcao que retorna um JSON contendo todos os dados de exemplo que devem ser gerados em um componente
     *
     * @param array $exampleModals nome das modais que devem ser salvas
     * @param array $exampleDataTabs objetos representando cada aba que deve ser salva
     * @param array $ignoreFields nome dos campos que nao devem ser salvos
     *
     * @return String JSON com todos os campos e valores que servirao para gerar os dados de exemplo
     */
	public static function prepareData($exampleModals, $exampleDataTabs, $ignoreFields) {
		// cria objeto pra salvar os dados que devem ser gerados de exemplo
        $generationData = new StdClass;
		// cria objeto que armazenara em json as configs de todas modais relacionadas ao tema escolhido
        $generationData->modalsJson = new StdClass;
        // cria objeto que armazenara valores de fields especificos
        $generationData->fields = new StdClass;
        // percorre cada objeto contendo os campos de exemplo a serem salvos
        foreach ($exampleDataTabs as $tab => $fields) {
            // percorre cada campo desse objeto
            foreach ($fields as $fieldName => $value) {
                // verifica se este campo deve ser ignorado
                if(in_array($fieldName, $ignoreFields)){
                    // pula iteracao
                    continue;
                }
                // verifica se o campo deve ser salvo como uma modal
                if(in_array($fieldName, $exampleModals)){
                    // salva no objeto de modais e pula a iteracao
                    $generationData->modalsJson->$fieldName = $value;
                    continue;
                }
                // salva o campo no objeto com os dados de exemplo
                $generationData->fields->$fieldName = $value;
            }
        }
        // retorna uma string em formato JSON com todos os campos e seus valores
        return json_encode($generationData);

    }



}
