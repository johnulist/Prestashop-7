<?php
/**
 * This file is part of SplashSync Project.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *  @author    Splash Sync <www.splashsync.com>
 *  @copyright 2015-2018 Splash Sync
 *  @license   MIT
 */

namespace Splash\Local\Objects\ThirdParty;

use Address;
use Context;
use State;
use Country;
use Translate;

/**
 * Access to thirdparty Primary Address Fields
 */
trait AddressTrait
{

    /**
     * Build Fields using FieldFactory
     */
    private function buildPrimaryAddressPart1Fields()
    {
        $groupName  =   Translate::getAdminTranslation("Address", "AdminCustomers");

        //====================================================================//
        // Addess
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("address1")
                ->Name($groupName)
                ->MicroData("http://schema.org/PostalAddress", "streetAddress")
                ->Group($groupName)
                ->isReadOnly();

        //====================================================================//
        // Addess Complement
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("address2")
                ->Name($groupName . " (2)")
                ->MicroData("http://schema.org/PostalAddress", "postOfficeBoxNumber")
                ->Group($groupName)
                ->isReadOnly();
        
        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("postcode")
                ->Name(Translate::getAdminTranslation("Zip/Postal Code", "AdminAddresses"))
                ->MicroData("http://schema.org/PostalAddress", "postalCode")
                ->Group($groupName)
                ->isReadOnly();
        
        //====================================================================//
        // City Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("city")
                ->Name(Translate::getAdminTranslation("City", "AdminAddresses"))
                ->MicroData("http://schema.org/PostalAddress", "addressLocality")
                ->Group($groupName)
                ->isReadOnly();
        
        //====================================================================//
        // State Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("state")
                ->Name(Translate::getAdminTranslation("State", "AdminAddresses"))
                ->MicroData("http://schema.org/PostalAddress", "addressRegion")
                ->Group($groupName)
                ->isReadOnly();
        
        //====================================================================//
        // State code
        $this->fieldsFactory()->create(SPL_T_STATE)
                ->Identifier("id_state")
                ->Name(Translate::getAdminTranslation("State", "AdminAddresses") . " (Code)")
                ->MicroData("http://schema.org/PostalAddress", "addressRegion")
                ->Group($groupName)
                ->isReadOnly();
        
        //====================================================================//
        // Country Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("country")
                ->Name(Translate::getAdminTranslation("Country", "AdminAddresses"))
                ->MicroData("http://schema.org/PostalAddress", "addressCountry")
                ->Group($groupName)
                ->isReadOnly();
        
        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->create(SPL_T_COUNTRY)
                ->Identifier("id_country")
                ->Name(Translate::getAdminTranslation("Country", "AdminAddresses") . " (Code)")
                ->MicroData("http://schema.org/PostalAddress", "addressCountry")
                ->Group($groupName)
                ->isReadOnly();
    }

    /**
     * Build Fields using FieldFactory
     */
    private function buildPrimaryAddressPart2Fields()
    {
        $GroupName  =   Translate::getAdminTranslation("Address", "AdminCustomers");
        
        //====================================================================//
        // Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
                ->Identifier("phone")
                ->Name(Translate::getAdminTranslation("Home phone", "AdminAddresses"))
                ->MicroData("http://schema.org/PostalAddress", "telephone")
                ->Group($GroupName)
                ->isReadOnly();
        
        //====================================================================//
        // Mobile Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
                ->Identifier("phone_mobile")
                ->Name(Translate::getAdminTranslation("Mobile phone", "AdminAddresses"))
                ->MicroData("http://schema.org/Person", "telephone")
                ->Group($GroupName)
                ->isReadOnly();

        //====================================================================//
        // VAT Number
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("vat_number")
                ->Name(Translate::getAdminTranslation("VAT Number", "AdminAddresses"))
                ->MicroData("http://schema.org/Organization", "vatID")
                ->isReadOnly();
    }
    
    /**
     * Read requested Field
     *
     * @return void
     */
    private function getAddressList()
    {
        
        //====================================================================//
        // Create List If Not Existing
        if (!isset($this->out["contacts"])) {
            $this->out["contacts"] = array();
        }

        //====================================================================//
        // Read Address List
        $AddresList = $this->object->getAddresses(Context::getContext()->language->id);

        //====================================================================//
        // If Address List Is Empty => Null
        if (empty($AddresList)) {
            return;
        }
                
        //====================================================================//
        // Run Through Address List
        foreach ($AddresList as $Key => $Address) {
            $this->out["contacts"][$Key] = array (
                "address" => self::objects()->Encode("Address", $Address["id_address"])
                );
        }
                
        return;
    }
    
    /**
     * Read requested Field
     *
     * @param       string    $key                    Input List Key
     * @param       string    $fieldName              Field Identifier / Name
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return         void
     */
    private function getPrimaryAddressFields($key, $fieldName)
    {
        //====================================================================//
        // Identify Main Address Id
        $mainAddressId = Address::getFirstCustomerAddressId($this->object->id);
        
        //====================================================================//
        // If Empty, Create A New One
        $mainAddress = new Address(is_numeric($mainAddressId) ? $mainAddressId : null);
        
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'address1':
            case 'address2':
            case 'postcode':
            case 'city':
            case 'country':
            case 'phone':
            case 'phone_mobile':
            case 'vat_number':
                //====================================================================//
                // READ Directly on Class
                $this->out[$fieldName] = $mainAddress->$fieldName;
                unset($this->in[$key]);
                break;
            case 'id_country':
                //====================================================================//
                // READ With Convertion
                $this->out[$fieldName] = Country::getIsoById($mainAddress->id_country);
                unset($this->in[$key]);
                break;
            case 'state':
                //====================================================================//
                // READ With Convertion
                $state = new State($mainAddress->id_state);
                $this->out[$fieldName] = $state->name;
                unset($this->in[$key]);
                break;
            case 'id_state':
                //====================================================================//
                // READ With Convertion
                $state = new State($mainAddress->id_state);
                $this->out[$fieldName] = $state->iso_code;
                unset($this->in[$key]);
                break;
        }
    }
}
