<?php
/**
 * @package			No Boss Extensions
 * @subpackage  	No Boss Library
 * @author			No Boss Technology <contact@nobosstechnology.com>
 * @copyright		Copyright (C) 2018 No Boss Technology. All rights reserved.
 * @license			GNU Lesser General Public License version 3 or later; see <https://www.gnu.org/licenses/lgpl-3.0.en.html>
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;

class NoBossUtilCurl{
    
    private static function validateCurlRequest($response, $values){
        $httpCode = $response->code;
        // Verifica se código de erro da curl é de "timeout".
        if($response->errorno == 28){
            $values->success = 0;
            $values->message = JText::_('LIB_NOBOSS_UTIL_CURL_ERROR_TIMEOUT');
        }
        // Verifica se código de erro da curl é relacionado ao certificado SSL (HTTPS)
        if($response->errorno == 60 || $response->errorno == 77){
            $values->success = 0;
            $values->message = JText::_('LIB_NOBOSS_UTIL_CURL_ERROR_SSL_CERTIFICATE');
        }
        // Valida se não teve erro 404 (não encontrado)
        if($httpCode == 404){
            $values->success = 0;
            $values->message = JText::_('LIB_NOBOSS_UTIL_CURL_ERROR_NOT_FOUND');
        } else if ($httpCode >= 400 && $httpCode < 500) {
            $values->success = 0;
            $values->message = JText::sprintf('LIB_NOBOSS_UTIL_CURL_ERROR_400_RANGE', $httpCode);
        }
        // Valida se não teve erro entre 500 e 600 (não encontrado)
        if($httpCode >= 500 && $httpCode < 600){
            $values->success = 0;
            $values->message = JText::sprintf('LIB_NOBOSS_UTIL_CURL_ERROR_500_RANGE', $httpCode);
        }
        // Verifica se a resposta não é falsa
        if ($response == "false" || empty($response)){
            $values->success = 0;
            $values->message = JText::_('LIB_NOBOSS_UTIL_CURL_ERROR_FALSE_RESPONSE');
        }
        return $values;
    }

    /**
     * Send a request to the server and return a HttpResponse object with the response.
     *
     * @param   string   $method            The HTTP method for sending the request.
     * @param   string      $uri               The URI to the resource to request.
     * @param   mixed    $data              Either an associative array or a string to be sent with the request.
     * @param   array    $headers           An array of request headers to send with the request.
     * @param   integer  $timeout           Read timeout in seconds.
     * @param   string   $userAgent         The optional user agent string to send with the request.
     * @param   string   $paramsOptions     Associative array of optional parameters
     * @param   boolean   $returnFullInfo     Sould return only the body response or bject with headers, body, httpcode and curl error code
     *
     * @return  Response
     *
     * @since   11.3
     * @throws  \RuntimeException
     */
    public static function request($method, $uri, $data = null, array $headers = null, $timeout = null, $userAgent = null, $paramsOptions = null, $returnFullInfo = false) {
        // Caso a curl não exista, solta um erro
        if (!function_exists('curl_init') || !is_callable('curl_init')) {
            throw new \RuntimeException('Cannot use a cURL transport when curl_init() is not available.');
        }
        // Cria um novo registro para não gerar erro
        if($paramsOptions === null) {
            $paramsOptions = new Registry;
        }
        // Caso um useragent não tenha sido definido, define como se fodde um navegador
        if($userAgent == null) {
            $userAgent = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/60.0.3112.113 Safari/537.36';
        }

        // Cria o objeto que será retornado
        $values = new stdClass();
        $values->success = 1;

        // Setup the cURL handle.
        $ch = curl_init();

        $options = array();
        // Set the request method.
        switch (strtoupper($method)) {
            case 'GET':
                $options[CURLOPT_HTTPGET] = true;
                break;
            case 'POST':
                $options[CURLOPT_POST] = true;
                break;
            case 'PUT':
            default:
                $options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
                break;
        }

        // Don't wait for body when $method is HEAD
        $options[CURLOPT_NOBODY] = ($method === 'HEAD');
        // Initialize the certificate store
        $options[CURLOPT_CAINFO] = $paramsOptions->get('curl.certpath', __DIR__ . '/cacert.pems');
        // Desativa a verificação ssl
        $options[CURLOPT_SSL_VERIFYPEER] = false;
        $options[CURLOPT_SSL_VERIFYHOST] = false;
        // If data exists let's encode it and make sure our Content-type header is set.
        if (isset($data) && !empty($data)) {
            // If the data is a scalar value simply add it to the cURL post fields.
            if (is_scalar($data) || (isset($headers['Content-Type']) && strpos($headers['Content-Type'], 'multipart/form-data') === 0)) {
                $options[CURLOPT_POSTFIELDS] = $data;
            }
            // Otherwise we need to encode the value first.
            else {
                $options[CURLOPT_POSTFIELDS] = http_build_query($data);
            }
            if (!isset($headers['Content-Type'])) {
                $headers['Content-Type'] = 'application/x-www-form-urlencoded; charset=utf-8';
            }
            // Add the relevant headers.
            if (is_scalar($options[CURLOPT_POSTFIELDS])) {
                $headers['Content-Length'] = strlen($options[CURLOPT_POSTFIELDS]);
            }
        }

        // Build the headers string for the request.
        $headerArray = array();
        if (isset($headers)) {
            foreach ($headers as $key => $value) {
                $headerArray[] = $key . ': ' . $value;
            }
            // Add the headers string into the stream context options array.
            $options[CURLOPT_HTTPHEADER] = $headerArray;
        }
        // Curl needs the accepted encoding header as option
        if (isset($headers['Accept-Encoding'])) {
            $options[CURLOPT_ENCODING] = $headers['Accept-Encoding'];
        }
        // If an explicit timeout is given user it.
        if (isset($timeout)) {
            $options[CURLOPT_TIMEOUT] = (int) $timeout;
            $options[CURLOPT_CONNECTTIMEOUT] = (int) $timeout;
        }
        // If an explicit user agent is given use it.
        if (isset($userAgent)) {
            $options[CURLOPT_USERAGENT] = $userAgent;
        }
        // Set the request URL.
        $options[CURLOPT_URL] = (string) $uri;
        // We want our headers. :-)
        $options[CURLOPT_HEADER] = true;
        // Return it... echoing it would be tacky.
        $options[CURLOPT_RETURNTRANSFER] = true;
        // Override the Expect header to prevent cURL from confusing itself in its own stupidity.
        // Link: http://the-stickman.com/web-development/php-and-curl-disabling-100-continue-header/
        $options[CURLOPT_HTTPHEADER][] = 'Expect:';

        // Proxy configuration
        $config = \JFactory::getConfig();
        if ($config->get('proxy_enable')) {
            $options[CURLOPT_PROXY] = $config->get('proxy_host') . ':' . $config->get('proxy_port');
            if ($user = $config->get('proxy_user')) {
                $options[CURLOPT_PROXYUSERPWD] = $user . ':' . $config->get('proxy_pass');
            }
        }

        // Set any custom transport options
        foreach ($paramsOptions->get('transport.curl', array()) as $key => $value) {
            $options[$key] = $value;
        }

        // Authentification, if needed
        if ($paramsOptions->get('userauth') && $paramsOptions->get('passwordauth')) {
            $options[CURLOPT_USERPWD] = $paramsOptions->get('userauth') . ':' . $paramsOptions->get('passwordauth');
            $options[CURLOPT_HTTPAUTH] = CURLAUTH_BASIC;
        }

        // Set the cURL options.
        curl_setopt_array($ch, $options);
        // Execute the request and close the connection.
        $content = curl_exec($ch);

        // // Check if the content is a string. If it is not, it must be an error.
        // if (!is_string($content)) {
        //     $message = curl_error($ch);
        //     if (empty($message)) {
        //         // Error but nothing from cURL? Create our own
        //         $message = 'No HTTP response received';
        //     }
        //     throw new \RuntimeException($message);
        // }
        // Pega o numero do erro curl
        $curlErroNo = curl_errno($ch);
        // Get the request information.
        $info = curl_getinfo($ch);
        $response = self::getResponse($content, $info);
        // Get the curl error number
        $response->errorno = $curlErroNo;

        // Chama o método para fazer as validações de curl
        $values = self::validateCurlRequest($response, $values);
        // fecha a curl
        curl_close($ch);

        // Manually follow redirects if server doesn't allow to follow location using curl
        if ($response->code >= 301 && $response->code < 400 && isset($response->headers['Location'])) {
            $redirect_uri = new Uri($response->headers['Location']);
            if (in_array($redirect_uri->getScheme(), array('file', 'scp'))) {
                throw new \RuntimeException('Curl redirect cannot be used in file or scp requests.');
            }
            $response = self::request($method, (string) $redirect_uri, $data, $headers, $timeout, $userAgent);
        }
        
        // Armazena a resposta para retorno
        if($returnFullInfo){
            $values->data = $response;
        } else {
            $values->data = $response->body;
        }
        return $values;
    }

    /**
     * Method to get a response object from a server response.
     *
     * @param   string  $content  The complete server response, including headers
     *                            as a string if the response has no errors.
     * @param   array   $info     The cURL request information.
     *
     * @return  Response
     *
     * @since   11.3
     * @throws  \UnexpectedValueException
     */
    protected static function getResponse($content, $info) {
        // Create the response object.
        $return = new JHttpResponse;        
        // Try to get header size
        if (isset($info['header_size'])) {
            $headerString = trim(substr($content, 0, $info['header_size']));
            $headerArray  = explode("\r\n\r\n", $headerString);
            // Get the last set of response headers as an array.
            $headers = explode("\r\n", array_pop($headerArray));
            // Set the body for the response.
            $return->body = substr($content, $info['header_size']);
        }
        // Fallback and try to guess header count by redirect count
        else {
            // Get the number of redirects that occurred.
            $redirects = isset($info['redirect_count']) ? $info['redirect_count'] : 0;
            /*
             * Split the response into headers and body. If cURL encountered redirects, the headers for the redirected requests will
             * also be included. So we split the response into header + body + the number of redirects and only use the last two
             * sections which should be the last set of headers and the actual body.
             */
            $response = explode("\r\n\r\n", $content, 2 + $redirects);
            // Set the body for the response.
            $return->body = array_pop($response);
            // Get the last set of response headers as an array.
            $headers = explode("\r\n", array_pop($response));
        }

        // Get the response code from the first offset of the response headers.
        preg_match('/[0-9]{3}/', array_shift($headers), $matches);

        $code = count($matches) ? $matches[0] : null;
        if (is_numeric($code)){
            $return->code = (int) $code;
        } else {
            $return->code = 500;
        }

        // Add the response headers to the response object.
        foreach ($headers as $header){
            $pos = strpos($header, ':');
            $return->headers[trim(substr($header, 0, $pos))] = trim(substr($header, ($pos + 1)));
        }
        return $return;
    }

}
