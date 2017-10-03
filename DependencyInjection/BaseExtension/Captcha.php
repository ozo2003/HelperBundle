<?php

namespace Sludio\HelperBundle\DependencyInjection\BaseExtension;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Sludio\HelperBundle\Captcha\Configurator;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

class Captcha implements Extensionable
{
    protected $type;
    
    /**
     * List of available Oauth providers
     * @var array
     */
    protected static $supportedTypes = [
        'recaptcha' => Configurator\ReCaptchaConfigurator::class,
        'custom' => Configurator\CustomCaptchaConfigurator::class,
    ];
    
    public function getConfigurator($type)
    {
        if (!isset($this->configurators[$type])) {
            $class = self::$supportedTypes[$type];

            $this->configurators[$type] = new $class();
        }

        return $this->configurators[$type];
    }    
    
    public function configure(ContainerBuilder &$container)
    {
        $clientConfigurations = $container->getParameter('sludio_helper.captcha.clients');
        foreach ($clientConfigurations as $key => $clientConfig) {
            $tree = new TreeBuilder();
            $processor = new Processor();
            
            if (!isset($clientConfig['type'])) {
                throw new InvalidConfigurationException(sprintf(
                   'sludio_helper_captcha_client.clients.%s config entry is missing the "type" key.',
                   $key
               ));
            }

            $this->type = $clientConfig['type'];
            unset($clientConfig['type']);
            if (!isset(self::$supportedTypes[$this->type])) {
                $supportedKeys = array_keys(self::$supportedTypes);
                sort($supportedKeys);
                throw new InvalidConfigurationException(sprintf(
                    'sludio_helper_captcha_client.clients config "type" key "%s" is not supported. Supported: %s',
                    $this->type,
                    implode(', ', $supportedKeys)
                ));
            }
            
            $node = $tree->root('sludio_helper_captcha_client/clients/' . $key);
            $this->buildClientConfiguration($node);
            $config = $processor->process($tree->buildTree(), [$clientConfig]);
            $clientServiceKey = 'sludio_helper.captcha.client.'.$key;
            $service = [
                'key' => $clientServiceKey,
                'name' => ucfirst($key)
            ];
            $clientServiceKeys[$key] = $service;
            foreach ($config as $ckey => $cvalue) {
                $container->setParameter($clientServiceKey.'.'.$ckey, $cvalue);
            }
            $this->configureClient($container, $clientServiceKey);
        }
        $container->getDefinition('sludio_helper.captcha.registry')->replaceArgument(1, $clientServiceKeys);
    }
    
    public function buildClientConfiguration(NodeDefinition &$node)
    {        
        $optionsNode = $node->children();
        $this->getConfigurator($this->getType())->buildConfiguration($optionsNode);
        $optionsNode->end();
    }
    
    public function configureClient(ContainerBuilder $container, $clientServiceKey, array $options = [])
    {
        $this->getConfigurator($this->getType())->configureClient($container, $clientServiceKey, $options);
    }
    
    /**
     * Get the value of Type
     *
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of Type
     *
     * @param mixed type
     *
     * @return self
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }
    
}