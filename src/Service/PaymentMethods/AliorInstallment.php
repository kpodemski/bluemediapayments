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

namespace BluePayment\Service\PaymentMethods;

use BluePayment\Api\BlueGatewayTransfers;
use BluePayment\Config\Config;
use BluePayment\Until\Helper;
use Cart;
use Configuration as Cfg;
use Context;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

class AliorInstallment implements GatewayType
{
    public function getPaymentOption(
        \BluePayment $module,
        array $data = []
    ): PaymentOption {
        $moduleLink = Context::getContext()->link->getModuleLink(
            'bluepayment',
            'payment',
            [],
            true
        );

        $option = new PaymentOption();
        $option->setCallToActionText($module->l($data['gateway_name']))
            ->setAction($moduleLink)
            ->setInputs([
                [
                    'type' => 'hidden',
                    'name' => 'bluepayment_gateway',
                    'value' => Config::GATEWAY_ID_ALIOR,
                ],
                [
                    'type' => 'hidden',
                    'name' => 'bluepayment_gateway_id',
                    'value' => Config::GATEWAY_ID_ALIOR,
                ],
            ])
            ->setLogo($data['gateway_logo_url'])
            ->setAdditionalInformation(
                $module->fetch('module:bluepayment/views/templates/hook/paymentRedirectAliorbank.tpl')
            );

        return $option;
    }

    /**
     * @return bool
     */
    public function isActive($cart_total = null): bool
    {
        $isoCode = Helper::getIsoFromContext(Context::getContext());
        $alior = BlueGatewayTransfers::isTransferActive(
            Config::GATEWAY_ID_ALIOR,
            $isoCode
        );
        if (!$cart_total) {
            $cart_total = Context::getContext()->cart->getOrderTotal(true, Cart::BOTH);
        }

        $activePromo = Cfg::get('BLUEPAYMENT_PROMO_PAY_LATER');

        return $alior
            && $activePromo
            && (float) $cart_total >= (float) Config::ALIOR_MIN_AMOUNT
            && (float) $cart_total <= (float) Config::ALIOR_MAX_AMOUNT;
    }
}
