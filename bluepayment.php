<?php

/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 * Php version 7.1
 *
 * @category  Payment
 * @package   Blue_Media
 * @author    Blue Media S.A. <biuro@bluemedia.pl>
 * @copyright Since 2015 Blue Media S.A.
 * @license   https://www.gnu.org/licenses/lgpl-3.0.en.html GNU
 */

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/vendor/autoload.php';

use BluePayment\Analyse\Amplitude;
use BluePayment\Install\Installer;
use BluePayment\Configure\Configure;
use BluePayment\HookDispatcher;
use BluePayment\Service\FactoryPaymentMethods;
use BluePayment\Until\Helper;
use Configuration as Cfg;

class BluePayment extends PaymentModule
{
    public $name_upper;

    /**
     * @var string
     */
    public $_path;

    /**
     * @var string
     */
    public $name;

    /**
     * @var null
     */
    public $tracked_id;

    /**
     * @var string
     */
    public $tab;

    /**
     * @var string
     */
    public $version;

    /**
     * @var string
     */
    public $author;

    /**
     * @var int
     */
    public $need_instance;

    /**
     * @var array
     */
    public $ps_versions_compliancy;

    /**
     * @var bool
     */
    public $currencies;

    /**
     * @var string
     */
    public $currencies_mode;

    /**
     * @var bool
     */
    public $bootstrap;

    /**
     * @var string
     */
    public $module_key;

    /**
     * @var string
     */
    public $images_dir;

    /**
     * @var string
     */
    public $displayName;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $confirmUninstall;

    /**
     * @var HookDispatcher
     */
    private $hookDispatcher;

    public $id_order = null;

    const PLUGIN_ACTIVATED = 'plugin activated';
    const PLUGIN_DEACTIVATED = 'plugin deactivated';

    public function __construct()
    {
        $this->name = 'bluepayment';
        $this->tracked_id = null;
        $this->name_upper = \Tools::strtoupper($this->name);

        require_once dirname(__FILE__) . '/config/config.inc.php';

        $this->tab = 'payments_gateways';
        $this->version = '2.7.9';
        $this->author = 'Blue Media S.A.';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.7', 'max' => _PS_VERSION_];
        $this->currencies = true;
        $this->currencies_mode = 'checkbox';
        $this->bootstrap = true;
        $this->module_key = '7dac119ed21c46a88632206f73fa4104';
        $this->images_dir = _MODULE_DIR_ . 'bluepayment/views/img/';

        parent::__construct();

        $this->displayName = $this->l('Blue Media payments');
        $this->description = $this->l(
            'Plugin supports online payments implemented by payment gateway Blue Media company.'
        );
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall?');

        $this->hookDispatcher = new HookDispatcher($this);
    }

    /**
     * Install module
     * @throws Exception
     * @return bool
     */
    public function install(): bool
    {
        $state = true;

        if (!parent::install() || false === (new Installer($this, $this->getTranslator()))->install()) {
            $state = false;
        }

        $this->registerHook(
            $this->getHookDispatcher()->getAvailableHooks()
        );

        $this->registerHook(
            'paymentOptions'
        );

        return $state;
    }

    /**
     * Uninstall module
     * @throws Exception
     * @return bool
     */
    public function uninstall(): bool
    {
        $state = true;

        if (parent::uninstall()) {
            if (false === (new Installer($this, $this->getTranslator()))->uninstall()) {
                $state = false;
            }

            if (false === (new Configure($this, $this->getTranslator()))->uninstall()) {
                $state = false;
            }
        }

        return $state;
    }


    public function enable($force_all = false)
    {

        if (false === (new Configure($this, $this->getTranslator()))->install()) {
            return false;
        }



        $data = [
            'events' => [
                "event_type" => self::PLUGIN_ACTIVATED,
                "user_properties" => [
                    self::PLUGIN_ACTIVATED => true,
                ],
            ],
        ];
        $amplitude = Amplitude::getInstance();
        $amplitude->sendEvent($data);

        return parent::enable($force_all);
    }

    public function disable($force_all = false)
    {
        $data = [
            'events' => [
                "event_type" => self::PLUGIN_DEACTIVATED,
                "user_properties" => [
                    self::PLUGIN_ACTIVATED => false,
                ],
            ],
        ];

        $amplitude = Amplitude::getInstance();
        $amplitude->sendEvent($data);

        return parent::disable($force_all);
    }

    /**
     * @return HookDispatcher
     */
    public function getHookDispatcher(): HookDispatcher
    {
        return $this->hookDispatcher;
    }

    /**
     * Return current context
     * @return Context
     */
    public function getContext(): Context
    {
        return $this->context;
    }

