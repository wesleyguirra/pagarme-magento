<?php
/*
 * @copyright  Copyright (C) 2015 Gamuza Technologies (http://www.gamuza.com.br/)
 * @author     Eneias Ramos de Melo <eneias@gamuza.com.br>
 */

class Inovarti_Pagarme_Model_Quote_Address_Total_Fee
extends Mage_Sales_Model_Quote_Address_Total_Abstract
{

protected $_code = 'fee';

public function collect (Mage_Sales_Model_Quote_Address $address)
{
    parent::collect($address);

    $this->_setAmount (0);
    $this->_setBaseAmount (0);

    $items = $this->_getAddressItems($address);
    if (!count ($items)) return $this;

    $quote = Mage::helper('checkout')->getQuote();
    $payment = $quote->getPayment()->getMethod();

    if ($payment == 'pagarme_cc')
    {
        $maxInstallments = (int) Mage::getStoreConfig('payment/pagarme_cc/max_installments');
        $minInstallmentValue = (float) Mage::getStoreConfig('payment/pagarme_cc/min_installment_value');
        $interestRate = (float) Mage::getStoreConfig('payment/pagarme_cc/interest_rate');
        $freeInstallments = (int) Mage::getStoreConfig('payment/pagarme_cc/free_installments');

        $total = Mage::helper('pagarme')->getBaseSubtotalWithDiscount () + Mage::helper ('pagarme')->getShippingAmount ();
        
        //var_dump (Mage::helper('pagarme')->getBaseSubtotalWithDiscount (), Mage::helper ('pagarme')->getShippingAmount ()); die;

        $n = floor ($total / $minInstallmentValue);
        if ($n > $maxInstallments) $n = $maxInstallments;
        elseif ($n < 1) $n = 1;

        $data = new Varien_Object();
        $data->setAmount(Mage::helper('pagarme')->formatAmount($total))
            ->setInterestRate($interestRate)
            ->setMaxInstallments($n)
            ->setFreeInstallments($freeInstallments);

        $post = Mage::app()->getRequest()->getPost();

        $payment_installment = 0;
        if (isset ($post ['payment']['installments']))
        {
            $payment_installment = $post ['payment']['installments'];
        }

        $installments = Mage::getModel('pagarme/api')->calculateInstallmentsAmount($data);
        $collection = $installments->getInstallments();

        foreach ($collection as $item)
        {
            if ($item->getInstallment() == $payment_installment)
            {
                $iamount = intval ($item->getInstallmentAmount ()) / 100;
                $famount = (float) number_format ($iamount, 2);
                $iqty = (int) $item->getInstallment();
                $balance = ($famount * $iqty) - $total;

                $address->setFeeAmount ($balance);
                $address->setBaseFeeAmount ($balance);

                $quote->setFeeAmount($balance);

                $address->setGrandTotal ($address->getGrandTotal () + $address->getFeeAmount ());
                $address->setBaseGrandTotal ($address->getBaseGrandTotal () + $address->getBaseFeeAmount ());

                //var_dump ($total, $iamount, $famount, $iqty, $balance); die;

                break;
            }
        }
    }
    
    return $this;
}

public function fetch (Mage_Sales_Model_Quote_Address $address)
{
    $amount = $address->getFeeAmount();
    if (!$amount) return $this;

    $address->addTotal (array(
        'code' => $this->getCode (),
        'title' => Mage::helper ('pagarme')->__('Fee'),
        'value'=> $amount,
    ));

    return $this;
}

}

