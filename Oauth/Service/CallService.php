<?php

namespace Sludio\HelperBundle\Oauth\Service;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class CallService
{
    public function sendRequest(array $options = [])
    {
        $headr = $this->header;
        if (isset($options['access_token'])) {
            $headr[] = 'Authorization: Bearer '.$options['access_token'];
            unset($options['access_token']);
        }
        $url = $options['url'];
        unset($options['url']);
        
        $url .= '?'.http_build_query($options);
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $information = curl_getinfo($ch);
        
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $curl_error_code = curl_errno($ch);
        curl_close($ch);
        
        return array('CURL_CODE' => $curl_error_code, 'RESPONSE' => $result);
    }
    
    public function __construct($clients, $session)
    {
        $this->clients = $clients;
        $this->header = [];
        $this->header[] = 'Content-length: 0';
        $this->header[] = 'Content-type: application/json';
        
        $this->request = Request::createFromGlobals();
        $this->session = $this->request->getSession() ?: $session;
    }
    
    public function getAccessToken($username = null, $password = null, $refresh_token = null, $client = null)
    {
        if (!$client) {
            foreach ($this->clients as $client => $value) {
                break;
            }
        }
        $client = $this->clients[$client];
        $options = [];
        if ($username && $password) {
            $options['grant_type'] = 'password';
            $options['username'] = $username;
            $options['password'] = $password;
        } elseif ($refresh_token) {
            $options['grant_type'] = 'refresh_token';
            $options['refresh_token'] = $refresh_token;
        }
        $options['url'] = $client['provider_options']['domain'].$client['provider_options']['token'];
        $options['client_id'] = $client['client_id'];
        $options['client_secret'] = $client['client_secret'];
        
        $response = $this->sendRequest($options);
        $response = json_decode($response['RESPONSE'], 1);
        
        $this->session->set('access_token', $response['access_token']);
        $this->session->set('refresh_token', $response['refresh_token']);
        
        $response1 = new Response();
        $response1->headers->setCookie(new Cookie('access_token_active', 1, time() + $response['expires_in'], '/'));
        $response1->sendHeaders();
        
        return $response;
    }
    
    public function callFunction($function, $access_token = null, $client = null)
    {
        if (!$client) {
            foreach ($this->clients as $client => $value) {
                break;
            }
        }
        $client = $this->clients[$client];
        if ($access_token) {
            if (array_key_exists($function, $client['provider_options']['functions'])) {
                $options['client_id'] = $client['client_id'];
                $options['client_secret'] = $client['client_secret'];
                $options['url'] = $client['provider_options']['domain'].$client['provider_options']['functions'][$function];
                $options['access_token'] = $access_token;
                
                $response = $this->sendRequest($options);
                return json_decode($response['RESPONSE'], 1);
            }
        }
    }
}
