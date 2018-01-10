<?php

namespace Sludio\HelperBundle\Openidconnect\Specification;

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
     * @param AbstractSpecification[] $validators
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
     * @param AbstractSpecification $validator
     *
     * @return $this
     * @internal param string $claim
     */
    public function addValidator(AbstractSpecification $validator)
    {
        $this->validators[$validator->getName()] = $validator;

        return $this;
    }

    /**
     * @param array $data
     * @param Token $token
     *
     * @return bool
     * @throws \OutOfBoundsException
     */
    public function validate(array $data, Token $token)
    {
        $valid = true;
        foreach ($this->validators as $claim => $validator) {
            if ($token->hasClaim($claim) === false) {
                if ($validator->isRequired()) {
                    $valid = false;
                    $this->messages[$claim] = sprintf('Missing required value for claim %s', $claim);
                    continue;
                }

                if (empty($data[$claim])) {
                    continue;
                }
            } else {
                if (isset($data[$claim]) && !$validator->isSatisfiedBy($data[$claim], $token->getClaim($claim))) {
                    $valid = false;
                    $this->messages[$claim] = $validator->getMessage();
                }
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
