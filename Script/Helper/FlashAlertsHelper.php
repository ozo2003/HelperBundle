<?php

namespace Sludio\HelperBundle\Script\Helper;

use Sludio\HelperBundle\Script\Model\AlertPublisher;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Templating\EngineInterface;

class FlashAlertsHelper extends Helper
{
    private $templating;
    private $alertPublisher;
    private $options = [];

    public function __construct(EngineInterface $templating, AlertPublisher $alertPublisher, $template, $styles, $scripts)
    {
        $this->templating = $templating;
        $this->alertPublisher = $alertPublisher;
        $this->options = [
            'use_styles' => $styles,
            'use_scripts' => $scripts,
            'template' => $template
        ];
    }

    public function renderFlashAlerts(array $options = [])
    {
        $options = $this->resolveOptions($options);

        return $this->templating->render($options['template'], $options);
    }

    private function resolveOptions(array $options = [])
    {
        $this->options['alert_publisher'] = $this->alertPublisher;

        return array_merge($this->options, $options);
    }

    public function getName()
    {
        return 'sludio_helper.templating.alerts_helper';
    }
}
