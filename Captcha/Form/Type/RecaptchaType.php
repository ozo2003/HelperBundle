<?php

namespace Sludio\HelperBundle\Captcha\Form\Type;

use Sludio\HelperBundle\Captcha\Router\LocaleResolver;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecaptchaType extends AbstractType
{
    /**
     * The reCAPTCHA Server URL's
     */
    const RECAPTCHA_API_SERVER = 'https://www.google.com/recaptcha/api';
    const RECAPTCHA_API_JS_SERVER = 'https://www.google.com/recaptcha/api/js/recaptcha_ajax.js';
    public $scripts = [];

    /**
     * The public key
     *
     * @var string
     */
    protected $publicKey;

    /**
     * Use AJAX API
     *
     * @var bool
     */
    protected $ajax;

    /**
     * Language
     *
     * @var LocaleResolver
     */
    protected $localeResolver;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Construct.
     *
     * @param String                $publicKey      Recaptcha site key
     * @param Boolean               $ajax           Ajax status
     * @param LocaleResolver|String $localeResolver Language or locale code
     * @param array                 $options
     */
    public function __construct($publicKey, $ajax, LocaleResolver $localeResolver, array $options = [])
    {
        $this->publicKey = $publicKey;
        $this->ajax = $ajax;
        $this->localeResolver = $localeResolver;
        $this->options = $options;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars = array_replace($view->vars, [
            'recaptcha_ajax' => $this->ajax,
            'public_key' => $this->publicKey,
        ]);

        if (!$this->ajax) {
            $view->vars = array_replace($view->vars, [
                'url_challenge' => sprintf('%s.js?hl=%s', self::RECAPTCHA_API_SERVER, $options['language']),
            ]);
        } else {
            $view->vars = array_replace($view->vars, [
                'url_api' => self::RECAPTCHA_API_JS_SERVER,
            ]);
        }

        $baseOptions = [
            'compound',
            'url_challenge',
            'url_noscript',
        ];

        $attributes = [
            'theme',
            'type',
            'size',
            'callback',
            'expiredCallback',
            'defer',
            'async',
        ];

        if (!empty($this->options)) {
            foreach ($baseOptions as $option) {
                if (isset($this->options[$option])) {
                    $view->vars[$option] = $this->options[$option];
                }
            }
            foreach ($attributes as $attribute) {
                if (isset($this->options[$attribute])) {
                    $view->vars['attr']['options'][$attribute] = $this->options[$attribute];
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     * @throws \Symfony\Component\OptionsResolver\Exception\AccessException
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'compound' => false,
            'language' => $this->localeResolver->resolve(),
            'public_key' => null,
            'url_challenge' => null,
            'url_noscript' => null,
            'attr' => [
                'options' => [
                    'theme' => 'light',
                    'type' => 'image',
                    'size' => 'normal',
                    'callback' => null,
                    'expiredCallback' => null,
                    'defer' => false,
                    'async' => false,
                ],
            ],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sludio_helper_captcha_recaptcha';
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
