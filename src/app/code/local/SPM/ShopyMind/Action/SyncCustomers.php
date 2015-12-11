<?php

class SPM_ShopyMind_Action_SyncCustomers implements SPM_ShopyMind_Interface_Action
{
    private $params;
    private $scope;

    public function __construct(SPM_ShopyMind_Model_Scope $scope, $start, $limit, $lastUpdate, $customerId = false, $justCount = false)
    {
        $this->scope = $scope;
        $this->params['start'] = $start;
        $this->params['limit'] = $limit;
        $this->params['lastUpdate'] = $lastUpdate;
        $this->params['customerId'] = $customerId;
        $this->params['justCount'] = $justCount;
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

        $customerCollection = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('updated_at', array('gt' => $this->params['lastUpdate']));
        $this->scope->restrictCollection($customerCollection);

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
