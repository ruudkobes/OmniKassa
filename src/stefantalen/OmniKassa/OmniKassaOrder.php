<?php

namespace stefantalen\OmniKassa;

use stefantalen\OmniKassa\OmniKassaRequest;

class OmniKassaOrder
{

    /**
     * @var $merchantId string
     */
    protected $merchantId;
    
    /**
     * @var $secretKey string
     */
    protected $secretKey;
    
    /**
     * @var $currency string
     */
    protected $currency;
        
    public function setMerchantId($id)
    {
        if(strlen($id) !== 15) {
            throw new \LengthException('The Merchant ID should contain 15 characters');
        }
        $this->merchantId = $merchantId;
    }
    
    public function setSecretKey($key)
    {
        $this->secretKey = $key;
    }
    
    /**
     * @param $currencyCode string
     * @return OmniKassaOrder
     */
    public function setCurrency($currencyCode)
    {
        if(!preg_match('/^[A-Z]{3}$/', $currencyCode)) {
            throw new \InvalidArgumentException('The given currency does not comply with the ISO 4217 standard');
        }
        $currencyCodes = array(
            'EUR' => '987',
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
        if(!array_key_exists($currencyCode, $currencyCodes)) {
            throw new \InvalidArgumentException(sprintf('The requested currency "%s" is not available', $currencyCode));
        }
        $this->currency = $currencyCodes[$currencyCode];
        
        return $this;
    }
    
    public function getCurrency()
    {
        return $this->currency;
    }
}
