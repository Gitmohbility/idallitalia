/**
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
 */

window?.psaccountsVue?.init();

let billingContext = {...window.psBillingContext.context};
let currentModal;
let customer;
// console.log(document.getElementsByClassName('panel'),'这是打印的东西');
if (window.psaccountsVue.isOnboardingCompleted() == true) {
    showPlanSelection();
    customer = new window.psBilling.CustomerComponent({
        context: billingContext,
        hideInvoiceList: true,
        onOpenModal,
        onEventHook,
        onOpenFunnel
    });
    customer.render("#ps-billing");
    if (hasSubscription) {
        // window.psBilling.initializeInvoiceList(
        billingContext;
        //  "#ps-billing-invoice"
        // );
        setTimeout(function (){
            document.getElementById('fieldset_0').style.display = 'block'
            document.getElementById('fieldset_0').style.height = '130px'
        },30);

    }else{
        setTimeout(function (){
            document.getElementById('fieldset_0').style.display = 'none'
            document.getElementById('fieldset_0').style.height = '130px'
        },30);
    }
}

// Modal open / close management
async function onCloseModal(data) {
    await Promise.all([currentModal.close(), updateCustomerProps(data)]);
}

function onOpenModal(type, data) {
    currentModal = new window.psBilling.ModalContainerComponent({
        type,
        context: {
            ...billingContext,
            ...data,
        },
        onCloseModal,
        onEventHook
    });
    currentModal.render('#ps-modal');
};

function updateCustomerProps(data) {
    return customer.updateProps({
        context: {
            ...billingContext,
            ...data,
        },
    });
};

function onEventHook(type, data) {
    // Event hook listener
    switch (type) {
        case window.psBilling.EVENT_HOOK_TYPE.SUBSCRIPTION_CREATED:
            showBillingWrapper();
            break;
        case window.psBilling.EVENT_HOOK_TYPE.SUBSCRIPTION_UPDATED:
            showBillingWrapper();
            break;
    }

}

function showPlanSelection() {
    document.getElementById('billing-plan-selection').style.display = 'block';
    document.getElementById('ps-billing-wrapper').style.display = 'none';
}

function showBillingWrapper() {
    document.getElementById('billing-plan-selection').style.display = 'none';
    document.getElementById('ps-billing-wrapper').style.display = 'block';
    // document.getElementById('fieldset_0').style.display = 'block'
}

// Open the checkout full screen modal
function openCheckout(pricingId) {
    const offerSelection = {offerSelection: {offerPricingId: pricingId}};
    onOpenModal(window.psBilling.MODAL_TYPE.SUBSCRIPTION_FUNNEL, offerSelection);
};

function onOpenFunnel({subscription}) {
    showPlanSelection();
}


