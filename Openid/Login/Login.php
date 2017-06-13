<?php

namespace Sludio\HelperBundle\Openid\Login;

use Sludio\HelperBundle\Openid\Utils\LoginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Sludio\HelperBundle\DependencyInjection\ProviderFactory;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

class Login implements LoginInterface
{
    protected $client_name;
    protected $request;
    protected $generator;
    protected $redirect_route = 'homepage';
    protected $redirect_route_params = [];

    protected $api_key;
    protected $openid_url;
    protected $preg_check;
    protected $profile_url = false;
    protected $ns_mode = 'auth';
    protected $sreg_fields = 'email';
    protected $user_class;

    public function __construct($client_name, RequestStack $request_stack, ContainerInterface $container, UrlGeneratorInterface $generator)
    {
        $this->client_name = $client_name;
        $this->request = $request_stack->getCurrentRequest();
        $this->generator = $generator;

        $this->api_key = $container->getParameter($client_name.'.api_key');
        $this->openid_url = $container->getParameter($client_name.'.openid_url');
        $this->preg_check = $container->getParameter($client_name.'.preg_check');
        $this->user_class = $container->getParameter($client_name.'.user_class');
        if ($container->hasParameter($client_name.'.option.profile_url')) {
            $this->profile_url = $container->getParameter($client_name.'.option.profile_url');
        }
        $this->ns_mode = $container->getParameter($client_name.'.option.ns_mode', $this->ns_mode);

        if ($container->hasParameter($client_name.'.option.sreg_fields')) {
            $fields = $container->getParameter($client_name.'.option.sreg_fields', null);
            if ($fields && is_array($fields)) {
                $this->sreg_fields = implode(',', $fields);
            }
        }

        if ($container->hasParameter($client_name.'.option.redirect_route')) {
            $this->redirect_route = $container->getParameter($client_name.'.option.redirect_route');
        }

        if ($container->hasParameter($client_name.'.option.params')) {
            $this->redirect_route_params = $container->getParameter($client_name.'.option.params');
        }
    }

    /**
     * Validates a given URL, ensuring it contains the http or https URI Scheme.
     *
     * @param string $url
     *
     * @return bool
     */
    private function validateUrl($url)
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        return true;
    }

    /**
     * Build the Steam login URL.
     *
     * @param string $return A custom return to URL
     *
     * @return string
     */
    public function url($return = null, $altRealm = null)
    {
        $useHttps = !empty($_SERVER['HTTPS']) || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https');
        if (!is_null($return)) {
            if (!$this->validateUrl($return)) {
                throw new Exception('The return URL must be a valid URL with a URI Scheme or http or https.');
            }
        } else {
            if ($altRealm == null) {
                $return = ($useHttps ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
            } else {
                $return = $altRealm.$_SERVER['SCRIPT_NAME'];
            }
        }

        $params = array(
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.mode' => 'checkid_setup',
            'openid.return_to' => $return,
            'openid.realm' => $altRealm != null ? $altRealm : (($useHttps ? 'https' : 'http').'://'.$_SERVER['HTTP_HOST']),
            'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
        );

        if ($this->ns_mode === 'sreg') {
            $params['openid.ns.sreg'] = 'http://openid.net/extensions/sreg/1.1';
            $params['openid.sreg.required'] = $this->sreg_fields;
        }

        return $this->openid_url.'/'.$this->api_key.'/?'.http_build_query($params);
    }

    /**
     * Validates a Steam login request and returns the users Steam Community ID.
     *
     * @return string
     */
    public function validate($timeout = 30)
    {
        $response = null;
        $get = $this->request->query->all();

        try {
            $params = array(
                'openid.assoc_handle' => $get['openid_assoc_handle'],
                'openid.signed' => $get['openid_signed'],
                'openid.sig' => $get['openid_sig'],
                'openid.ns' => 'http://specs.openid.net/auth/2.0',
            );

            $signed = explode(',', $get['openid_signed']);

            foreach ($signed as $item) {
                $val = $get['openid_'.str_replace('.', '_', $item)];
                $params['openid.'.$item] = get_magic_quotes_gpc() ? stripslashes($val) : $val;
            }

            $params['openid.mode'] = 'check_authentication';

            $data = http_build_query($params);

            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Accept-language: en\r\n".
                    "Content-type: application/x-www-form-urlencoded\r\n".
                    'Content-Length: '.strlen($data)."\r\n",
                    'content' => $data,
                    'timeout' => $timeout,
                    ),
            ));

            $result = file_get_contents($this->openid_url.'/'.$this->api_key, false, $context);

            preg_match($this->preg_check, urldecode($get['openid_claimed_id']), $matches);

            $openID = $matches[1];

            $response = preg_match("#is_valid\s*:\s*true#i", $result) == 1 ? $openID : null;
        } catch (Exception $e) {
            $response = null;
        }

        return $response;
    }

    private function getData($openID = null)
    {
        if ($openID) {
            $data = file_get_contents($this->profile_url.$openID);
            $json = json_decode($data, true);

            return new $this->user_class($json['response'], $openID);
        }

        return null;
    }

    public function fetchUser()
    {
        $user = $this->validate();
        if ($user !== null) {
            if ($this->profile_url === false) {
                $user = new $this->user_class($this->request->query->all(), $user);
            } else {
                $user = $this->getData($user);
            }
        }

        if ($user === null) {
            throw new \Exception('The login request timed out or was invalid', 400);
        }

        return $user;
    }

    public function redirect()
    {
        $providerFactory = new ProviderFactory($this->generator);
        $redirectUri = $providerFactory->generateUrl($this->redirect_route, $this->redirect_route_params);

        return new RedirectResponse($this->url($redirectUri));
    }
}
