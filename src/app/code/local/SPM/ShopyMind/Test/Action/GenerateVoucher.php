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
    public function testProcessDuplicateExistingRuleWillDuplicateDataAndSetValidFromNow()
    {
        $this->mockShopymindHelper->expects($this->any())
            ->method('shortId')
            ->willReturn('test-3');

        $GenerateVoucher = new SPM_ShopyMind_Action_GenerateVoucher(null, 'percent', 20, null, null, 10, null, null, null, 'ECPA2015');
        $GenerateVoucher->now = '2016-01-25';
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
            'from_date' => '2016-01-25',
            'to_date' => '2016-02-04',
            'uses_per_customer' => 100,
            'is_active' => 0,
            'conditions_serialized' => 'a:6:{s:4:"type";s:32:"salesrule/rule_condition_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";b:1;s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}',
            'actions_serialized' => 'a:6:{s:4:"type";s:40:"salesrule/rule_condition_product_combine";s:9:"attribute";N;s:8:"operator";N;s:5:"value";b:1;s:18:"is_value_processed";N;s:10:"aggregator";s:3:"all";}',
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * @loadFixture salesrule
     */
    public function testProcessDuplicateNonExistingRule()
    {
        $this->mockShopymindHelper->expects($this->any())
            ->method('shortId')
            ->willReturn('test-4');

        $GenerateVoucher = new SPM_ShopyMind_Action_GenerateVoucher(null, 'percent', 20, null, 100, 10, null, null, null, 'non-existing-coupon');
        $actual = $GenerateVoucher->process();

        $this->assertFalse($actual);
    }

    /**
     * @loadFixture salesrule
     */
    public function testProcessDuplicateExistingRuleAndUpdateDesc()
    {
        $this->mockShopymindHelper->expects($this->any())
            ->method('shortId')
            ->willReturn('test-5');

        $GenerateVoucher = new SPM_ShopyMind_Action_GenerateVoucher(null, 'percent', 20, null, 100, 10, 'My new description', null, null, 'ECPA2015');
        $ruleName = $GenerateVoucher->process();
        $this->couponToCleanup = $ruleName;
        $ruleId = Mage::getModel('salesrule/coupon')->load($ruleName, 'code')->getRuleId();
        $this->ruleToCleanup = $ruleId;
        $rule = Mage::getModel('salesrule/rule')->load($ruleId);

        $actual = $rule->getDescription();
        $expected = 'My new description';

        $this->assertEquals($expected, $actual);
    }

    public function testProcessCreateVoucherWithSpecifiedStore()
    {
        $this->mockShopymindHelper->expects($this->any())
            ->method('shortId')
            ->willReturn('test-6');

        $GenerateVoucher = new SPM_ShopyMind_Action_GenerateVoucher(null, 'percent', 20, null, 100, 10, 'My new description', 'store-1');
        $ruleName = $GenerateVoucher->process();
        $this->couponToCleanup = $ruleName;
        $ruleId = Mage::getModel('salesrule/coupon')->load($ruleName, 'code')->getRuleId();
        $this->ruleToCleanup = $ruleId;
        $rule = Mage::getModel('salesrule/rule')->load($ruleId);

        $actual = $rule->getWebsiteIds();
        $expected = array(1);

        $this->assertEquals($expected, $actual);
    }

    public function testProcessCreateVoucherWillConvertCurrencyAmountAndMinimumOrderAmountInBaseCurrency()
    {
        $this->markTestIncomplete('TODO');
    }
}
