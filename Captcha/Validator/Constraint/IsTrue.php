<?php

namespace Sludio\HelperBundle\Captcha\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target("PROPERTY")
 */
class IsTrue extends Constraint
{
    /**
     * The reCAPTCHA validation message
     */
    public $message = 'sludio_helper.captcha.recaptcha.validator.message';

    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return Constraint::PROPERTY_CONSTRAINT;
    }
}
