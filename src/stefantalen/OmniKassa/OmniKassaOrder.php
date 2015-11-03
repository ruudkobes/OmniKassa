<?php

namespace stefantalen\OmniKassa;

class OmniKassaOrder
{
    /**
     * @var array $currencyCodes
     */
    protected $currencyCodes = array(
        'EUR' => '978',
        'USD' => '840',
        'CHF' => '756',
        'GBP' => '824',
        'CAD' => '124',
        'JPY' => '392',
        'AUD' => '036',
        'NOK' => '578',
        'SEK' => '752',
        'DKK' => '208',
    );

    /**
     * @var string $merchantId
     */
    protected $merchantId;

    /**
     * @var string $transactionReference
     */
    protected $transactionReference;
    
    /**
     * @var string $amount
     */
    protected $amount;
    
    /**
     * @var string $orderId
     */
    protected $orderId;
    
    /**
     * @var int $captureDay
     */
    protected $captureDay;
    
    /**
     * @var string $captureMode
     */
    protected $captureMode;
        
    /**
     * @var boolean $testMode
     */
    protected $testMode = false;

    /**
     * @var string
     */
    protected $currencyCode;

    public static function fromData($data)
    {
        $order = new self();
        $order
            ->setMerchantId($data['merchantId'])
            ->setCurrencyId($data['currencyCode'])
            ->setServerAmount($data['amount'])
            ->setTransactionReference($data['transactionReference'])
            ->setOrderId($data['orderId']);

        return $order;
    }

    /**
     * Set the merchant id provided by OmniKassa
     *
     * @param string $id The id
     *
     * @return OmniKassaOrder
     *
     * @throws \BadMethodCallException if test mode is enabled
     * @throws \LengthException if the length of the ID is not 15 characters
     */
    public function setMerchantId($id)
    {
        if ($this->testMode) {
            throw new \BadMethodCallException('The Merchant ID cannot be set in test mode');
        }
        if (strlen($id) !== 15) {
            throw new \LengthException('The Merchant ID should contain 15 characters');
        }
        $this->merchantId = $id;
        return $this;
    }

    /**
     * @param string $currencyCode
     *
     * @return OmniKassaOrder
     */
    public function setCurrencyCode($currencyCode)
    {
        if (!preg_match('/^[A-Z]{3}$/', $currencyCode)) {
            throw new \InvalidArgumentException('The given currency does not comply with the ISO 4217 standard');
        }
        if (!array_key_exists($currencyCode, $this->currencyCodes)) {
            throw new \InvalidArgumentException(sprintf('The requested currency "%s" is not available', $currencyCode));
        }
        $this->currencyCode = $currencyCode;
        
        return $this;
    }


    /**
     * @param string $currencyCode
     *
     * @return OmniKassaOrder
     */
    public function setCurrencyId($currencyId)
    {
        if (!is_numeric($currencyId)) {
            throw new \InvalidArgumentException('The currency ID must be an integer');
        }

        $currencyCode = array_search($currencyId, $this->currencyCodes);
        if ($currencyCode === false) {
            throw new \InvalidArgumentException(sprintf('A currency with Id "%s" is not available', $currencyCode));
        }
        $this->currencyCode = $currencyCode;

        return $this;
    }
    
    public function getCurrencyId()
    {
        return $this->currencyCodes[$this->currencyCode];
    }
    
    /**
     * Set the transaction reference
     *
     * @param string $reference The reference
     *
     * @return OmniKassaOrder
     *
     * @throws \LengthException if the reference is longer than 32 characters
     * @throws \InvalidArgumentException if the reference contains non-alphanumeric characters
     */
    public function setTransactionReference($reference)
    {
        if (strlen($reference) > 32) {
            throw new \LengthException('The transactionReference has a maximum of 32 characters');
        }
        if (!preg_match('/^[a-zA-Z0-9]+$/i', $reference)) {
            throw new \InvalidArgumentException('The transactionReference can only contain alphanumeric characters');
        }
        $this->transactionReference = $reference;
        return $this;
    }
    
    /**
     * Get the transaction reference
     *
     * @return string
     */
    public function getTransactionReference()
    {
        return $this->transactionReference;
    }

