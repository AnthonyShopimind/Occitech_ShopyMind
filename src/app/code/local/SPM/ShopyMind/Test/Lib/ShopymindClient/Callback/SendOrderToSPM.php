<?php

/**
 * @loadSharedFixture
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_SendOrderToSPM extends EcomDev_PHPUnit_Test_Case
{
    public function testFormatOrderDataShouldReturnCorrectInformation()
    {
        $orderId = 2;
        $order = Mage::getModel('sales/order')->load($orderId);
        $orderData = $order->getData();

        $expected = array (
            'idRemindersSend' => 1,
            'shopIdShop' => '1',
            'orderIsConfirm' => false,
            'idStatus' => 'pending',
            'idCart' => '1',
            'dateCart' => '2015-01-01 10:00:00',
            'idOrder' => '2',
            'amount' => '100.0000',
            'taxRate' => '1.0000',
            'currency' => 'EUR',
            'dateOrder' => '2015-01-01 10:00:00',
            'voucherUsed' => 1,
            'products' => array(),
            'customer' => ShopymindClient_Callback::getUser(1),
            'shipping_number' => array(),
        );

        $method = $this->getPrivateMethod('ShopymindClient_Callback', 'formatOrderData');
        $ShopymindClient_Callback = new ShopymindClient_Callback();
        $result = $method->invokeArgs($ShopymindClient_Callback, array($orderData, 1, 1));

        $this->assertEquals($expected, $result);
    }

    public function testFormatOrderDataShouldReturnOrderIsConfirmTrueIfStateIsNew(){
        $orderId = 2;
        $order = Mage::getModel('sales/order')->load($orderId);
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING);
        $orderData = $order->getData();

        $method = $this->getPrivateMethod('ShopymindClient_Callback', 'formatOrderData');
        $ShopymindClient_Callback = new ShopymindClient_Callback();
        $result = $method->invokeArgs($ShopymindClient_Callback, array($orderData, 1, 1));

        $this->assertTrue($result['orderIsConfirm']);
    }

    private function getPrivateMethod($className, $methodName) {
        $reflector = new ReflectionClass($className);
        $method = $reflector->getMethod($methodName);
        $method->setAccessible(true);

        return $method;
    }

}
