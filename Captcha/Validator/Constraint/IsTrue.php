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
    public $message = "sludio_helper_captcha.recaptcha.validator.message";


    /**
     * {@inheritdoc}
     */
    public function getTargets()
    {
        return Constraint::PROPERTY_CONSTRAINT;
    }

    /**
     * {@inheritdoc}
     */
    public function validatedBy()
    {
        return "sludio_helper_captcha.recaptcha.true";
    }
}