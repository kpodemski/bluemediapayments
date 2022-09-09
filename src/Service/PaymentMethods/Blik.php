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
use Configuration as Config;
use Context;
use Module;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use Tools;

class Blik implements GatewayType
{
    public function getPaymentOption(
        \Module $module,
        array $data = []
    ): PaymentOption {

        if (Config::get($module->name_upper . '_BLIK_REDIRECT')) {
            $moduleLink = Context::getContext()->link->getModuleLink(
                'bluepayment',
                'payment',
                [],
                true
            );

            $option = new PaymentOption();
            $option
                ->setCallToActionText($module->l($data['gateway_name']))
                ->setAction($moduleLink)
                ->setInputs([
                    [
                        'type' => 'hidden',
                        'name' => 'bluepayment_gateway',
                        'value' => GATEWAY_ID_BLIK,
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'bluepayment_gateway_id',
                        'value' => GATEWAY_ID_BLIK,
                    ]
                ])
                ->setLogo($data['gateway_logo_url'])
                ->setAdditionalInformation(
                    $module->fetch('module:bluepayment/views/templates/hook/paymentRedirectBlik.tpl')
                );
        } else {
            $blikModuleLink = Context::getContext()->link->getModuleLink(
                'bluepayment',
                'chargeBlik',
                [],
                true
            );

            Context::getContext()->smarty->assign([
                'blik_moduleLink' => $blikModuleLink,
            ]);

            $option = new PaymentOption();
            $option
                ->setCallToActionText($data['gateway_name'])
                ->setAction($blikModuleLink)
                ->setBinary(true)
                ->setLogo($data['gateway_logo_url'])
                ->setAdditionalInformation(
                    $module->fetch('module:bluepayment/views/templates/hook/paymentBlik.tpl')
                );
        }

        return $option;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return (bool)BlueGatewayTransfers::gatewayIsActive(
            GATEWAY_ID_BLIK,
            Context::getContext()->currency->iso_code
        );
    }
}