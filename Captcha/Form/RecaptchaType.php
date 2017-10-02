<?php

namespace Sludio\HelperBundle\Captcha\Form;

use Sludio\HelperBundle\Router\LocaleResolver;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecaptchaType extends AbstractType
{
    /**
     * The reCAPTCHA Server URL's
     */
    const RECAPTCHA_API_SERVER    = "https://www.google.com/recaptcha/api";
    const RECAPTCHA_API_JS_SERVER = "https://www.google.com/recaptcha/api/js/recaptcha_ajax.js";

    /**
     * The public key
     *
     * @var String
     */
    protected $publicKey;

    /**
     * Use AJAX API
     *
     * @var Boolean
     */
    protected $ajax;

    /**
     * Language
     *
     * @var String
     */
    protected $localeResolver;


    /**
     * Construct.
     *
     * @param String    $publicKey            Recaptcha site key
     * @param Boolean   $ajax               Ajax status
     * @param String    $$localeResolver    Language or locale code
     */
    public function __construct($publicKey, $ajax, LocaleResolver $localeResolver)
    {
        $this->publicKey        = $publicKey;
        $this->ajax           = $ajax;
        $this->localeResolver = $localeResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_replace($view->vars, array(
            "sludio_helper_recaptcha_ajax"    => $this->ajax
        ));

        if (!$this->enabled) {
            return;
        }

        if (!isset($options["language"])) {
            $options["language"] = $this->localeResolver->resolve();
        }

        if (!$this->ajax) {
            $view->vars = array_replace($view->vars, array(
                "url_challenge" => sprintf("%s.js?hl=%s", self::RECAPTCHA_API_SERVER, $options["language"]),
                "public_key"      => $this->publicKey
            ));
        } else {
            $view->vars = array_replace($view->vars, array(
                "url_api"  => self::RECAPTCHA_API_JS_SERVER,
                "public_key" => $this->publicKey
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                "compound"      => false,
                "language"      => $this->localeResolver->resolve(),
                "public_key"      => null,
                "url_challenge" => null,
                "url_noscript"  => null,
                "attr"          => array(
                    "options" => array(
                        "theme"           => "light",
                        "type"            => "image",
                        "size"            => "normal",
                        "callback"        => null,
                        "expiredCallback" => null,
                        "defer"           => false,
                        "async"           => false
                    )
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return "sludio_helper_recaptcha";
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * Gets the Javascript source URLs.
     *
     * @param String $key The script name
     *
     * @return String The javascript source URL
     */
    public function getScriptURL($key)
    {
        return isset($this->scripts[$key]) ? $this->scripts[$key] : null;
    }

    /**
     * Gets the public key.
     *
     * @return String The javascript source URL
     */
    public function getpublicKey()
    {
        return $this->publicKey;
    }
}