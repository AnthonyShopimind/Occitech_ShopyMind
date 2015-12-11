<?php

class SPM_ShopyMind_Action_SyncCustomers implements SPM_ShopyMind_Interface_Action
{
    private $params;

    public function __construct(array $params = array())
    {
        $this->params = $params;
    }

    public function process()
    {
        $customerIds = $this->retrieveCustomerIds();

        if ($this->params['justCount']) {
            return $customerIds;
        }

        return array_map(array(ShopymindClient_Callback, 'getUser'), $customerIds);
    }

    public function retrieveCustomerIds()
    {
        if ($this->params['customerId']) {
            if (!is_array($this->params['customerId'])) {
                return array($this->params['customerId']);
            }
            return $this->params['customerId'];
        }
        $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($this->params['idShop']);
        $customerCollection = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('updated_at', array('gt' => $this->params['lastUpdate']));
        $scope->restrictCollection($customerCollection);
        if ($this->params['limit']) {
            $customerCollection->getSelect()->limit($this->params['limit'], $this->params['start']);
        }

        if ($this->params['justCount']) {
            return $customerCollection->count();
        }

        $result = array();
        foreach($customerCollection as $customer) {
            $result[] = $customer->getId();
        }

        return $result;
    }
}
