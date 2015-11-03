<?php

namespace stefantalen\OmniKassa;

class OmniKassaResponse
{
    /**
     * @var string $rawData
     */
    protected $rawData;
    /**
     * @var string $seal
     */
    protected $seal;
    
    /**
     * @var string $responseCode
     */
    protected $responseCode;
    
    /**
     * @var \DateTime $transactionDateTime
     */
    protected $transactionDateTime;

    /** @var  OmniKassaOrder */
    protected $order;

    /**
     * @var string
     */
    protected $secretKey;

    /**
     * @var int
     */
    protected $keyVersion;

    /**
     * Handle the POST array
     *
     * @param array $postArray The POST array
     *
     * @throws \InvalidArgumentException if the Data key does not exist
     * @throws \InvalidArgumentException if the Seal key does not exist
     *
     */
    public function __construct($postArray = array())
    {
        // Check if the required fields are present in the array
        if (!isset($postArray['Data'])) {
            throw new \InvalidArgumentException('The array should contain a "Data" key');
        }
        if (!isset($postArray['Seal'])) {
            throw new \InvalidArgumentException('The array should contain a "Seal" key');
        }
        $this->rawData = $postArray['Data'];
        $this->seal = $postArray['Seal'];
    }
    
    /**
     * Validate the response
     *
     * @throws \UnexpectedValueException if the response is not valid
     *
     */
    public function validate()
    {
        if ($this->seal === $this->getSeal()) {
            $this->handleData($this->rawData);
        } else {
            throw new \UnexpectedValueException('This response is not valid');
        }
    }

    /**
     * Convert the Data string
     *
     * @param string $dataString The Data string provided by OmniKassa
     */
    protected function handleData($dataString)
    {
        $dataArray = explode('|', $dataString);
        $data = array();
        foreach ($dataArray as $d) {
            list($k, $v) = explode('=', $d);
            $data[$k] = $v;
        }

        $this->order = OmniKassaOrder::fromData($data);

        $this
            ->setKeyVersion($data['keyVersion'])
            ->setResponseCode($data['responseCode'])
            ->setTransactionDateTime($data['transactionDateTime'])
        ;

    }
    
    /**
     * Get the seal
     *
     * @return string
     *
     * @throws \BadMethodCallException if no secret key is specified
     */
    protected function getSeal()
    {
        if (null === $this->secretKey) {
            throw new \BadMethodCallException('A secret key must be provided');
        }
        return hash('sha256', utf8_encode($this->rawData. $this->secretKey));
    }
    
    /**
     * Set the amount of the order
     *
     * @param string $amount
     *
     * @return OmniKassaResponse
     *
     * @throws \InvalidArgumentException if the amount does not consist of numerics
     */
    public function setAmount($amount)
    {
        // Check if the amount is a valid value
        if (!preg_match('/^[0-9]*$/', $amount)) {
            throw new \InvalidArgumentException('The amount can only contain numerics');
        }
        
        // Add decimals to value the currency is not Japanese Yen
        if ($this->currencyId !== '392') {
            if ($amount >= 100) {
                $amount = preg_replace('/^([0-9]*)([0-9]{2})$/', '$1.$2', $amount);
            } else {
                $amount = '0.'. $amount;
            }
        }
        $this->amount = $amount;
        return $this;
    }
    
    /**
     * Set the responseCode
     *
     * @param string $code The response code given by OmniKassa
     *
     * @return OmniKassaResponse
     */
    protected function setResponseCode($code)
    {
        $this->responseCode = $code;
        return $this;
    }
    
    
    /**
     * Get the responseCode
     *
     * @return string
     */
    public function getResponseCode()
    {
        return $this->responseCode;
    }
    
    /**
     * Set the transaction date and time
     *
     * @param string $datetime The transaction date time string in ISO 8601 format
     *
     * @return OmniKassaResponse
     *
     * @throws \InvalidArgumentException if the provided string does not match the ISO 8601 format
     */
    public function setTransactionDateTime($datetime)
    {
        if (!preg_match('/(\d{4})-(\d{2})-(\d{2})T(\d{2})\:(\d{2})\:(\d{2})[+-](\d{2})\:(\d{2})/', $datetime)) {
            throw new \InvalidArgumentException('The transactionDateTime should be in ISO 8601 format');
        }
        $this->transactionDateTime = \DateTime::createFromFormat(\DateTime::ISO8601, $datetime);
        return $this;
    }
    
    /**
     * Get the transaction date and time
     *
     * @return \DateTime
     */
    public function getTransactionDateTime()
    {
        return $this->transactionDateTime;
    }

    /**
     * Set the secret key provided by OmniKassa
     *
     * @param string $key The secret key
     *
     * @return OmniKassaResponse

     * @throws \BadMethodCallException if test mode is enabled
     */
    public function setSecretKey($key)
    {
//        if ($this->isTestRequest) {
//            throw new \BadMethodCallException('The secret key cannot be set in a test request');
//        }
        $this->secretKey = $key;
        return $this;
    }

    /**
     * The version number of the secret key, can be found on the OmniKassa website
     *
     * @param string $version The version number
     *
     * @return OmniKassaResponse
     *
     * @throws \BadMethodCallException if test mode is enabled
     * @throws \LengthException if the key is longer than 10 characters
     */
    public function setKeyVersion($version)
    {
//        if ($this->isTestRequest) {
//            throw new \BadMethodCallException('The keyVersion cannot be set in a test request');
//        }
        if (strlen($version) > 10) {
            throw new \LengthException('The keyVersion has a maximum of 10 characters');
        }
        $this->keyVersion = $version;
        return $this;
    }

    /**
     * Get the secret key
     *
     * @return string
     */
    public function getSecretKey()
    {
        return $this->secretKey;
    }

}
