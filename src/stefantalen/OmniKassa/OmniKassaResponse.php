<?php

namespace stefantalen\OmniKassa;

use stefantalen\OmniKassa\OmniKassaOrder;

class OmniKassaResponse extends OmniKassaOrder
{
    /**
     * @var string $data
     */
    protected $data;
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
        $this->data = $postArray['Data'];
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
            $this->handleData($this->data);
        } else {
            throw new \UnexpectedValueException('This response is not valid');
        }
    }
    
    /**
     * Set the currency based in the code
     *
     * @param string $code The currency code
     *
     * @return OmniKassaResponse
     *
     * @throws \InvalidArgumentException if the currency is not available
     */
    public function setCurrencyCode($code)
    {
        if (!in_array($code, $this->currencyCodes)) {
            throw new \InvalidArgumentException(sprintf('The requested currency code "%s" is not available', $code));
        }
        $this->currency = $code;
        return $this;
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
        $this
            ->setCurrencyCode($data['currencyCode'])
            ->setAmount($data['amount'])
            ->setTransactionReference($data['transactionReference'])
            ->setOrderId($data['orderId'])
        ;
        if (!$this->testMode) {
            $this
                ->setMerchantId($data['merchantId'])
                ->setKeyVersion($data['keyVersion'])
            ;
        }
        if ($this instanceof OmniKassaResponse) {
            $this
                ->setResponseCode($data['responseCode'])
                ->setTransactionDateTime($data['transactionDateTime'])
            ;
        }
        
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
        return hash('sha256', utf8_encode($this->data. $this->secretKey));
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
        if ($this->currency !== '392') {
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
}
