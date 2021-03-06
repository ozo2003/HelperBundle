<?php

namespace Sludio\HelperBundle\DependencyInjection\Component;

use Sludio\HelperBundle\Captcha\Configurator;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class Captcha implements ExtensionInterface
{
    /**
     * List of available Oauth providers
     * @var array
     */
    protected static $supportedTypes = [
        'recaptcha_v2' => Configurator\ReCaptchaConfigurator::class,
        'custom' => Configurator\CustomCaptchaConfigurator::class,
    ];
    public $configurators = [];
    protected $type;
    protected $alias;
    protected $usedTypes = [];

    public function configure(ContainerBuilder $container, $alias)
    {
        $this->alias = $alias.'.captcha';
        $clientConfigurations = $container->getParameter($this->alias.'.clients');
        /** @var $clientConfigurations array */
        foreach ($clientConfigurations as $key => $clientConfig) {
            $tree = new TreeBuilder();
            $processor = new Processor();

            if (!isset($clientConfig['type'])) {
                throw new InvalidConfigurationException(sprintf('sludio_helper_captcha_client.clients.%s config entry is missing the "type" key.', $key));
            }

            $this->type = $clientConfig['type'];
            unset($clientConfig['type']);
            if (!isset(self::$supportedTypes[$this->type])) {
                $supportedKeys = array_keys(self::$supportedTypes);
                sort($supportedKeys);
                throw new InvalidConfigurationException(sprintf('sludio_helper_captcha_client.clients config "type" key "%s" is not supported. Supported: %s', $this->type, implode(', ', $supportedKeys)));
            }

            if (!\in_array($this->type, $this->usedTypes, true)) {
                $this->usedTypes[] = $this->type;
            } else {
                throw new InvalidConfigurationException(sprintf('sludio_helper_captcha_client.clients config "type" key "%s" is already in use. Only one occurence by type is allowed', $this->type));
            }

            $node = $tree->root('sludio_helper_captcha_client/clients/'.$key);
            $this->buildClientConfiguration($node);
            $config = $processor->process($tree->buildTree(), [$clientConfig]);
            $clientServiceKey = $this->alias.'.client.'.$key;
            foreach ($config as $ckey => $cvalue) {
                $container->setParameter($clientServiceKey.'.'.$ckey, $cvalue);
            }
            $this->configureClient($container, $clientServiceKey);
        }
    }

    public function buildClientConfiguration(NodeDefinition $node)
    {
        $node->addDefaultsIfNotSet();
        $optionsNode = $node->children();
        $this->getConfigurator($this->getType())->buildConfiguration($optionsNode);
        $optionsNode->end();
    }

    public function getConfigurator($type)
    {
        if (!isset($this->configurators[$type])) {
            $class = self::$supportedTypes[$type];

            $this->configurators[$type] = new $class();
        }

        return $this->configurators[$type];
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function configureClient(ContainerBuilder $container, $clientServiceKey, array $options = [])
    {
        $this->getConfigurator($this->getType())->configureClient($container, $clientServiceKey, $options);
    }

}
