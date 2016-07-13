{*
 * BlueMedia_BluePayment extension
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the GNU General Public License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://www.gnu.org/licenses/gpl-3.0.html
 *
 * @category       BlueMedia
 * @package        BlueMedia_BluePayment
 * @copyright      Copyright (c) 2015-2016
 * @license        https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License
 *}

{if $hash_valid == false}
    <span class="alert-warning">
        {l s='We noticed a problem with your order. If you think this is an error, feel free to contact our' mod='bluepayment'}
        <a href="{$link->getPageLink('contact', true)|escape:'html'}">{l s='expert customer support team' mod='bluepayment'}</a>.
    </span>
{/if}