    /**
     * Set the amount of the order
     *
     * @param mixed $amount
     *
     * @return OmniKassaOrder
     *
     * @throws \LogicException if there hasn't been a currency supplied
     * @throws \InvalidArgumentException if the amount is not in the right format
     */
    public function setLocalAmount($amount)
    {
//        // A currency must be set
//        if (null === $this->currencyId) {
//            throw new \LogicException('Please set a currency first');
//        }
        $amount  = str_replace(',','.',$amount);
        $amount = floatval($amount);

        // Add decimals to value the currency is not Japanese Yen
        if ($this->currencyCode === 'JPY') {
            //convert to int, to guarantee no decimals while not multiplying
            $amount = (int)$amount;
        } else {
            $amount *= 100; //remove decimal places by multiplying with 100
        }
        // Check the maximum value
        if ($amount > 999999999999) {
            throw new \InvalidArgumentException('The amount cannot be over 9.999.999.999,99');
        }
        $this->amount = $amount;
        return $this;
    }

    /**
     * @param int $amount
     */
    public function setServerAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }
    
    /**
     * Get the amount
     *
     * @return string
     */
    public function getServerAmount()
    {
        return $this->amount;
    }

    public function getLocalAmount(){

        return $this->currencyCode === 'JPY' ?
            $this->amount : $this->amount /100;
    }
    
    /**
     * Set the order ID to give the transaction a reference
     *
     * @param string $orderId The order ID
     *
     * @return OmniKassaOrder
     *
     * @throws \LengthException when the orderId has more than 32 characters
     * @throws \InvalidArgumentException when the orderId has invalid characters
     */
    public function setOrderId($orderId)
    {
        if (strlen($orderId) > 32) {
            throw new \LengthException('The orderId has a maximum of 32 characters');
        }
        if (!preg_match('/^[a-z0-9]+$/i', $orderId)) {
            throw new \InvalidArgumentException('The orderId can only contain alphanumeric characters');
        }
        $this->orderId = $orderId;
        return $this;
    }
    
    /**
     * Get the order id
     *
     * @return string
     */
    public function getOrderId()
    {
        return $this->orderId;
    }
    
    /**
     * Set the number of days after authorization of a creditcard transaction in which a validation of
     * the transaction will be executed.
     *
     * @param int $days The number of days
     *
     * @return OmniKassaOrder
     *
     * @throws \InvalidArgumentException if the number is below 1 or higher than 99
     */
    public function setCaptureDay($days)
    {
        if (!is_int($days) || $days <= 0 || $days > 99) {
            throw new \InvalidArgumentException('The capture day should be an integer value between 1 and 100');
        }
        $this->captureDay = (int)$days;
        return $this;
    }
    
    /**
     * Set the number of days after authorization of a creditcard transaction in which a validation of
     * the transaction will be executed.
     *
     * @return int
     */
    public function getCaptureDay()
    {
        return $this->captureDay;
    }
    
    /**
     * Set the capture mode
     *
     * @todo Implementation of this function
     *
     * @throws \Exception Because this function is not yet implemented the user gets this exception
     */
    public function setCaptureMode($mode)
    {
        throw new \Exception('This function is not yet implemented');
    }
    
    /**
     * Get the capture mode
     *
     * @return string
     */
    public function getCaptureMode()
    {
        return $this->captureMode;
    }

    public function getData()
    {
        $data = array(
            'amount' => $this->getServerAmount(),
            'currencyCode' => $this->getCurrencyId(), //numeric id
            'merchantId' => $this->merchantId,
            'transactionReference' => $this->getTransactionReference(),
            'orderId' => $this->getOrderId(),
        );
        if($this->captureDay !== null)
        {
            $data['captureDay'] = $this->getCaptureDay();
            $data['captureMode'] = $this->getCaptureMode();
        }
        return $data;
    }
    
    /**
     * Enable test mode
     *
     * @return OmniKassaOrder
     */
    public function enableTestMode()
    {
        if($this->testMode === true){
            return $this;
        }
        $this->setMerchantId('002020000000001');
        $this->testMode = true;
        return $this;
    }
}