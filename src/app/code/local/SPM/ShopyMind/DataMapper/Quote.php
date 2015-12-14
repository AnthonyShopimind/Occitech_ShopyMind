<?php

class SPM_ShopyMind_DataMapper_Quote
{
    public function format(Mage_Sales_Model_Quote $quote)
    {
        return array(
            'id_customer' => $quote->getCustomerId(),
            'id_cart' => $quote->getId(),
            'date_add' => $quote->getCreatedAt(),
            'date_upd' => $quote->getUpdatedAt(),
            'amount' => $quote->getGrandTotal(),
            'tax_rate' => $quote->getBaseToQuoteRate(),
            'currency' => $quote->getQuoteCurrencyCode(),
            'voucher_used' => $this->getVoucherFor($quote),
            'voucher_amount' => $this->discountAmountFor($quote),
            'products' => $this->productsFor($quote),
        );
    }

    private function productsFor($quote)
    {
        $ItemFormatter = new SPM_ShopyMind_DataMapper_QuoteItem();
        return array_map(
            array($ItemFormatter, 'format'),
            $quote->getAllVisibleItems()
        );
    }

    private function getVoucherFor($quote)
    {
        $voucherUsed = array ();
        $vouchersOrder = $quote->getCouponCode();
        if ($vouchersOrder) {
            $voucherUsed[] = $vouchersOrder;
        }

        return $voucherUsed;
    }

    private function discountAmountFor($quote)
    {
        return $quote->getSubtotalWithDiscount() - $quote->getSubtotal();
    }
}
