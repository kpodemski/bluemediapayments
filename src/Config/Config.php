<?php
/**
 * NOTICE OF LICENSE
 * This source file is subject to the GNU Lesser General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/lgpl-3.0.en.html
 *
 * @author     Blue Media S.A.
 * @copyright  Since 2015 Blue Media S.A.
 * @license    https://www.gnu.org/licenses/lgpl-3.0.en.html GNU Lesser General Public License
 */

declare(strict_types=1);

namespace BluePayment\Config;

class Config
{
    public const BM_IMAGES_PATH = _MODULE_DIR_ . 'bluepayment/views/img/';
    public const SERVICE_PARTNER_ID = '_SERVICE_PARTNER_ID';
    public const SHARED_KEY = '_SHARED_KEY';

    public const HASH_SEPARATOR = '|';
    public const ALIOR_MIN_AMOUNT = 50;
    public const ALIOR_MAX_AMOUNT = 7750;
    public const SMARTNEY_MIN_AMOUNT = 100;
    public const SMARTNEY_MAX_AMOUNT = 2500;
    public const BLIK_CODE_LENGTH = 6;
    public const DEFAULT_PAYMENT_FORM_LANGUAGE = 'pl';

    public const GATEWAY_ID_ALIOR = 1506;
    public const GATEWAY_ID_CARD = 1500;
    public const GATEWAY_ID_GOOGLE_PAY = 1512;
    public const GATEWAY_ID_APPLE_PAY = 1513;
    public const GATEWAY_ID_SMARTNEY = 700;
    public const GATEWAY_ID_PAYPO = 705;
    public const GATEWAY_ID_BLIK = 509;
    public const GATEWAY_ID_WALLET = 999;
    public const GATEWAY_ID_TRANSFER = 9999;

    // Amplitude events
    public const PLUGIN_ACTIVATED = 'plugin activated';
    public const PLUGIN_DEACTIVATED = 'plugin deactivated';
    public const PLUGIN_INSTALLED = 'plugin installed';
    public const PLUGIN_UNINSTALLED = 'plugin uninstalled';
    public const PLUGIN_VERSION = 'plugin version';
    public const PLUGIN_UPDATED = 'plugin updated';
    public const PLUGIN_PAY_COMPLETED = 'transaction completed';

    public const PLUGIN_AUTH = 'plugin authorized';
    public const API_AUTHENTICATION_SUCCESS = 'authorization completed';
    public const API_AUTHENTICATION_FAILED = 'authorization failed';

    public static function getSdk(): void
    {
        if (file_exists(__DIR__ . '/../../libs/index.php')) {
            require_once __DIR__ . '/../../libs/index.php';
        }
    }
}
