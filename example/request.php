<?php
require_once('../vendor/autoload.php');

use stefantalen\OmniKassa\OmniKassaRequest;

function getPath()
{
    $directories = explode('/', $_SERVER['REQUEST_URI']);
    array_pop($directories);
    return 'http://'. $_SERVER['HTTP_HOST'] . implode('/', $directories);
}

$order = new \stefantalen\OmniKassa\OmniKassaOrder();
$order
    ->setCurrencyCode('EUR')
    ->setLocalAmount('2,0')
    ->setMerchantId('000000000000000')
    ->setTransactionReference(date('Ymdhis').'1')
    ->setOrderId(date('Ymdhis'));

$request = new OmniKassaRequest($order);
$request
    ->addPaymentMeanBrand('IDEAL')
    ->addPaymentMeanBrand('INCASSO')
    ->setNormalReturnUrl(getPath() .'/return.php')
    ->setAutomaticResponseUrl(getPath() .'/response.php')
    ->setKeyVersion('1')
    ->setSecretKey('002020000000001_KEY1')
    ->enableTestMode()
;

?>
<html>
<form method="post" action="<?php echo $request->getActionUrl() ?>">
    <input type="hidden" name="Data" value="<?php echo $request->getData() ?>">
    <input type="hidden" name="InterfaceVersion" value="<?php echo $request->getInterfaceVersion() ?>">
    <input type="hidden" name="Seal" value="<?php echo $request->getSeal() ?>">
    <input type="submit" value="Naar betaling" />
</form>



</html>