<?php

namespace Sludio\HelperBundle\Openid\Login;

use Sludio\HelperBundle\DependencyInjection\ProviderFactory;
use Sludio\HelperBundle\Openid\Component\Loginable;
use Sludio\HelperBundle\Script\Security\Exception\ErrorException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Sludio\HelperBundle\Script\Utils\Helper;
use Symfony\Component\HttpFoundation\Request;

class Login implements Loginable
{
    /**
     * @var RequestStack
     */
    public $requestStack;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var UrlGeneratorInterface
     */
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
    protected $fields = [];

    public function __construct($clientName, RequestStack $requestStack, ContainerInterface $interface, UrlGeneratorInterface $generator)
    {
        $this->request = $requestStack->getCurrentRequest();
        $this->requestStack = $requestStack;
        $this->generator = $generator;

        $container = $interface;
        $this->setInputs($clientName, $container);
        $this->nsMode = $container->getParameter($clientName.'.option.ns_mode') ?: $this->nsMode;
        $this->setParameters($clientName, $container);

        if (!empty($this->fields) && \is_array($this->fields)) {
            $this->sregFields = implode(',', $this->fields);
        }
    }

    private function setInputs($clientName, ContainerInterface $container)
    {
        $inputs = [
            'apiKey' => $clientName.'.api_key',
            'openidUrl' => $clientName.'.openid_url',
            'pregCheck' => $clientName.'.preg_check',
            'userClass' => $clientName.'.user_class',
        ];

        foreach ($inputs as $key => $input) {
            $this->{$key} = $container->getParameter($input);
        }
    }

    private function setParameters($clientName, ContainerInterface $container)
    {
        $parameters = [
            'profileUrl' => $clientName.'.option.profile_url',
            'redirectRoute' => $clientName.'.redirect_route',
            'redirectRouteParams' => $clientName.'.option.params',
            'fields' => $clientName.'.option.sreg_fields',
        ];

        foreach ($parameters as $key => $param) {
            if ($container->hasParameter($param)) {
                $this->{$key} = $container->getParameter($param);
            }
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
     * @throws ErrorException
     */
    public function urlPath($return = null, $altRealm = null) //HTTP_X_FORWARDED_PROTO
    {
        $realm = $altRealm ?: Helper::getSchema($this->request).$this->request->server->get('HTTP_HOST');

        if (null !== $return) {
            if (!$this->validateUrl($return)) {
                throw new ErrorException('error_oauth_invalid_return_url');
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
     * @return string|null
     */
    public function validate($timeout = 30)
    {
        $get = $this->request->query->all();

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

        preg_match($this->pregCheck, urldecode($get['openid_claimed_id']), $matches);
        $openID = (\is_array($matches) && isset($matches[1])) ? $matches[1] : null;

        return preg_match("#is_valid\s*:\s*true#i", file_get_contents($this->openidUrl.'/'.$this->apiKey, false, $context)) === 1 ? $openID : null;
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
            throw new ErrorException('error_oauth_login_invalid_or_timed_out');
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
