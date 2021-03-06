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

namespace   Splash\Local\Objects;

use Splash\Core\SplashCore      as Splash;

use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;
use Splash\Models\Objects\ObjectsTrait;
use Splash\Local\Local;
use Splash\Local\Services\LanguagesManager;

use Shop;
use Configuration;
use Currency;
use SplashSync;
use OrderInvoice;
use Order as psOrder;

/**
 * @abstract    Splash Local Object Class - Customer Invoices Local Integration
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Invoice extends AbstractObject
{

    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;
    use ObjectsTrait;

    // Prestashop Common Traits
    use \Splash\Local\Objects\Core\DatesTrait;
    use \Splash\Local\Objects\Core\SplashMetaTrait;
    use \Splash\Local\Objects\Core\ObjectsListCommonsTrait;
    use \Splash\Local\Traits\SplashIdTrait;
    
    // Prestashop Order Traits
    use \Splash\Local\Objects\Order\CoreTrait;
    use \Splash\Local\Objects\Order\MainTrait;
    use \Splash\Local\Objects\Order\AddressTrait;
    use \Splash\Local\Objects\Order\ItemsTrait;
    use \Splash\Local\Objects\Order\PaymentsTrait;

    // Prestashop Invoice Traits
    use \Splash\Local\Objects\Invoice\ObjectsListTrait;
    use \Splash\Local\Objects\Invoice\CRUDTrait;
    use \Splash\Local\Objects\Invoice\CoreTrait;
    use \Splash\Local\Objects\Invoice\StatusTrait;

    
    //====================================================================//
    // Object Definition Parameters
    //====================================================================//
    
    /**
     *  Object Disable Flag. Uncomment this line to Override this flag and disable Object.
     */
//    protected static    $DISABLED        =  True;
    
    /**
     *  Object Name (Translated by Module)
     */
    protected static $NAME            =  "Customer Invoice";
    
    /**
     *  Object Description (Translated by Module)
     */
    protected static $DESCRIPTION     =  "Prestashop Customers Invoice Object";
    
    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO     =  "fa fa-money";
    
    /**
     *  Object Synchronistion Limitations
     *
     *  This Flags are Used by Splash Server to Prevent Unexpected Operations on Remote Server
     */
    protected static $ALLOW_PUSH_CREATED         =  false;       // Allow Creation Of New Local Objects
    protected static $ALLOW_PUSH_UPDATED         =  false;       // Allow Update Of Existing Local Objects
    protected static $ALLOW_PUSH_DELETED         =  false;       // Allow Delete Of Existing Local Objects
    
    /**
     *  Object Synchronistion Recommended Configuration
     */
    // Enable Creation Of New Local Objects when Not Existing
    protected static $ENABLE_PUSH_CREATED       =  false;
    // Enable Update Of Existing Local Objects when Modified Remotly
    protected static $ENABLE_PUSH_UPDATED       =  false;
    // Enable Delete Of Existing Local Objects when Deleted Remotly
    protected static $ENABLE_PUSH_DELETED       =  false;

    //====================================================================//
    // General Class Variables
    //====================================================================//
   
    protected $Products       = null;
    protected $Payments       = null;
    protected $PaymentMethod  = null;
    
    /**
     * @var OrderInvoice
     */
    protected $object;
    
    /**
     * @var int
     */
    private $LangId = null;

    /**
     * @var Currency
     */
    private $Currency = null;
    
    /**
     * @var SplashSync
     */
    private $spl = null;
    
    //====================================================================//
    // Class Constructor
    //====================================================================//
        
    public function __construct()
    {
        //====================================================================//
        // Set Module Context To All Shops
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        //====================================================================//
        //  Load Local Translation File
        Splash::translator()->load("objects@local");
        //====================================================================//
        // Load Splash Module
        $this->spl = Local::getLocalModule();
        //====================================================================//
        // Load Default Language
        $this->LangId   = LanguagesManager::loadDefaultLanguage();
        //====================================================================//
        // Load OsWs Currency
        $this->Currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));
    }
}
