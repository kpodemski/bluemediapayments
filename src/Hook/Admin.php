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

namespace BluePayment\Hook;

use Configuration as Config;
use OrderHistory;
use Order;
use Tools;
use BluePayment\Service\Refund;
use BluePayment\Until\AdminHelper;
use BluePayment\Until\Helper;

class Admin extends AbstractHook
{
    const AVAILABLE_HOOKS = [
        'adminPayments',
        'adminOrder',
        'displayAdminAfterHeader'
    ];

    /**
     * Payment statuses
     */
    const PAYMENT_STATUS_PENDING = 'PENDING';
    const PAYMENT_STATUS_SUCCESS = 'SUCCESS';
    const PAYMENT_STATUS_FAILURE = 'FAILURE';

    /**
     * Get the payment methods available in the administration
     */
    public function adminPayments()
    {
        $list = $transfer_payments = $wallets = [];

        $adminHelper = new AdminHelper();

        foreach (AdminHelper::getSortCurrencies() as $currency) {
            $paymentList = $adminHelper->getListChannels($currency['iso_code']);
            $title = $currency['name'] . ' (' . $currency['iso_code'] . ')';

            if (!empty($paymentList)) {
                $list[] = $adminHelper->renderAdditionalOptionsList($this->module, $paymentList, $title);
            }

            if ($adminHelper->getListAllPayments($currency['iso_code'], 'transfer')) {
                $transfer_payments[$currency['iso_code']] = $adminHelper->getListAllPayments(
                    $currency['iso_code'],
                    'transfer'
                );
            }

            if ($adminHelper->getListAllPayments($currency['iso_code'], 'transfer')) {
                $wallets[$currency['iso_code']] = $adminHelper->getListAllPayments(
                    $currency['iso_code'],
                    'wallet'
                );
            }
        }

        $this->module->display(
            $this->module->getPathUrl(),
            'views/templates/admin/_configure/helpers/form/notification-info.tpl'
        );

        $this->context->smarty->assign(
            [
                'list' => $list,
                'transfer_payments' => $transfer_payments,
                'wallets' => $wallets,
            ]
        );

        return $this->module->display(
            $this->module->getPathUrl(),
            'views/templates/admin/_configure/helpers/container_list.tpl'
        );
    }


    public function displayAdminAfterHeader()
    {
        try {
            // Connect to Prestashop addons API
            $api_url = 'https://api-addons.prestashop.com/';
            $params = '?format=json&iso_lang=pl&iso_code=pl&method=module&id_module=49791&method=listing&action=module';

            $api_request = $api_url . $params;

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $api_request);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            $output = curl_exec($curl);
            curl_close($curl);

            $api_response = json_decode($output);
            $ver = $api_response->modules[0]->version;

            $this->context->smarty->assign(['version' => $ver]);

            if ($ver && version_compare($ver, $this->module->version, '>')) {
                \PrestaShopLogger::addLog('BM - Dostępna aktualizacja', 1);
                return $this->module->fetch('module:bluepayment/views/templates/admin/_partials/upgrade.tpl');
            }
        } catch (Exception $e) {
            \PrestaShopLogger::addLog('Brak aktualizacji', 3);
        }

        return null;
    }


    public function adminOrder($params)
    {
        $this->module->id_order = $params['id_order']; /// todo seter
        $order = new Order($this->id_order);

        $output = '';

        if ($order->module !== 'bluepayment') {
            return $output;
        }
        $updateOrderStatusMessage = '';

        $order_payment = Helper::getLastOrderPaymentByOrderId($params['id_order']);

        $refundable = $order_payment['payment_status'] === self::PAYMENT_STATUS_SUCCESS;
        $refund_type = Tools::getValue('bm_refund_type', 'full');
        $refund_amount = $refund_type === 'full'
            ? $order->total_paid
            : (float)str_replace(',', '.', Tools::getValue('bm_refund_amount'));
        $refund_errors = [];
        $refund_success = [];

        if ($refundable && Tools::getValue('go-to-refund-bm')) {
            if ($refund_amount > $order->total_paid) {
                $refund_errors[] = $this->module->l('The refund amount you entered is greater than paid amount.');
            } else {
                $refund = new Refund($this->module);
                $order = new \OrderCore($order->id);
                $currency = new \Currency($order->id_currency);

                $refundOrder = $refund->refundOrder(
                    $refund_amount,
                    $order_payment['remote_id'],
                    $currency
                );

                if (!empty($refundOrder[1]) || $refundOrder[0] !== true) {
                    $refund_errors[] = $this->module->l('Refund error: ') . $refundOrder[1];
                }

                if (empty($refund_errors) && $refundOrder[0] === true) {
                    $history = new OrderHistory();
                    $history->id_order = (int)$order->id;
                    $history->id_employee = (int)$this->context->employee->id;
                    $history->changeIdOrderState(Config::get('PS_OS_REFUND'), (int)$order->id);
                    $history->addWithemail(true, []);
                    $refund_success[] = $this->module->l('Successful refund');
                }
            }
        }

        $this->context->smarty->assign([
            'BM_ORDERS' => Helper::getOrdersByOrderId($params['id_order']),
            'BM_ORDER_ID' => $this->module->id_order,
            'BM_CANCEL_ORDER_MESSAGE' => $updateOrderStatusMessage,
            'SHOW_REFUND' => $refundable,
            'REFUND_FULL_AMOUNT' => number_format($order->total_paid, 2, '.', ''),
            'REFUND_ERRORS' => $refund_errors,
            'REFUND_SUCCESS' => $refund_success,
            'REFUND_TYPE' => $refund_type,
            'REFUND_AMOUNT' => $refund_amount,
        ]);

        return $this->module->fetch('module:bluepayment/views/templates/admin/status.tpl');
    }
}