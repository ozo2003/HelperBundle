<?php

namespace Sludio\HelperBundle\Script\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class AlertController extends Controller
{
    public function displayAlertsAction($isAddStyles = true, $isAddJsAlertClose = true)
    {
        $options = [
            'alert_publisher' => $this->get('sludio_helper.alert.publisher'),
            'alert_use_styles' => $isAddStyles,
            'alert_use_scripts' => $isAddJsAlertClose,
        ];

        return $this->render('SludioHelperBundle:Script:layout.html.twig', $options);
    }
}
