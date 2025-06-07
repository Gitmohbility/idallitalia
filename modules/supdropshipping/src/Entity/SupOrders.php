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
class SupOrders
{
    protected $orderDetailTable = _DB_PREFIX_ . 'order_detail';

    public function getOrders()
    {
        $service = new SupdropShippingService();
        $table = [
            _DB_PREFIX_ . 'orders',
            _DB_PREFIX_ . 'address',
            _DB_PREFIX_ . 'currency',
            _DB_PREFIX_ . 'country',
            _DB_PREFIX_ . 'state',
        ];
        list($field, $fieldStr) = $service->setField($table);
        $sql = $this->setBaseSql($fieldStr);
        $page = \Tools::getValue('page');
        $num = \Tools::getValue('limit');
        $where = '';
        if (!empty(\Tools::getValue('start'))) {
            $start = pSQL(\Tools::getValue('start'));
            $where = 'invoice_date>="' . $start . '"';
        }
        if (!empty(\Tools::getValue('end'))) {
            $end = pSQL(\Tools::getValue('end'));
            if ($where) {
                $where .= ' and ' . _DB_PREFIX_ . 'orders.invoice_date<"' . $end . '"';
            } else {
                $where = _DB_PREFIX_.'orders.invoice_date<"' . $end . '"';
            }
        }
        if ($where) {
            $sql = $sql . ' where ' . $where;
        }
        $sql = $sql . ' order by '._DB_PREFIX_.'orders.id_order desc  limit ' . (int)$page . ',' . (int)$num;
        return $this->setReturn($sql, $field);
    }

    public function getOrder()
    {
        $service = new SupdropShippingService();
        $table = [
            _DB_PREFIX_ . 'orders',
            _DB_PREFIX_ . 'address',
            _DB_PREFIX_ . 'currency',
            _DB_PREFIX_ . 'country',
            _DB_PREFIX_ . 'state',
        ];
        list($field, $fieldStr) = $service->setField($table);
        $sql = $this->setBaseSql($fieldStr);
        $id_order = \Tools::getValue('id_order');
        $sql .= ' where ' . _DB_PREFIX_ . 'orders.id_order=' . (int)$id_order;
        return $this->setReturn($sql, $field);
    }

    public function setReturn($sql, $field)
    {
        $service = new SupdropShippingService();
        $res = \Db::getInstance()->executeS(
            $sql
        );
        $data = [];
        if (count($res)) {
            $return = $service->setData($field, $res);
            $data = [];
            $table = [
                _DB_PREFIX_ . 'order_detail',
            ];
            list($field, $fieldStr) = $service->setField($table);
            $fieldStr = implode(',', $fieldStr);
            foreach ($return as &$val) {
                $sql = 'select ' . $fieldStr . ' from ' . $this->orderDetailTable . ' where id_order=' . (int)$val['orders']['orders_id_order'];
                $ps_order_detail = \Db::getInstance()->executeS($sql);
                $quantity = array_column($ps_order_detail, 'order_detail_product_quantity');
                $val['orders']['quantity'] = array_sum($quantity);
                $val['order_detail'] = $ps_order_detail;
            }
            if (!empty(\Tools::getValue('id_order'))) {
                $data = $return[0];
            } else {
                $data = $return;
            }
        }
        return $service->returnInfo('true', 'success', $data);
    }

    public function setBaseSql($field)
    {
        $prefix = _DB_PREFIX_;
        $field = implode(',', $field);
        $sql = 'SELECT ' . $field . '  FROM ' . _DB_PREFIX_ . 'orders';
        $sql = $sql . ' LEFT JOIN ' . _DB_PREFIX_ . 'address ' . $prefix . 'address ON ' . $prefix . 'orders.id_address_delivery = ' . $prefix . 'address.id_address';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'currency as ' . $prefix . 'currency on ' . $prefix . 'orders.id_currency = ' . $prefix . 'currency.id_currency';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'country as ' . $prefix . 'country on ' . $prefix . 'country.id_country=' . $prefix . 'address.id_country';
        $sql .= ' LEFT JOIN ' . _DB_PREFIX_ . 'state as ' . $prefix . 'state ON ' . $prefix . 'state.id_state=' . $prefix . 'address.id_state';
        return $sql;
    }
}
