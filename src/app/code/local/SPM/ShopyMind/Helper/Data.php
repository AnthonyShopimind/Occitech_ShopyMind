<?php
/**
 * SPM_ShopyMind_Helper_Data
 *
 * @package     SPM_ShopyMind
 * @copyright   Copyright (c) 2014 - ShopyMind SARL (http://www.shopymind.com)
 * @license     New BSD license (http://license.shopymind.com)
 * @author      Couvert Jean-Sébastien <js.couvert@shopymind.com>
 * @version     $Id Data.php 2014-12-17$
 */
class SPM_ShopyMind_Helper_Data extends Mage_Core_Helper_Abstract {

    public function formatCustomerQuote($quote)
    {
        return array(
            'id_customer' => $quote->getCustomerId(),
            'id_cart' => $quote->getId(),
            'date_add' => $quote->getCreatedAt(),
            'date_upd' => $quote->getUpdatedAt(),
            'amount' => $quote->getGrandTotal(),
            'tax_rate',
            'currency' => $quote->getQuoteCurrencyCode(),
            'voucher_used' => ShopymindClient_Callback::getCartVouchers(),
            'voucher_amount',
            'products',
        );
    }

    public function formatQuoteItem($quoteItem)
    {
        $product = $quoteItem->getProduct();
        $children = $quoteItem->getChildren();
        $combinationId = count($children) ? $children[0]->getProductId() : $product->getId();

        return array(
            'id_product' => $product->getId(),
            'qty' => $quoteItem->getQty(),
            'price' => $quoteItem->getPriceInclTax(),
            'id_combination' => $combinationId,
            'id_manufacturer' => $product->getManufacturer(),
        );
    }
}
