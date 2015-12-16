<?php

/**
 * @loadSharedFixture
 * @group actions
 */
class SPM_ShopyMind_Test_Action_GenerateVoucher extends EcomDev_PHPUnit_Test_Case
{
    private $mockShopymindHelper;
    private $couponToCleanup;
    private $ruleToCleanup;

    public function setUp()
    {
        parent::setUp();

        $this->mockShopymindHelper = $this->getHelperMock('shopymind', array('shortId'));
        $this->replaceByMock('helper', 'shopymind', $this->mockShopymindHelper);
    }

    protected function tearDown()
    {
        parent::tearDown();

        $coupon = Mage::getModel('salesrule/coupon')->load($this->couponToCleanup, 'code');
        $rule = Mage::getModel('salesrule/rule')->load($this->ruleToCleanup);

        if($coupon) {
            $coupon->delete();
        }

        if($rule) {
            $rule->delete();
        }
    }

    public function testProcessCreateVoucher()
    {
        $this->mockShopymindHelper->expects($this->any())
            ->method('shortId')
            ->willReturn('test-1');

        $GenerateVoucher = new SPM_ShopyMind_Action_GenerateVoucher(null, 'percent', 20, null, 100, 10, null, null);
        $ruleName = $GenerateVoucher->process();
        $this->couponToCleanup = $ruleName;
        $ruleId = Mage::getModel('salesrule/coupon')->load($ruleName, 'code')->getRuleId();
        $this->ruleToCleanup = $ruleId;

        $actual = Mage::getModel('salesrule/rule')->load($ruleId)->getCouponCode();
        $expected = 'SPM-test-1';

        $this->assertEquals($expected, $actual);
    }

    public function testProcessCreateVoucherWithSpecifiedPrefix()
    {
        $this->mockShopymindHelper->expects($this->any())
            ->method('shortId')
            ->willReturn('test-2');

        $GenerateVoucher = new SPM_ShopyMind_Action_GenerateVoucher(null, 'percent', 20, null, 100, 10, null, null, 'MY_PREFIX');
        $ruleName = $GenerateVoucher->process();
        $this->couponToCleanup = $ruleName;
        $ruleId = Mage::getModel('salesrule/coupon')->load($ruleName, 'code')->getRuleId();
        $this->ruleToCleanup = $ruleId;

        $actual = Mage::getModel('salesrule/rule')->load($ruleId)->getCouponCode();
        $expected = 'SPM-MY_PREFIX-test-2';

        $this->assertEquals($expected, $actual);
    }

    /**
     * @loadFixture salesrule
     */
    public function testProcessDuplicateExistingRule()
    {
        $this->mockShopymindHelper->expects($this->any())
            ->method('shortId')
            ->willReturn('test-3');

        $GenerateVoucher = new SPM_ShopyMind_Action_GenerateVoucher(null, 'percent', 20, null, 100, 10, null, null, null, 12);
        $ruleName = $GenerateVoucher->process();
        $this->couponToCleanup = $ruleName;
        $ruleId = Mage::getModel('salesrule/coupon')->load($ruleName, 'code')->getRuleId();
        $this->ruleToCleanup = $ruleId;
        $rule = Mage::getModel('salesrule/rule')->load($ruleId);

        $actual = array(
            'name' => $rule->getName(),
            'description' => $rule->getDescription(),
            'from_date' => $rule->getFromDate(),
            'to_date' => $rule->getToDate(),
            'uses_per_customer' => $rule->getUsesPerCustomer(),
            'is_active' => $rule->getIsActive(),
            'conditions_serialized' => $rule->getConditionsSerialized(),
            'actions_serialized' => $rule->getActionsSerialized(),
        );

        $expected = array(
            'name' => 'Réduction 10%',
            'description' => 'Remise 10% catégorie sport',
            'from_date' => '2015-01-21',
            'to_date' => '2015-06-30',
            'uses_per_customer' => 100,
            'is_active' => 0,
            'conditions_serialized' => 'a:6:{s:4:"type";s:32:"salesrule/rule_condition_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}',
            'actions_serialized' => 'a:7:{s:4:"type";s:40:"salesrule/rule_condition_product_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";s:10:"conditions";a:1:{i:0;a:7:{s:4:"type";s:40:"salesrule/rule_condition_product_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";s:1:"1";s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";s:10:"conditions";a:2:{i:0;a:5:{s:4:"type";s:32:"salesrule/rule_condition_product";s:9:"attribute";s:12:"category_ids";s:8:"operator";s:2:"()";s:5:"value";s:23:"220, 227, 224, 225, 226";s:18:"is_value_processed";b:0;}i:1;a:5:{s:4:"type";s:32:"salesrule/rule_condition_product";s:9:"attribute";s:3:"sku";s:8:"operator";s:3:"!()";s:5:"value";s:195:"511-40108, 511-41186B, 511-41186C, 511-41186A, UKG-1011-01, UKG-1016-01, UKG-1017-01, UKG-1003-01, UKG-1009-01, UKG-5003-01, UKG-5007-02, UKG-5009-01, UKG-5009-02, UKG-5016, UKG-5017, UKG-1007-03";s:18:"is_value_processed";b:0;}}}}}',
        );

        $this->assertEquals($expected, $actual);
    }
}
