<?php

namespace Sludio\HelperBundle\Captcha\Validator\Constraint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ValidatorException;

class IsTrueValidator extends ConstraintValidator
{
    /**
     * The reCAPTCHA server URL's
     */
    const RECAPTCHA_VERIFY_SERVER = 'https://www.google.com';

    /**
     * Recaptcha Private Key
     *
     * @var string
     */
    protected $secretKey;

    /**
     * Request Stack
     *
     * @var Request
     */
    protected $request;

    /**
     * HTTP Proxy informations
     * @var array
     */
    protected $httpProxy;

    /**
     * Enable serverside host check.
     *
     * @var Boolean
     */
    protected $verifyHost;

    protected $validate;

    /**
     * Construct.
     *
     * @param String       $secretKey
     * @param array        $httpProxy
     * @param Boolean      $verifyHost
     * @param RequestStack $requestStack
     * @param bool         $validate
     */
    public function __construct($secretKey, array $httpProxy, $verifyHost, RequestStack $requestStack, $validate = true)
    {
        $this->secretKey = $secretKey;
        $this->request = $requestStack->getCurrentRequest();
        $this->httpProxy = $httpProxy;
        $this->verifyHost = $verifyHost;
        $this->validate = $validate;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Symfony\Component\Validator\Exception\ValidatorException
     */
    public function validate($value, Constraint $constraint)
    {
        // define variable for recaptcha check answer.
        $remoteip = $this->request->getClientIp();
        $response = $this->request->get('g-recaptcha-response');

        $isValid = $this->checkAnswer($this->secretKey, $remoteip, $response);

        if ($isValid['success'] !== true) {
            $this->context->addViolation($constraint->message);
            // Perform server side hostname check
        } elseif ($this->verifyHost && $isValid['hostname'] !== $this->request->getHost()) {
            $this->context->addViolation($constraint->invalidHostMessage);
        }
    }

    /**
     * Calls an HTTP POST function to verify if the user's guess was correct.
     *
     * @param String $secretKey
     * @param String $remoteip
     * @param String $response
     *
     * @return array|bool|mixed
     * @throws ValidatorException When missing remote ip
     */
    private function checkAnswer($secretKey, $remoteip, $response)
    {
        if ($this->validate === false) {
            return ['success' => true];
        }

        if ($remoteip === null || $remoteip === '') {
            throw new ValidatorException('sludio_helper.captcha.recaptcha.validator.remote_ip');
        }

        // discard spam submissions
        if ($response === null || '' === $response) {
            return false;
        }

        $input = [
            'secret' => $secretKey,
            'remoteip' => $remoteip,
            'response' => $response,
        ];
        $response = (string)$this->httpGet(self::RECAPTCHA_VERIFY_SERVER, '/recaptcha/api/siteverify', $input);

        return json_decode($response, true);
    }

    /**
     * Submits an HTTP POST to a reCAPTCHA server.
     *
     * @param String $host
     * @param String $path
     * @param array  $data
     *
     * @return array response
     */
    private function httpGet($host, $path, $data)
    {
        $host = sprintf('%s%s?%s', $host, $path, http_build_query($data, null, '&'));

        $context = $this->getResourceContext();

        return file_get_contents($host, false, $context);
    }

    /**
     * Resource context.
     *
     * @return resource context for HTTP Proxy.
     */
    private function getResourceContext()
    {
        if (null === $this->httpProxy['host'] || null === $this->httpProxy['port']) {
            return null;
        }

        $options = [];
        $protocols = [
            'http',
            'https',
        ];
        foreach ($protocols as $protocol) {
            $options[$protocol] = [
                'method' => 'GET',
                'proxy' => sprintf('tcp://%s:%s', $this->httpProxy['host'], $this->httpProxy['port']),
                'request_fulluri' => true,
            ];

            if (null !== $this->httpProxy['auth']) {
                $options[$protocol]['header'] = sprintf('Proxy-Authorization: Basic %s', base64_encode($this->httpProxy['auth']));
            }
        }

        return stream_context_create($options);
    }
}
