<?php

namespace Sludio\HelperBundle\Openidconnect\Validator;

use Lcobucci\JWT\Token;

class ValidatorChain
{
    /**
     * @var array
     */
    protected $validators = [];

    /**
     * @var array
     */
    protected $messages = [];

    /**
     * @param ValidInterface[] $validators
     *
     * @return $this
     */
    public function setValidators(array $validators)
    {
        $this->validators = [];

        foreach ($validators as $validator) {
            $this->addValidator($validator);
        }

        return $this;
    }

    /**
     * @param string         $claim
     * @param ValidInterface $validator
     *
     * @return $this
     */
    public function addValidator(ValidInterface $validator)
    {
        $this->validators[$validator->getName()] = $validator;

        return $this;
    }

    /**
     * @param array $data
     * @param Token $token
     *
     * @return bool
     */
    public function validate(array $data, Token $token)
    {
        $valid = true;
        foreach ($this->validators as $claim => $validator) {
            if ($validator->isRequired() && false === $token->hasClaim($claim)) {
                $valid = false;
                $this->messages[$claim] = sprintf("Missing required value for claim %s", $claim);
                continue;
            } elseif (empty($data[$claim]) || false === $token->hasClaim($claim)) {
                continue;
            }

            if (!$validator->isValid($data[$claim], $token->getClaim($claim))) {
                $valid = false;
                $this->messages[$claim] = $validator->getMessage();
            }
        }

        return $valid;
    }

    /**
     * @param $name
     *
     * @return bool
     */
    public function hasValidator($name)
    {
        return array_key_exists($name, $this->validators);
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public function getValidator($name)
    {
        return $this->validators[$name];
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }
}
