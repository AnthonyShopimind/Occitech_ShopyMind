<?php

class SPM_ShopyMind_Action_SyncCustomers implements SPM_ShopyMind_Interface_Action
{
    private $params;

    public function __construct(SPM_ShopyMind_Model_Scope $scope, $start, $limit, $lastUpdate, $customerId, $justCount)
    {
        $this->params['start'] = $start;
        $this->params['limit'] = $limit;
        $this->params['lastUpdate'] = $lastUpdate;
        $this->params['customerId'] = $customerId;
        $this->params['justCount'] = $justCount;
        $this->params['scope'] = $scope;
    }

    public function process()
    {
        $customerIds = $this->retrieveCustomerIds();
        if ($this->params['justCount']) {
            return $customerIds;
        }

        $GetUsers = new SPM_ShopyMind_Action_GetUser($customerIds);
        return $GetUsers->process();
    }

    public function retrieveCustomerIds()
    {
        $customerCollection = Mage::getModel('customer/customer')->getCollection()
            ->addFieldToFilter('updated_at', array('gt' => $this->params['lastUpdate']));

        if ($this->params['scope']->getId()) {
            $this->params['scope']->restrictCollection($customerCollection);
        }

        if (!is_array($this->params['customerId']) && !empty($this->params['customerId'])) {
            return $this->params['customerId'];
        }

        if ($this->params['customerId']) {
            $customerCollection->addFieldToFilter('entity_id', array('in' => $this->params['customerId']));
        }

        if ($this->params['limit']) {
            $customerCollection->getSelect()->limit($this->params['limit'], $this->params['start']);
        }

        if ($this->params['justCount']) {
            return $customerCollection->count();
        }

        return array_map(function($customer) {
            return $customer->getEntityId();
        }, iterator_to_array($customerCollection->getIterator()));
    }
}
