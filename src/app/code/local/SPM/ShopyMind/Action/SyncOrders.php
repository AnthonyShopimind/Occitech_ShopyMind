<?php

class SPM_ShopyMind_Action_SyncOrders implements SPM_ShopyMind_Interface_Action
{

    private $params;

    public function __construct(SPM_ShopyMind_Model_Scope $scope, $start, $limit, $lastUpdate, $orderId, $justCount)
    {
        $this->params['scope'] = $scope;
        $this->params['start'] = $start;
        $this->params['limit'] = $limit;
        $this->params['lastUpdate'] = $lastUpdate;
        $this->params['orderId'] = $orderId;
        $this->params['justCount'] = $justCount;
    }

    public function process()
    {
        $orders = $this->retrieveOrders();
        if ($this->params['justCount']) {
            return $orders;
        }

        $Formatter = new SPM_ShopyMind_DataMapper_Order();

        return array_map(array($Formatter, 'format'), iterator_to_array($orders->getIterator()));
    }

    public function retrieveOrders()
    {

        $orderCollection = Mage::getModel('sales/order')->getCollection()
            ->addFieldToFilter('updated_at', array('gt' => $this->params['lastUpdate']));
        $this->params['scope']->restrictCollection($orderCollection);

        if ($this->params['orderId']) {
            $orderCollection->addFieldToFilter('entity_id', array('in' => $this->params['orderId']));
        }

        if ($this->params['limit']) {
            $orderCollection->getSelect()->limit($this->params['limit'], $this->params['start']);
        }

        if ($this->params['justCount']) {
            return $orderCollection->count();
        }

        return $orderCollection;
    }
}
