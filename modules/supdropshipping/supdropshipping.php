<?php
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
 * @copyright 2010-2022 Supdropshipping
 * @license   https://www.supdropshipping.com Academic Free License 1.0 (AFL-3.0)
 */

use PrestaShop\PrestaShop\Core\Addon\Module\ModuleManagerBuilder;

if (!defined('_PS_VERSION_')) {
    exit;
}
require_once __DIR__ . '/vendor/autoload.php';

class supdropshipping extends Module
{
    private $container;
    private $sandbox = false;

    public function __construct()
    {
        $this->module_key = 'bd2685cdac5cbe3709dd4fc12a2f4f32';
        $this->name = 'supdropshipping';
        $this->tab = 'content_management';
        $this->version = '1.1.2';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = [
            'min' => '1.8',
            'max' => _PS_VERSION_,
        ];
        parent::__construct();
        $this->author = $this->trans('Sup Dropshipping');
        $this->displayName = $this->trans('Sup Dropshipping');
        $this->description = $this->trans('Sup Dropshipping is dedicated to optimizing your margins by getting access to direct factories and wholesale suppliers. We help you lower your purchasing cost. Sup offers professional services to help you build and grow your E-commerce brand. From customizing your products to improve unboxing experience of your customers, we can handle everything. Sup automatically syncs your Shopify orders. You just need to confirm, and everything else will be handled by Sup team.');
        $this->confirmUninstall = $this->trans('Are you sure you want to uninstall?');
        if ($this->container === null) {
            $this->container = new \PrestaShop\ModuleLibServiceContainer\DependencyInjection\ServiceContainer(
                $this->name,
                $this->getLocalPath()
            );
        }
    }

    public function install()
    {
        $mboStatus = new Prestashop\ModuleLibMboInstaller\Presenter();
        $mboStatus = $mboStatus->present();
        if (!$mboStatus['isInstalled']) {
            $mboInstaller = new Prestashop\ModuleLibMboInstaller\Installer(_PS_VERSION_);
            $mboInstaller->installModule();
            $this->installDependencies();
        } else {
            $this->installDependencies();
        }
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        return parent::install() && $this->getService('supdropshipping.ps_accounts_installer');
    }


    public function installDependencies()
    {
        $moduleManager = ModuleManagerBuilder::getInstance()->build();

        /* PS Account */
        if (!$moduleManager->isInstalled('ps_accounts')) {
            $moduleManager->install('ps_accounts');
        } else if (!$moduleManager->isEnabled('ps_accounts')) {
            $moduleManager->enable('ps_accounts');
            $moduleManager->upgrade('ps_accounts');
        } else {
            $moduleManager->upgrade('ps_accounts');
        }

//        /* Cloud Sync - PS Eventbus */
        if (!$moduleManager->isInstalled('ps_eventbus')) {
            $moduleManager->install('ps_eventbus');
        } else if (!$moduleManager->isEnabled('ps_eventbus')) {
            $moduleManager->enable('ps_eventbus');
            $moduleManager->upgrade('ps_eventbus');
        } else {
            $moduleManager->upgrade('ps_eventbus');
        }
    }

    public function uninstall()
    {
        $account = Db::getInstance()->executeS(
            'select id_webservice_account from ' . _DB_PREFIX_ . 'webservice_account where module_name="supdropshipping"'
        );
        foreach ($account as $val) {
            Db::getInstance()->delete('webservice_permission', 'id_webservice_account=' . $val['id_webservice_account']);
        }
        Db::getInstance()->delete('webservice_account', 'module_name="supdropshipping"');
        return parent::uninstall() && Configuration::updateValue('PS_WEBSERVICE', 0);
    }

