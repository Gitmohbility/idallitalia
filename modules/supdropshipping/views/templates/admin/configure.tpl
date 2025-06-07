{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop-project.org/ for more information.
 *
 * @author    Supdropshipping SA <info@supdropshipping.com>
 * @copyright 2010-2024 Supdropshipping
 * @license   https://www.supdropshipping.com Academic Free License 1.0 (AFL-3.0)
 *}
<prestashop-accounts></prestashop-accounts>

<!-- You should use the billing plan library in order to display your plan -->
<section id="billing-plan-selection" style="display:none">
    <h2>Select your plan</h2>
    <div id="back-button" {if !$hasSubscription}class="hide" {/if}>
        <button onclick="showBillingWrapper()"
                style="background: black;color: white; padding: 0.5rem; font-weight: bold;margin-bottom: 1.5rem;">Back to
            subscription</button>
    </div>
    <div style=" display:flex">
        {foreach $componentItems as $item}
            <div style="border: 1px solid;padding: 2rem;text-align:center;margin-left:1rem;width: 30%;">
                <h3 style="font-weight: bold;margin-bottom: 1rem;">{$item['details']['name']|escape:'htmlall':'UTF-8'}</h3>

                <!-- Pricing information must be retrieved from billing API -->
                <p style="margin-bottom: 1rem;">{$item['price']/100|escape:'htmlall':'UTF-8'}€/{$item['billingPeriodUnit']|escape:'htmlall':'UTF-8'}</p>
                <!-- Pricing id must be retrieved from billing API -->
                <button onclick="openCheckout('{$item['id']|escape:'htmlall':'UTF-8'}')" style="background: black;color: white; padding: 0.5rem; font-weight: bold;margin-bottom: 1.5rem;">Select this offer</button>
                {if !empty($item['details']['features'])}
                    <div class="billing-plan__feature-group">
                        {foreach $item['details']['features'] as $feature}
                            <div style="display: flex; flex-direction: row;">
                                <div style="display: flex; flex-direction: row; align-items: flex-start;">
                                    <div class="puik-icon material-icons-round" style="font-size: 1.25rem;">check</div>
                                    <div>{$feature|escape:'htmlall':'UTF-8'}</div>
                                </div>
                            </div>
                        {/foreach}
                    </div>
                {/if}
            </div>
        {/foreach}
</section>

<div id="ps-billing-wrapper" style="display:none">
    <div id="ps-billing"></div>
</div>
<div id="ps-modal"></div>
{literal}
<script>
    const hasSubscription = {/literal}{$hasSubscription|intval}{literal};
</script>
{/literal}
<script src="{$urlAccountsCdn|escape:'htmlall':'UTF-8'}" rel=preload></script>
<script src="{$urlBilling|escape:'htmlall':'UTF-8'}" rel=preload></script>
<script src="{$urlConfigureJs|escape:'htmlall':'UTF-8'}" rel=preload></script>
