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

use Configuration as Cfg;

class Design extends AbstractHook
{
    const AVAILABLE_HOOKS = [
        'header',
        'displayBeforeBodyClosingTag',
        'displayProductPriceBlock',
        'displayBanner',
        'displayFooterBefore',
        'displayProductAdditionalInfo',
        'displayLeftColumn',
        'displayRightColumn',
        'displayShoppingCartFooter'
    ];


    /**
     * Header hook
     */
    public function header()
    {
        \Media::addJsDef([
            'bluepayment_env' => (int)Cfg::get($this->module->name_upper . '_TEST_ENV') === 1 ? 'TEST' : 'PRODUCTION',
            'asset_path' => $this->module->getPathUrl() . 'views/',
            'change_payment' => $this->module->l('change'),
            'read_more' => $this->module->l('read more'),
            'get_regulations_url' => $this->context->link->getModuleLink('bluepayment', 'regulationsGet', [], true),
        ]);

        $this->context->controller->addCSS($this->module->getPathUrl() . 'views/css/front.css');
        $this->context->controller->addJS($this->module->getPathUrl() . 'views/js/front.min.js');
        $this->context->controller->addJS($this->module->getPathUrl() . 'views/js/blik_v3.js');
        $this->context->controller->addJS($this->module->getPathUrl() . 'views/js/gpay.js');
    }


    /**
     * Add analytics Gtag
     * @param $params
     * @return void
     */
    public function displayProductPriceBlock($params)
    {
        if ($params['type'] === 'before_price') {
            $product = $params['product'];
            $brand = '';

            if (isset($product['id_manufacturer'])) {
                $brand = \Manufacturer::getNameById($product['id_manufacturer']);
            }

            $this->context->smarty->assign([
                'ga_product_id' => $product['id'],
                'ga_product_name' => $product['name'],
                'ga_product_brand' => $brand,
                'ga_product_cat' => $product['category_name'],
                'ga_product_price' => $product['price'],
            ]);

            return $this->module->fetch('module:bluepayment/views/templates/hook/ga_listing.tpl');
        }
    }


    /**
     * Adds promoted payments to the top of the page
     * @return string|null
     */
    public function displayBanner()
    {
        if (Cfg::get($this->module->name_upper . '_PROMO_HEADER')) {
            $this->getSmartyAssets();
            return $this->module->fetch('module:bluepayment/views/templates/hook/labels/header.tpl');
        }
        return null;
    }

    /**
     * Adds promoted payments above the footer
     * @return string|null
     */
    public function hookDisplayFooterBefore()
    {
        if (Cfg::get($this->module->name_upper . '_PROMO_FOOTER')) {
            $this->getSmartyAssets();
            return $this->module->fetch('module:bluepayment/views/templates/hook/labels/footer.tpl');
        }
        return null;
    }

    /**
     * Adds promoted payments under the buttons in the product page
     * @return string|null
     */
    public function displayProductAdditionalInfo()
    {
        if (Cfg::get($this->module->name_upper . '_PROMO_PRODUCT')) {
            $this->getSmartyAssets('product');
            return $this->module->fetch('module:bluepayment/views/templates/hook/labels/product.tpl');
        }
        return null;
    }

    /**
     * Adds promoted payments sidebar
     * @return string|null
     */
    public function getSidebarPromo()
    {
        if (Cfg::get($this->module->name_upper . '_PROMO_LISTING')) {
            $this->getSmartyAssets('sidebar');
            return $this->module->fetch('module:bluepayment/views/templates/hook/labels/labels.tpl');
        }
        return null;
    }


    /**
     * Adds promoted payments in the left column on the category subpage
     */
    public function displayLeftColumn()
    {
        $this->getSidebarPromo();
    }

    /**
     * Adds promoted payments in the right column on the category subpage
     */
    public function displayRightColumn()
    {
        $this->getSidebarPromo();
    }

    /**
     * Adds promoted payments in the shopping cart under products
     * @return string|null
     */
    public function displayShoppingCartFooter()
    {
        if (Cfg::get($this->module->name_upper . '_PROMO_CART')) {
            $this->getSmartyAssets('cart');
            return $this->module->fetch('module:bluepayment/views/templates/hook/labels/labels.tpl');
        }
        return null;
    }

    /**
     * Gtag data
     * @return string
     */
    public function displayBeforeBodyClosingTag()
    {
        $controller = \Tools::getValue('controller');

        $tracking_id = false;
        $secret_key = false;

        if (Cfg::get('BLUEPAYMENT_GA_TRACKER_ID')) {
            $tracking_id = Cfg::get('BLUEPAYMENT_GA_TRACKER_ID');
        } elseif (Cfg::get('BLUEPAYMENT_GA4_TRACKER_ID') && Cfg::get('BLUEPAYMENT_GA4_SECRET')) {
            $tracking_id = Cfg::get('BLUEPAYMENT_GA4_TRACKER_ID');
            $secret_key = Cfg::get('BLUEPAYMENT_GA4_SECRET');
        }

        $this->context->smarty->assign([
            'tracking_id' => $tracking_id,
            'tracking_secret_key' => $secret_key,
            'controller' => $controller,
            'bm_ajax_controller' => $this->context->link->getModuleLink(
                $this->module->name,
                'ajax',
                array('ajax' => 1)
            )
        ]);

        if ($controller == 'cart') {
            $this->context->smarty->assign([
                'products' => $this->context->cart->getProducts(false, false, null, false),
            ]);
        } elseif ($controller == 'order') {
            $coupons_array = [];
            $coupons_list = '';

            if ($this->context->cart->getCartRules()) {
                foreach ($this->context->cart->getCartRules() as $coupon) {
                    $coupons_array[] = $coupon['name'];
                }
                $coupons_list = implode(", ", $coupons_array);
            }

            $this->context->smarty->assign([
                'products' => $this->context->cart->getProducts(true),
                'coupons' => $coupons_list,
                'ga4_tracking_id' => Cfg::get('BLUEPAYMENT_GA4_TRACKER_ID') ?? false,
                'ga4_secret' => Cfg::get('BLUEPAYMENT_GA4_SECRET') ?? false,
            ]);
        }

        return $this->module->display(
            $this->module->getPathUrl(),
            'views/templates/hook/gtag.tpl'
        );
    }



    /**
     * Get smarty assets
     *
     * @param string $type
     * @return void
     */
    private function getSmartyAssets(string $type = 'main')
    {
        $pay_later = Cfg::get($this->module->name_upper . '_PROMO_PAY_LATER');
        $instalment = Cfg::get($this->module->name_upper . '_PROMO_INSTALMENTS');
        $match_instalments = Cfg::get($this->module->name_upper . '_PROMO_MATCHED_INSTALMENTS');
        $promo_checkout = Cfg::get($this->module->name_upper . '_PROMO_CHECKOUT');

        $this->context->smarty->assign(
            [
                'bm_assets_images' => $this->module->images_dir,
                'bm_instalment' => $instalment,
                'bm_pay_later' => $pay_later,
                'bm_matched_instalments' => $match_instalments,
                'bm_promo_checkout' => $promo_checkout,
                'bm_promo_type' => $type
            ]
        );
    }
}