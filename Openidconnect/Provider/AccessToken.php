<?php

namespace Sludio\HelperBundle\Openidconnect\Provider;

use Lcobucci\JWT\Parser;
use League\OAuth2\Client\Token\AccessToken as BaseAccessToken;

class AccessToken extends BaseAccessToken
{
    protected $idToken;

    protected $idTokenHint;

    public function __construct(array $options = [])
    {
        parent::__construct($options);

        if (!empty($this->values['id_token'])) {
            $this->idToken = (new Parser())->parse($this->values['id_token']);
            $this->idTokenHint = $this->values['id_token'];
            unset($this->values['id_token']);
        }
    }

    public function getIdToken()
    {
        return $this->idToken;
    }

    public function jsonSerialize()
    {
        $parameters = parent::jsonSerialize();
        if ($this->idToken) {
            $parameters['id_token'] = (string)$this->idToken;
        }

        return $parameters;
    }

    /**
     * @return mixed
     */
    public function getIdTokenHint()
    {
        return $this->idTokenHint;
    }
}
