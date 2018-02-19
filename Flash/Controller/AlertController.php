<?php

namespace Sludio\HelperBundle\Flash\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AlertController extends Controller
{
    public function displayAlertsAction($useStyles = true, $useScripts = true)
    {
        $options = [
            'alert_publisher' => $this->get('sludio_helper.alert.publisher'),
            'alert_use_styles' => $useStyles,
            'alert_use_scripts' => $useScripts,
        ];

        return $this->render('SludioHelperBundle:Flash:layout.html.twig', $options);
    }
}