    /**
     * Dispatch hooks
     * @param string $methodName
     * @param array $arguments
     */
    public function __call(string $methodName, array $arguments = [])
    {
        return $this->getHookDispatcher()->dispatch(
            $methodName,
            !empty($arguments[0]) ? $arguments[0] : []
        );
    }


    /**
     * Post form method
     * @return void
     */
    public function getContent()
    {
        \Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminBluepaymentPayments')
        );
    }


    public function HookTranslateElements()
    {
        $this->l('Payment by card');
        $this->l('Virtual wallet');
        $this->l('Blue Media - Configuration');
    }

    /**
     * Create payment methods
     */
    public function hookPaymentOptions()
    {

        if (!$this->active) {
            return null;
        }

        $currency = $this->context->currency;
        $id_shop = $this->context->shop->id;

        $serviceId = Helper::parseConfigByCurrency(
            $this->name_upper . SERVICE_PARTNER_ID,
            $currency->iso_code
        );
        $sharedKey = Helper::parseConfigByCurrency(
            $this->name_upper . SHARED_KEY,
            $currency->iso_code
        );

        $paymentDataCompleted = !empty($serviceId) && !empty($sharedKey);

        if ($paymentDataCompleted === false) {
            return null;
        }

        $moduleLink = $this->context->link->getModuleLink('bluepayment', 'payment', [], true);

        require_once BM_SDK_PATH;

        /// Get all transfers
        $gatewayTransfer = new \DbQuery();
        $gatewayTransfer->from('blue_gateway_transfers', 'gt');
        $gatewayTransfer->leftJoin('blue_gateway_transfers_shop', 'gts', 'gts.id = gt.id');
        $gatewayTransfer->where('gt.gateway_id NOT IN (' . Helper::getGatewaysList() . ')');
        $gatewayTransfer->where('gt.gateway_status = 1');
        $gatewayTransfer->where('gt.gateway_currency = "' . pSql($currency->iso_code) . '"');

        if (Shop::isFeatureActive()) {
            $gatewayTransfer->where('gts.id_shop = ' . (int)$id_shop);
        }

        $gatewayTransfer->select('*');
        $gatewayTransfer = Db::getInstance()->executeS($gatewayTransfer);


        /// Get all wallets
        $gatewayWallet = new \DbQuery();
        $gatewayWallet->from('blue_gateway_transfers', 'gt');
        $gatewayWallet->leftJoin('blue_gateway_transfers_shop', 'gts', 'gts.id = gt.id');
        $gatewayWallet->where('gt.gateway_id IN (' . Helper::getWalletsList() . ')');
        $gatewayWallet->where('gt.gateway_status = 1');
        $gatewayWallet->where('gt.gateway_currency = "' . pSql($currency->iso_code) . '"');

        if (Shop::isFeatureActive()) {
            $gatewayWallet->where('gts.id_shop = ' . (int)$id_shop);
        }

        $gatewayWallet->select('*');
        $gatewayWallet = Db::getInstance()->executeS($gatewayWallet);

        $this->context->smarty->assign([
            'module_link' => $moduleLink,
            'ps_version' => _PS_VERSION_,
            'module_dir' => $this->getPathUrl(),
            'selectPayWay' => Cfg::get($this->name_upper . '_SHOW_PAYWAY'),
            'gateway_transfers' => $gatewayTransfer,
            'gateway_wallets' => $gatewayWallet,
            'img_wallets' => Helper::getImgPayments('wallet'),
            'img_transfers' => Helper::getImgPayments('transfers'),
            'regulations_get' => $this->context->link->getModuleLink('bluepayment', 'regulationsGet', [], true),
            'changePayment' => $this->l('change'),
            'bm_promo_checkout' => Cfg::get($this->name_upper . '_PROMO_CHECKOUT'),
            'gpayRedirect' => Cfg::get($this->name_upper . '_GPAY_REDIRECT'),
            'start_payment_translation' => $this->l('Start payment'),
            'start_payment_intro' => $this->l('Internet transfer, BLIK, payment card, Google Pay, Apple Pay'),
            'order_subject_to_payment_obligation_translation' => $this->l('Order with the obligation to pay'),
        ]);

        $paymentMethods = new FactoryPaymentMethods($this);
        if (Cfg::get($this->name_upper . '_SHOW_PAYWAY')) {
            $newOptions = $paymentMethods->create();
        } else {
            $newOptions = $paymentMethods->single();
        }

        return $newOptions;
    }


    /**
     * Get module path
     * @return string
     */
    public function getPathUrl(): string
    {
        return $this->_path;
    }

    public function debug( $texto ) {
        $logfilename = dirname( __FILE__ ) . '/log.log';
        file_put_contents( $logfilename, print_r( $texto, true ) );
    }

}
