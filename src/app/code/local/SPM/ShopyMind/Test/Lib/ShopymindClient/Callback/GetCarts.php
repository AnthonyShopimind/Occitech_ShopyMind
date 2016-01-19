<?php

/**
 * @loadSharedFixture
 * @doNotIndexAll
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetCarts extends EcomDev_PHPUnit_Test_Case
{
    protected function setUp()
    {
        parent::setUp();
        $this->replaceByMock('model', 'core/session', $this->guestSession());
    }

    protected $_anExpectedCart = array(
        'sum_cart' => 173.88999999999999,
        'currency' => 'USD',
        'tax_rate' => '1.0000',
        'id_cart' => '1',
        'link_cart' => 'checkout/cart/',
        'date_cart' => '2014-01-29 12:45:46',
        'date_upd' => '2014-01-30 13:45:46',
        'products' => array (
            array (
                'id_product' => 1,
                'id_manufacturer' => null,
                'price' => '13.0000',
                'reference' => '14156575-XS-9394',
                'combinations' => array(),
                'product_link' => 'catalog/product/view/id/1/s/legging/',
                'price_discount' => '13.0000',
                'name' => 'LEGGING',
                'description_short' => null,
                'description' => null,
                'id_categories' => array(1, 2),
                'date_creation' => '1970-01-01 00:00:00',
                'active' => true,
                'quantity_remaining' => '8.0000',
                'shop_id_shop' => '1',
                'lang' => 'en',
                'currency' => 'USD',
            ),
        ),
        'customer' => array(
            'id_customer' => '1234',
            'optin' => false,
            'customer_since' => '0000-00-00 00:00:00',
            'last_name' => 'Oliver',
            'first_name' => 'April',
            'email_address' => 'april.oliver90@example.com',
            'phone1' => '',
            'phone2' => '',
            'gender' => '2',
            'birthday' => 0,
            'locale' => 'en_00',
            'date_last_order' => 0,
            'nb_order' => '0',
            'sum_order' => 0,
            'groups' => array ('1'),
            'shop_id_shop' => '1',
            'nb_order_year' => '0',
            'sum_order_year' => 0,
            'region' => null,
            'postcode' => null,
            'active' => true,
            'addresses' => array(),
        ),
    );

    public static function tearDownAfterClass()
    {
        parent::tearDownAfterClass();
        $write = Mage::getSingleton('core/resource')->getConnection('write');
        $write->query(<<<'QUERY'
        TRUNCATE catalog_product_entity_varchar;
        TRUNCATE catalog_product_entity_int;
        TRUNCATE catalog_product_entity_decimal;
        TRUNCATE catalog_product_entity_text;
        TRUNCATE catalog_category_product;
        TRUNCATE catalog_product_entity;
QUERY
        );
    }

    public function testGetCartsReturnEmptyListIfTheCartIdDoesNotExists()
    {
        $this->assertEmpty(ShopymindClient_Callback::getCarts('store-1', 1));
    }

    /**
     * @loadFixture aCart
     */
    public function testGetCartsReturnEmptyListIfTheCartIdDoesNotBelongsToTheGivenStore()
    {
        $this->assertEmpty(ShopymindClient_Callback::getCarts('store-2', 1));
    }

    /**
     * @loadFixture aCart
     */
    public function testGetASingleCart()
    {
        $results = ShopymindClient_Callback::getCarts('store-1', 1);
        $results = $this->removeImageUrlKeyFrom($results);

        $expectedResult = array(
            $this->_anExpectedCart,
        );
        $this->assertEquals($expectedResult, $results);
    }

    /**
     * @loadFixture multipleCarts
     */
    public function testGetMultipleCarts()
    {
        $results = ShopymindClient_Callback::getCarts('store-1', array(1, 2));
        $results = $this->removeImageUrlKeyFrom($results);

        $anotherCart = array_merge(
            $this->_anExpectedCart,
            array(
                'id_cart' => '2',
                'date_cart' => '2014-01-30 12:45:46',
                'date_upd' => '2014-01-31 13:45:46',
            )
        );

        $expectedResult = array(
            $this->_anExpectedCart,
            $anotherCart,
        );

        $this->assertEquals($expectedResult, $results);
    }

    private function removeImageUrlKeyFrom($results)
    {
        $results = array_map(function ($result) {
            $result['products'] = array_map(function ($product) {
                unset($product['image_link']);
                return $product;
            }, $result['products']);

            return $result;
        }, $results);
        return $results;
    }
}
