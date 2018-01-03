<?php

namespace Sludio\HelperBundle\Openid\Login;

use Exception;
use Sludio\HelperBundle\DependencyInjection\ProviderFactory;
use Sludio\HelperBundle\Openid\Component\Loginable;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class Login implements Loginable
{
    protected $request;

    public $requestStack;

    protected $generator;
    protected $redirectRoute;
    protected $redirectRouteParams = [];

    protected $apiKey;
    protected $openidUrl;
    protected $pregCheck;
    protected $profileUrl = false;
    protected $nsMode = 'auth';
    protected $sregFields = 'email';
    protected $userClass;

    public function __construct($clientName, RequestStack $requestStack, ContainerBuilder $container, UrlGeneratorInterface $generator)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->requestStack = $requestStack;
        $this->generator = $generator;

        $this->apiKey = $container->getParameter($clientName.'.api_key');
        $this->openidUrl = $container->getParameter($clientName.'.openid_url');
        $this->pregCheck = $container->getParameter($clientName.'.preg_check');
        $this->userClass = $container->getParameter($clientName.'.user_class');
        if ($container->hasParameter($clientName.'.option.profile_url')) {
            $this->profileUrl = $container->getParameter($clientName.'.option.profile_url');
        }
        $this->nsMode = $container->getParameter($clientName.'.option.ns_mode') ?: $this->nsMode;

        if ($container->hasParameter($clientName.'.option.sreg_fields')) {
            $fields = $container->getParameter($clientName.'.option.sreg_fields');
            if ($fields && \is_array($fields)) {
                $this->sregFields = implode(',', $fields);
            }
        }

        if ($container->hasParameter($clientName.'.redirect_route')) {
            $this->redirectRoute = $container->getParameter($clientName.'.redirect_route');
        }

        if ($container->hasParameter($clientName.'.option.params')) {
            $this->redirectRouteParams = $container->getParameter($clientName.'.option.params');
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

    private function getParams($return, $realm)
    {
        $params = [
            'openid.ns' => 'http://specs.openid.net/auth/2.0',
            'openid.mode' => 'checkid_setup',
            'openid.return_to' => $return,
            'openid.realm' => $realm,
            'openid.identity' => 'http://specs.openid.net/auth/2.0/identifier_select',
            'openid.claimed_id' => 'http://specs.openid.net/auth/2.0/identifier_select',
        ];

        if ($this->nsMode === 'sreg') {
            $params['openid.ns.sreg'] = 'http://openid.net/extensions/sreg/1.1';
            $params['openid.sreg.required'] = $this->sregFields;
        }

        return $params;
    }

    /**
     * Build the OpenID login URL.
     *
     * @param string      $return A custom return to URL
     *
     * @param string|null $altRealm
     *
     * @return string
     * @throws Exception
     */
    public function urlPath($return = null, $altRealm = null) //HTTP_X_FORWARDED_PROTO
    {
        $useHttps = $this->request->server->get('HTTPS') || ($this->request->server->get('HTTP_X_FORWARDED_PROTO') && $this->request->server->get('HTTP_X_FORWARDED_PROTO') === 'https');
        $realm = $altRealm ?: ($useHttps ? 'https' : 'http').'://'.$this->request->server->get('HTTP_HOST');

        if (null !== $return) {
            if (!$this->validateUrl($return)) {
                throw new Exception('error_oauth_invalid_return_url');
            }
        } else {
            $return = $realm.$this->request->server->get('SCRIPT_NAME');
        }

        return $this->openidUrl.'/'.$this->apiKey.'/?'.http_build_query($this->getParams($return, $realm));
    }

    /**
     * Validates a OpenID login request and returns the users OpenID.
     *
     * @param int $timeout
     *
     * @return string
     */
    public function validate($timeout = 30)
    {
        $response = null;
        $get = $this->request->query->all();

        try {
            $params = [
                'openid.assoc_handle' => $get['openid_assoc_handle'],
                'openid.signed' => $get['openid_signed'],
                'openid.sig' => $get['openid_sig'],
                'openid.ns' => 'http://specs.openid.net/auth/2.0',
            ];

            $signed = explode(',', $get['openid_signed']);

            foreach ($signed as $item) {
                $val = $get['openid_'.str_replace('.', '_', $item)];
                $params['openid.'.$item] = get_magic_quotes_gpc() ? stripslashes($val) : $val;
            }

            $params['openid.mode'] = 'check_authentication';

            $data = http_build_query($params);

            $context = stream_context_create([
                'http' => [
                    'method' => 'POST',
                    'header' => "Accept-language: en\r\n"."Content-type: application/x-www-form-urlencoded\r\n".'Content-Length: '.\strlen($data)."\r\n",
                    'content' => $data,
                    'timeout' => $timeout,
                ],
            ]);

            $result = file_get_contents($this->openidUrl.'/'.$this->apiKey, false, $context);

            preg_match($this->pregCheck, urldecode($get['openid_claimed_id']), $matches);

            $openID = (\is_array($matches) && isset($matches[1])) ? $matches[1] : null;

            $response = preg_match("#is_valid\s*:\s*true#i", $result) === 1 ? $openID : null;
        } catch (Exception $e) {
            $response = null;
        }

        return $response;
    }

    private function getData($openID = null)
    {
        if ($openID) {
            $data = file_get_contents($this->profileUrl.$openID);
            $json = json_decode($data, true);

            return new $this->userClass($json['response'], $openID);
        }

        return null;
    }

    public function fetchUser()
    {
        $user = $this->validate();
        if ($user !== null) {
            if ($this->profileUrl === false) {
                $user = new $this->userClass($this->request->query->all(), $user);
            } else {
                $user = $this->getData($user);
            }
        }

        if ($user === null) {
            throw new Exception('error_oauth_login_invalid_or_timed_out');
        }

        return $user;
    }

    public function redirect()
    {
        $providerFactory = new ProviderFactory($this->generator, $this->requestStack);
        $redirectUri = $providerFactory->generateUrl($this->redirectRoute, $this->redirectRouteParams);

        return new RedirectResponse($this->urlPath($redirectUri));
    }
}