    public function getContent()
    {
        try {
            $accountsFacade = $this->getService('supdropshipping.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        } catch (\PrestaShop\PsAccountsInstaller\Installer\Exception\InstallerException $e) {
            $mboStatus = (new Prestashop\ModuleLibMboInstaller\Presenter)->present();
            if (!$mboStatus['isInstalled']) {
                $mboInstaller = new Prestashop\ModuleLibMboInstaller\Installer(_PS_VERSION_);
                $mboInstaller->installModule();
                $this->installDependencies();
            }
            $accountsFacade = $this->getService('supdropshipping.ps_accounts_facade');
            $accountsService = $accountsFacade->getPsAccountsService();
        }
        $context = new Context();
        $wrapper = new \PrestaShopCorp\Billing\Wrappers\BillingContextWrapper($accountsFacade, $context, $this->sandbox);
        $module = Module::getInstanceByName('supdropshipping');
        $billingFacade = new PrestaShopCorp\Billing\Presenter\BillingPresenter($wrapper, $module);
        $billingService = new \PrestaShopCorp\Billing\Services\BillingService($wrapper, $module);
        $res = $billingService->getCurrentSubscription();
        $isLink = $accountsService->isAccountLinked();
        if (Tools::isSubmit('submit' . $this->name)) {
            if (empty($res['success']) || !$isLink) {
                $this->context->controller->errors[] = 'Please,check you plan';
                return '';
            }
            $key = md5(time());
            $apiAccess = new WebserviceKey();
            $apiAccess->key = $key;
            $apiAccess->save();
            $id = $apiAccess->id;
            Db::getInstance()->update('webservice_account',
                ['module_name' => 'supdropshipping'],
                'id_webservice_account=' . $id
            );
            $permissions = $this->getPermissionData();
            WebserviceKey::setPermissionForAccount($id, $permissions) && Configuration::updateValue('PS_WEBSERVICE', 1);
            $account = Db::getInstance()->executeS(
                '
                        select account.id_webservice_account,account.key,account_shop.id_shop,shop.name as shop_name from ' . _DB_PREFIX_ . 'webservice_account account
                        left join ' . _DB_PREFIX_ . 'webservice_account_shop account_shop on account.id_webservice_account=account_shop.id_webservice_account
                        left join ' . _DB_PREFIX_ . 'shop shop on account_shop.id_shop=shop.id_shop
                        where account.module_name="supdropshipping" limit 1
                      '
            );
            $account = $account[0];
            $account['domain'] = $_SERVER['HTTP_ORIGIN'];
            $url = 'https://www.supdropshipping.com/api/prestashop/auth.json?' . http_build_query($account);
            Tools::redirect($url);
        }
        try {
            Media::addJsDef([
                'contextPsAccounts' => $accountsFacade->getPsAccountsPresenter()->present($this->name),
            ]);
        } catch (Exception $e) {
            $this->context->controller->errors[] = $e->getMessage();
            return '';
        }
        $partnerLogo = $this->getLocalPath() . 'logo.png';
        Media::addJsDef($billingFacade->present([
            'logo' => $partnerLogo,
            'tosLink' => 'https://www.supdropshipping.com/term-of-use/',
            'privacyLink' => 'https://www.supdropshipping.com/privacy-policy/',
            'emailSupport' => 'info@supdropshipping.com',
        ]));
        $productComponents = $billingService->getProductComponents();
        $componentItems = [];
        if (!empty($productComponents['body']['items'])) {
            $componentItemsTmp = $productComponents['body']['items'];
            $planInfos = [
                [
                    'component_ids' => ['supdropshipping_basic-EUR-Monthly'],
                    'name' => 'BASIC',
                    'features' => []
                ],
                [
                    'component_ids' => ['supdropshipping_pro-EUR-Monthly'],
                    'name' => 'PRO',
                    'features' => []
                ],
                [
                    'component_ids' => ['supdropshipping_premium-EUR-Monthly'],
                    'name' => 'PREMIUM',
                    'features' => []
                ],

            ];
            foreach ($planInfos as $planInfo) {
                foreach ($planInfo['component_ids'] as $component_id) {
                    $componentItemIndex = array_search($component_id, array_column($componentItemsTmp, 'id'));
                    if ($componentItemIndex !== false) {
                        $componentItemsTmp[$componentItemIndex]['details'] = [
                            'features' => $planInfo['features'],
                            'name' => $planInfo['name'],
                        ];

                        // We store only components that are referenced in $planInfos, and keep the order from $planInfos
                        array_push($componentItems, $componentItemsTmp[$componentItemIndex]);
                    }
                }
            }
        }
        if (!empty($res['success'])) {
            $subscription = $res['body'];
            $subscriptionStatus = $res['body']['status'];
        } else {
            $subscription = [];
            $subscriptionStatus = 'cancelled';
        }
        if(!empty($subscription) && $subscriptionStatus != 'cancelled'){
            $hasSubscription = 1;
        }else{
            $hasSubscription = 0;
        }
        $this->context->smarty->assign([
            'componentItems' => $componentItems,
            'urlBilling' => 'https://unpkg.com/@prestashopcorp/billing-cdc/dist/bundle.js',
            'urlConfigureJs' => $this->getPathUri() . 'views/js/configure.js',
            'urlAccountsCdn' => $accountsService->getAccountsCdn(),
            'subscription' => $subscription,
            'hasSubscription' => $hasSubscription,
        ]);
        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');
        return $output . $this->displayForm();
    }

    public function displayForm()
    {
        $path = $this->_path . '/views/css/supdropshipping.css';
        $this->context->controller->addCSS($path);
        $form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'submit' => [
                    'type' => 'shop',
                    'title' => 'Connect SupDropshipping Account',
                    'class' => 'SupdropShippingButton',
                ],
            ],
        ];
        $lang = Configuration::get('PS_LANG_DEFAULT');
        $helper = new HelperForm();
        $helper->table = $this->table;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
        $helper->submit_action = 'submit' . $this->name;
        $helper->default_form_language = $lang;
        $helper->fields_value['MYMODULE_CONFIG'] = Tools::getValue('MYMODULE_CONFIG', Configuration::get('MYMODULE_CONFIG'));
        return $helper->generateForm([$form]);
    }

    public function getPermissionData()
    {
        $permissions = [
            'products' => [
                'GET' => 1,
                'POST' => 1,
                'PUT' => 1,
                'DELETE' => 1,
                'HEAD' => 1,
            ],
            'currencies' => [
                'GET' => 1,
            ],
            'combinations' => [
                'GET' => 1,
                'POST' => 1,
                'PUT' => 1,
                'DELETE' => 1,
                'HEAD' => 1,
            ],
            'images' => [
                'GET' => 1,
                'POST' => 1,
                'PUT' => 1,
                'DELETE' => 1,
                'HEAD' => 1,
            ],
            'image_types' => [
                'GET' => 1,
                'POST' => 1,
                'PUT' => 1,
                'DELETE' => 1,
                'HEAD' => 1,
            ],
            'product_options' => [
                'GET' => 1,
            ],
            'product_option_values' => [
                'GET' => 1,
            ],
            'countries' => [
                'GET' => 1,
            ],
            'order_state' => [
                'GET' => 1,
                'PUT' => 1,
            ],
            'states' => [
                'GET' => 1,
            ],
            'languages' => [
                'GET' => 1,
            ],
            'tax_rule_groups' => [
                'GET' => 1,
            ],
        ];
        return $permissions;
    }

    public function getService($serviceName)
    {
        return $this->container->getService($serviceName);
    }
}
