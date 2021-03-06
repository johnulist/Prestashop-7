<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Order;

use Cart;
use Configuration;
use Order;
use Splash\Core\SplashCore      as Splash;
use TaxCalculator;

/**
 * Prestashop Orders CRUD Functions
 */
trait CRUDTrait
{
    /**
     * @var Order
     */
    protected $Order;
    
    /**
     * @var TaxCalculator
     */
    protected $ShippingTaxCalculator;
    /**
     * @var Cart
     */
    private $Cart;
    
    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return false|Order
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Load Object
        $object = new Order($objectId);
        if ($object->id != $objectId) {
            return Splash::log()
                    ->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load Order (" . $objectId . ").");
        }
        
        //====================================================================//
        // Load Order Products
        $this->Products         = $object->getProductsDetail();
        $this->Payments         = $object->getOrderPaymentCollection();
        $this->PaymentMethod    = $object->module;
        
        //====================================================================//
        // Load Shipping Tax Calculator
        $this->ShippingTaxCalculator    = (new \Carrier($object->id_carrier))
            ->getTaxCalculator(new \Address($object->id_address_delivery));

        return $object;
    }

    /**
     * Create Request Object
     *
     * @return false|Order New Object
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        
        //====================================================================//
        // Check Order Minimal Fields are given
        // => Done By IntelliParser
        //====================================================================//
 
        //====================================================================//
        // Create a New Cart
        $this->Cart =   new Cart();
        $this->Cart->id_currency      =   Configuration::get('PS_CURRENCY_DEFAULT');
        if (true != $this->Cart->add()) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, "Unable to Create new Order Cart.");
        }
        
        //====================================================================//
        // Create a New Order
        $this->object  =   new Order();
        
        //====================================================================//
        // Setup Minimal Data
        $this->object->id_cart          =   $this->Cart->id;
        $this->object->id_currency      =   Configuration::get('PS_CURRENCY_DEFAULT');
        $this->object->conversion_rate  =   1;
        $this->object->id_carrier       =   1;
        $this->object->id_shop          =   1;
        $this->object->payment          =   "Payment by check";
        $this->object->module           =   "ps_checkpayment";
        $this->object->secure_key       =   md5(uniqid(rand(), true));
        
        $this->object->total_products       = (float) 0;
        $this->object->total_products_wt    = (float) 0;
        $this->object->total_paid_tax_excl  = (float) 0;
        $this->object->total_paid_tax_incl  = (float) 0;
        $this->object->total_paid           = 0;
        $this->object->total_paid_real      = 0;
        $this->object->round_mode           = Configuration::get('PS_PRICE_ROUND_MODE');
        $this->object->round_type           = Configuration::get('PS_ROUND_TYPE');

        $this->setCoreFields("id_customer", $this->in["id_customer"]);
        
        $this->setAddressFields("id_address_delivery", $this->in["id_address_delivery"]);
        $this->setAddressFields("id_address_invoice", $this->in["id_address_invoice"]);
        
        //====================================================================//
        // Persist Order in Database
        if (true != $this->object->add()) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, "Unable to Create new Order.");
        }
        
        //====================================================================//
        // Create Empty Order Products List
        $this->Products         = array();
            
        Splash::log()->deb("MsgLocalTpl", __CLASS__, __FUNCTION__, "New Order Created");

        return $this->object;
    }
    
    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return false|string Object Id
     */
    public function update($needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        if (!$needed) {
            return (string) $this->object->id;
        }
        
        //====================================================================//
        // If Id Given = > Update Object
        //====================================================================//
        if (!empty($this->object->id)) {
            if (true != $this->object->update()) {
                return Splash::log()->err(
                    "ErrLocalTpl",
                    __CLASS__,
                    __FUNCTION__,
                    "Unable to Update Order (" . $this->object->id . ")."
                );
            }
            
            Splash::log()->deb("MsgLocalTpl", __CLASS__, __FUNCTION__, "Order Updated");
        }
        
        //====================================================================//
        // UPDATE/CREATE SPLASH ID
        //====================================================================//
        if (!is_null($this->NewSplashId)) {
            self::setSplashId(self::$NAME, $this->object->id, $this->NewSplashId);
            $this->NewSplashId = null;
        }
        
        return (string) $this->object->id;
    }
    
    /**
     * Delete requested Object
     *
     * @param int $Id Object Id.  If NULL, Object needs to be created.
     *
     * @return bool
     */
    public function delete($Id = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        
        //====================================================================//
        // An Order Cannot Get deleted
        Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, "You Cannot Delete Prestashop Orders");
        
        //====================================================================//
        // Load Object From DataBase
        //====================================================================//
        $this->object     = new Order($Id);
        if ($this->object->id != $Id) {
            return Splash::log()->war("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load Order (" . $Id . ").");
        }
        
        //====================================================================//
        // Else Delete Product From DataBase
        $this->object->delete();

        return true;
    }
}
