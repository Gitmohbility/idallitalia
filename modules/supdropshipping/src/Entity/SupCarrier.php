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
class SupCarrier
{
    protected $carrierTable = _DB_PREFIX_ . 'carrier';
    protected $groupTable = _DB_PREFIX_ . 'group';
    protected $shopTable = _DB_PREFIX_ . 'shop';
    protected $langTable = _DB_PREFIX_ . 'lang';
    protected $zoneTable = _DB_PREFIX_ . 'zone';
    protected $orderTable = _DB_PREFIX_ . 'orders';
    protected $invoiceTable = _DB_PREFIX_ . 'order_invoice';
    protected $orderCarrierTable = _DB_PREFIX_ . 'order_carrier';

    public function addCarrier()
    {
        $db = \Db::getInstance();
        $service = new SupdropShippingService();
        $res = \Db::getInstance()->executeS(
            'select * from ' . $this->carrierTable . ' order by id_carrier desc limit 1'
        );
        $id_reference = $res[0]['id_reference'] + 1;
        $position = $res[0]['position'] + 1;
        $data = [
            'id_reference' => $id_reference,
            'name' => 'SupdropShipping',
            'url' => 'https://t.17track.net/en#nums=@',
            'active' => 1,
            'position' => $position,
        ];
        $carrier = $db->insert('carrier', $data);
        if ($carrier) {
            $carrier = $db->executeS(
                'select * from ' . $this->carrierTable . ' where name="SupdropShipping" and url="https://t.17track.net/en#nums=@" order by id_carrier desc limit 1'
            );
            $id_carrier = empty($carrier[0]['id_carrier']) ? '' : $carrier[0]['id_carrier'];
            if (!$id_carrier) {
                return false;
            }
            $idGroup = \Db::getInstance()->executeS(
                'select id_group from ' . $this->groupTable
            );
            foreach ($idGroup as &$group) {
                $group['id_carrier'] = $id_carrier;
            }
            $insert['carrier_group'] = $idGroup;
            $rules_group_shop = $idShop = \Db::getInstance()->executeS(
                'select id_shop from ' . $this->shopTable
            );
            foreach ($idShop as &$shop) {
                $shop['id_carrier'] = $id_carrier;
            }
            $insert['carrier_shop'] = $idShop;
            $idLang = \Db::getInstance()->executeS(
                'select id_lang from ' . $this->langTable
            );
            foreach ($idLang as &$lang) {
                $lang['id_carrier'] = $id_carrier;
            }
            $insert['carrier_lang'] = $idLang;
            foreach ($rules_group_shop as &$groupShop) {
                $groupShop['id_carrier'] = $id_carrier;
            }
            $insert['carrier_tax_rules_group_shop'] = $rules_group_shop;
            $idZone = \Db::getInstance()->executeS(
                'select id_zone from ' . $this->zoneTable
            );
            foreach ($idZone as &$zone) {
                $zone['id_carrier'] = $id_carrier;
            }
            $insert['carrier_zone'] = $idZone;
            foreach ($insert as $key => $val) {
                \Db::getInstance()->insert($key, $val);
            }
        } else {
            $service->returnInfo('false', 'Add failed');
        }
        return $service->returnInfo('true', 'success', $carrier[0]);
    }

    public function deliverGoods()
    {
        $service = new SupdropShippingService();
        $idOrder = \Tools::getValue('id_order');
        $trackNnumber = \Tools::getValue('track_number');
        $carrierId = \Tools::getValue('id_carrier');
        $res = \Db::getInstance()->executeS(
            'select * from ' . $this->orderTable . ' as orders
                 left join ' . $this->invoiceTable . ' as invoice on orders.id_order=invoice.id_order
                 where orders.id_order=' . (int)$idOrder
        );
        if (empty($res)) {
            return $service->returnInfo('false', 'Order not found');
        }
        $order = $res[0];
        $carrier = \Db::getInstance()->executeS(
            'select * from ' . $this->carrierTable . ' where id_carrier=' . (int)$carrierId
        );
        if (empty($carrier)) {
            return $service->returnInfo('false', 'Carrier not found');
        }
        $data = [
            'id_order' => $idOrder,
            'id_carrier' => $carrierId,
            'id_order_invoice' => $order['id_order_invoice'],
            'tracking_number' => pSQL($trackNnumber),
            'date_add' => date('Y-m-d H:i:s'),
        ];
        \Db::getInstance()->update('orders', ['delivery_number' => pSQL($trackNnumber), 'current_state' => 5], 'id_order=' . $idOrder);
        \Db::getInstance()->delete('order_carrier', 'id_order=' . $idOrder);
        \Db::getInstance()->insert('order_carrier', $data);
        $orderCarrier = \Db::getInstance()->executeS(
            'select * from ' . $this->orderCarrierTable . '  where id_order=' . (int)$idOrder
        );
        if (empty($orderCarrier)) {
            return $service->returnInfo('false', 'Add failed');
        }
        return $service->returnInfo('true', 'success', $orderCarrier[0]);
    }
}
