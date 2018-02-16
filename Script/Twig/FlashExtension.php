<?php

namespace Sludio\HelperBundle\Script\Twig;

use Sludio\HelperBundle\Script\Helper\FlashAlertsHelper;

class FlashExtension extends \Twig_Extension
{
    use TwigTrait;

    private $publisher;

    /**
     * @var FlashAlertsHelper
     */
    private $helper;

    public function __construct($shortFunctions, $publisher, $helper)
    {
        $this->shortFunctions = $shortFunctions;
        $this->publisher = $publisher;
        $this->helper = $helper;
    }

    public function getAlertPublisher()
    {
        return $this->publisher;
    }

    public function renderFlashAlerts(array $options = [])
    {
        return $this->helper->renderFlashAlerts($options);
    }

    public function getFunctions()
    {
        $input = [
            'get_alert_publisher' => [
                $this,
                'getAlertPublisher',
                ['is_safe' => ['html']],
            ],
            'render_flash_alerts' => [
                $this,
                'renderFlashAlerts',
                ['is_safe' => ['html']],
            ],
        ];

        return $this->makeArray($input, 'function');
    }
}
