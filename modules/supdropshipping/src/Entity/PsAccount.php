<?php
namespace supdropshipping\Entity;
if (!defined('_PS_VERSION_')) {
    exit;
}
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

class PsAccount
{
    public $accountsFacde;

    public function __construct(\PrestaShop\PsAccountsInstaller\Installer\Facade\PsAccounts $accountsFacde)
    {
        $this->accountsFacde = $accountsFacde;
    }

    public function account()
    {
        $accountsService = $this->accountsFacde->getPsAccountsService();
        Media::addJsDef(['contextPsAccounts' => $accountsService->getPsAccountsPresenter()->present($this->name)]);
        return $accountsService;
    }
}