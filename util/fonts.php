<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('JPATH_PLATFORM') or die;

class NoBossUtilFonts
{
	/**
	 * Recebe o nome de um arquivo de fonte, adiciona na página e retorna o nome próprio da fonte para a chamada no css
	 *
	 * @param   string   $fontFile  Nome do arquivo de fonte
	 * @param   string   $fontUrl   Url do google para importar a fonte
	 *
	 * @return  string  elemento css de definição da font-family
	 */
     public static function importFont($fontFile, $fontUrl = '', $textFontStyle = 'Regular', $onlyName = false){
        $doc = JFactory::getDocument();

        if($fontFile == 'inherit'){
            $fontName = 'inherit';
        } else if ($fontFile == 'external_linked'){
            if(!empty($fontUrl)){
                $fontUrlParse = parse_url($fontUrl);

                // Pega os parametros da url
                parse_str($fontUrlParse['query'], $query);
                // Pega a primeira fonte do parametro Family
                $fontName = explode('|', $query['family']);
                $fontName = $fontName[0];
                // Cria o css que importa a fonte
                $doc->addStylesheet($fontUrl);
            }else{
                $fontName = 'inherit';
            }
        } else {
            $fontFormats = array(
                'ttf'   => 'truetype',
                'otf'   => 'opentype',
                'woff'  => 'woff',
                'woff2' => 'woff2',
                'svg'   => 'svg',
                'eot'   => 'embedded-opentype'
            );
            // Pega a extensão do arquivo
            $fileExt = pathinfo($fontFile, PATHINFO_EXTENSION);
            // Remove a extensão do arquivo
            $filename = substr($fontFile, 0 , (strrpos($fontFile, ".")));
            // Monta o Font Family a partir do nome do arquivo
            $fontName = str_replace('-', ' ', $filename);

            // Monta o caminho para o arquivo de fonte
            $fontPath = JUri::root().'libraries/noboss/forms/fields/assets/fonts/'.$fontFile;

            // Verifica se a fonte tem alguma estilização, ex. itálico ou negrito  
            if($textFontStyle != 'Regular'){
                // Remonta o $fontFile adicionando a o nome da estilização depois de um '-'
                // $tmp = explode('.', $fontFile);
                // $tmp[0] = $tmp[0].'_'.ucfirst($textFontStyle);
                // $styledFontFile = implode('.', $tmp);
                
                $styledFontFile = str_replace("Regular", $textFontStyle, $fontFile);
                $fontName = substr($styledFontFile, 0 , (strrpos($styledFontFile, ".")));

                // Monta o caminho até a fonte
                if(file_exists(JPATH_SITE.'/libraries/noboss/forms/fields/assets/fonts-stylized/'.$styledFontFile)){
                    // Monta o caminho até a fonte
                    $fontPath = JUri::root().'libraries/noboss/forms/fields/assets/fonts-stylized/'.$styledFontFile;
                }
            }

            $format = '';
            if ($fileExt != ''){
                $format = $fontFormats[$fileExt];
            }

            // Monta o código que deve ser posto na tag style
            $callCode = "@font-face { font-family: '{$fontName}'; src: url('{$fontPath}') format('{$format}')}\n";
    
            // Adiciona a codigo na tag style
            $doc->addStyleDeclaration($callCode);
        }
        // Verifica se deve retornar somente o nome da fonte ou a declaração css
        if (!$onlyName){
            if($fontName == "inherit"){
                // Retorna a declaracao css da fonte
                return 'font-family: ' . $fontName . ';';
            } else {
                // Retorna a declaracao css da fonte
                return 'font-family: "' . $fontName . '";';
            }
        } else {
            // Retorna o nome da fonte
            return $fontName;
        }
    }

    /**
     * Recebe o valor do campo nobossfontlist, prepara ele e chama a função importFont
     *
     * @param String $value string contendo o json do campo nobossfontlist
     * 
	 * @return  string  elemento css de definição da font-family
     */
    public static function importNobossfontlist($value, $onlyName = false){
        if($decoded = json_decode($value)){
            return self::importFont($decoded->fontfamily, $decoded->externalLinked, $decoded->fontStyle, $onlyName);
        } else {
            return self::importFont($value, '', 'Regular', $onlyName);
        }
    }

}
