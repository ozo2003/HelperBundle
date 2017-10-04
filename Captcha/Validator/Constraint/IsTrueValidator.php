<?php

namespace Sludio\HelperBundle\Captcha\Validator\Constraint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\ValidatorException;

class IsTrueValidator extends ConstraintValidator
{
    /**
     * The reCAPTCHA server URL's
     */
    const RECAPTCHA_VERIFY_SERVER = "https://www.google.com";

    /**
     * Recaptcha Private Key
     *
     * @var Boolean
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
     * @var Array
     */
    protected $httpProxy;

    /**
     * Enable serverside host check.
     *
     * @var Boolean
     */
    protected $verifyHost;

    /**
     * Construct.
     *
     * @param String       $secretKey
     * @param Array        $httpProxy
     * @param Boolean      $verifyHost
     */
    public function __construct($secretKey, array $httpProxy, $verifyHost)
    {
        $this->secretKey  = $secretKey;
        $this->request    = Request::createFromGlobals();
        $this->httpProxy  = $httpProxy;
        $this->verifyHost = $verifyHost;
    }

    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        // define variable for recaptcha check answer.
        $remoteip = $this->request->getClientIp();
        $response = $this->request->get("g-recaptcha-response");

        $isValid = $this->checkAnswer($this->secretKey, $remoteip, $response);

        if ($isValid["success"] !== true) {
            $this->context->addViolation($constraint->message);
            // Perform server side hostname check
        } elseif ($this->verifyHost && $isValid["hostname"] !== $this->request->getHost()) {
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
     * @throws ValidatorException When missing remote ip
     *
     * @return Boolean
     */
    private function checkAnswer($secretKey, $remoteip, $response)
    {
        if ($remoteip == null || $remoteip == "") {
            throw new ValidatorException("sludio_helper_captcha.recaptcha.validator.remote_ip");
        }

        // discard spam submissions
        if ($response == null || strlen($response) == 0) {
            return false;
        }

        $response = $this->httpGet(
            self::RECAPTCHA_VERIFY_SERVER, "/recaptcha/api/siteverify",
            array(
                "secret" => $secretKey,
                "remoteip" => $remoteip,
                "response" => $response
            )
        );

        return json_decode($response, true);
    }

    /**
     * Submits an HTTP POST to a reCAPTCHA server.
     *
     * @param String $host
     * @param String $path
     * @param Array  $data
     *
     * @return Array response
     */
    private function httpGet($host, $path, $data)
    {
        $host = sprintf("%s%s?%s", $host, $path, http_build_query($data, null, "&"));

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
        if (null === $this->httpProxy["host"] || null === $this->httpProxy["port"]) {
            return null;
        }

        $options = array();
        foreach (array("http", "https") as $protocol) {
            $options[$protocol] = array(
                "method" => "GET",
                "proxy" => sprintf("tcp://%s:%s", $this->httpProxy["host"], $this->httpProxy["port"]),
                "request_fulluri" => true
            );

            if (null !== $this->httpProxy["auth"]) {
                $options[$protocol]["header"] = sprintf("Proxy-Authorization: Basic %s", base64_encode($this->httpProxy["auth"]));
            }
        }

        return stream_context_create($options);
    }
}