<?php

class SPM_ShopyMind_Action_GenerateVoucher implements SPM_ShopyMind_Interface_Action
{
    private $params;

    public function __construct($id_customer, $type, $amount, $amountCurrency, $minimumOrder, $nbDayValidate, $description, $idShop, $prefix = null, $duplicateCode = null)
    {
        $this->params['customerIdentifier'] = $id_customer;
        $this->params['type'] = $type;
        $this->params['amount'] = $amount;
        $this->params['amountCurrency'] = $amountCurrency;
        $this->params['minimumOrder'] = $minimumOrder;
        $this->params['nbDayValidate'] = $nbDayValidate;
        $this->params['description'] = $description;
        $this->params['idShop'] = $idShop;
        $this->params['prefix'] = $prefix;
        $this->params['duplicateCode'] = $duplicateCode;
    }

    public function process()
    {
        $couponCode = $this->generateCouponCode();

        if (empty($this->params['duplicateCode'])) {
            $rule = $this->makeNewRule($couponCode);
        } else {
            $rule = $this->makeDuplicatedRule($this->params['duplicateCode']);
            if (empty($rule)) {
                return false;
            }
        }

        if ($this->params['description']) {
            $rule->setDescription($this->params['description']);
        }

        if ($this->params['minimumOrder']) {
            $condition = Mage::getModel('salesrule/rule_condition_address')
                ->setType('salesrule/rule_condition_address')
                ->setAttribute('base_subtotal')
                ->setOperator('>=')
                ->setValue((int) $this->params['minimumOrder']);

            $rule->getConditions()->addCondition($condition);
        }

        $websiteId = null;

        if ($this->params['idShop']) {
            $scope = SPM_ShopyMind_Model_Scope::fromShopymindId($this->params['idShop']);
            $stores = $scope->stores();
            $websiteId = $stores[0]->getWebsiteId();
        }

        $this->createCouponFor($rule, $couponCode, $websiteId);

        if ($rule->save()) {
            return $couponCode;
        }
        return false;
    }

    private function getAllCustomerGroupsIds()
    {
        $return = array();
        $results = Mage::getResourceModel('customer/group_collection')->toOptionArray();
        if ($results) {
            foreach ($results as $row) {
                $return [] = $row ['value'];
            }
        }

        return $return;
    }

    private function getAllWebsitesIds()
    {
        $websites = Mage::getModel('core/website')->getCollection();
        $websiteIds = array();
        foreach ($websites as $website) {
            $websiteIds[] = $website->getId();
        }

        return $websiteIds;
    }

    private function generateCouponCode()
    {
        $shortId = Mage::helper('shopymind')->shortId();
        $prefix = !empty($this->params['prefix']) ? 'SPM-' . $this->params['prefix'] . '-' : 'SPM-';
        $coupon_code = $prefix . $shortId;
        return $coupon_code;
    }


    private function createCouponFor($rule, $coupon_code, $websiteId = null)
    {
        if (!$websiteId) {
            $websiteId = $this->getAllWebsitesIds();
        }

        $rule->setDiscountStep('0')
            ->setApplyToShipping('0')
            ->setTimesUsed('0')
            ->setIsRss('0')
            ->setCouponType('2')
            ->setUsesPerCoupon(1)
            ->setCustomerGroupIds($this->getAllCustomerGroupsIds())
            ->setWebsiteIds($websiteId)
            ->setCouponCode($coupon_code);
    }

    private function makeNewRule($couponCode)
    {
        $now = date('Y-m-d H:i:s');
        $rule = Mage::getModel('salesrule/rule');

        $rule->setName($couponCode)
            ->setFromDate($now)
            ->setToDate((date('Y-m-d 23:59:59', mktime(date("H"), date("i"), date("s"), date("m"), date("d") + $this->params['nbDayValidate'], date("Y")))))
            ->setUsesPerCustomer('1')
            ->setIsActive('1')
            ->setStopRulesProcessing('0')
            ->setIsAdvanced('1')
            ->setProductIds(NULL)
            ->setSortOrder('0');

        if ($this->params['type'] == 'percent') {
            $rule->setSimpleAction('by_percent')
                ->setDiscountAmount($this->params['amount'])
                ->setDiscountQty(NULL)
                ->setSimpleFreeShipping('0');
        }

        if ($this->params['type'] == 'amount') {
            $rule->setSimpleAction('cart_fixed')
                ->setDiscountAmount($this->params['amount'])
                ->setDiscountQty(NULL)
                ->setSimpleFreeShipping('0');
        } elseif ($this->params['type'] == 'shipping') {
            $rule->setSimpleFreeShipping(1);
        }

        return $rule;
    }

    private function makeDuplicatedRule($fromCode)
    {
        $ruleId = Mage::getModel('salesrule/coupon')->load($fromCode, 'code')->getRuleId();
        if (!$ruleId) {
            return false;
        }

        $ruleData = Mage::getModel('salesrule/rule')->load($ruleId)->getData();
        unset($ruleData['rule_id']);

        $rule = Mage::getModel('salesrule/rule');
        $rule->setData($ruleData);
        return $rule;
    }
}
