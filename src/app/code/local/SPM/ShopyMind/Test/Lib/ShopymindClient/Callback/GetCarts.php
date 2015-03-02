<?php

/**
 * @loadSharedFixture
 * @doNotIndexAll
 */
class SPM_ShopyMind_Test_Lib_ShopymindClient_Callback_GetCarts extends EcomDev_PHPUnit_Test_Case
{
    protected $_aCart;

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

    protected function tearDown()
    {
        parent::tearDown();

        if (session_id()) {
            session_destroy();
        }
    }

    public function setUp()
    {
        $this->_aCart = array(
            'sum_cart' => 173.88999999999999,
            'currency' => 'USD',
            'tax_rate' => '1.0000',
            'id_cart' => '1',
            'link_cart' => 'checkout/cart/',
            'date_cart' => '2014-01-29 12:45:46',
            'date_upd' => '2014-01-30 13:45:46',
            'products' => array (
                array (
                    'id' => 1,
                    'description' => 'LEGGING',
                    'price' => '13.0000',
                    'image_url' => $this->placeholderImageUrl(),
                    'product_url' => 'catalog/product/view/id/1/s/legging/',
                    'id_combination' => false,
                    'qty' => '2.0000',
                    'product_categories' => array(1, 2),
                    'product_manufacturer' => null, // See test below
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
                'locale' => '_00',
                'date_last_order' => 0,
                'nb_order' => '0',
                'sum_order' => 0,
                'groups' => array ('1'),
                'shop_id_shop' => '1',
                'nb_order_year' => '0',
                'sum_order_year' => 0,
                'region' => null,
                'postcode' => null,
            ),
        );

        parent::setUp();
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

        $expectedResult = array(
            $this->_aCart,
        );
        $this->assertEquals($expectedResult, $results);
    }

    /**
     * @loadFixture multipleCarts
     */
    public function testGetMultipleCarts()
    {
        $results = ShopymindClient_Callback::getCarts('store-1', array(1, 2));

        $anotherCart = array_merge(
            $this->_aCart,
            array(
                'id_cart' => '2',
                'date_cart' => '2014-01-30 12:45:46',
                'date_upd' => '2014-01-31 13:45:46',
            )
        );
        $anotherCart['products'][0]['qty'] = '1.0000';

        $expectedResult = array(
            $this->_aCart,
            $anotherCart,
        );

        $this->assertEquals($expectedResult, $results);
    }

    private function placeholderImageUrl()
    {
        if (!function_exists('imagecreatefromjpeg')) {
            $this->markTestSkipped('Impossible to work with jpeg images on your system');
        }
        return '/catalog/product/cache/1/small_image/200x/9df78eab33525d08d6e5fb8d27136e95/images/catalog/product/placeholder/small_image.jpg';
    }
}
