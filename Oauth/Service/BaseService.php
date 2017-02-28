<?php

namespace Sludio\HelperBundle\Oauth\Service;

use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Request;

class BaseService
{
    public function sendRequest(array $options = [])
    {
        $headr = array();
        $headr[] = 'Content-length: 0';
        $headr[] = 'Content-type: application/json';
        
        $ch = curl_init($options['url']);
        unset($options['url']);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headr);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        $information = curl_getinfo($ch);
        
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        ld($information);

        $curl_error_code = curl_errno($ch);
        curl_close($ch);
        return array('CURL_CODE' => $curl_error_code, 'RESPONSE' => $result);
    }
    
    public function __construct($clients){
        $this->clients = $clients;
    }
    
    public function getAccessToken($client, $username, $password){
        $client = $this->clients[$client];
    }
}
