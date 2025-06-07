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
class SupdropShippingService
{
    protected $accountTable = _DB_PREFIX_ . 'webservice_account';
    protected $shopTable = _DB_PREFIX_ . 'shop';
    protected $langTable = _DB_PREFIX_ . 'lang';

    public function checkAuth()
    {
        if (empty(\Tools::getValue('account_id'))) {
            $return['code'] = 233;
            $return['message'] = 'Missing parameter account_id';
            return $return;
        }
        $token = empty($_SERVER['HTTP_AUTHORIZATION']) ? '' : $_SERVER['HTTP_AUTHORIZATION'];
        if (!$token) {
            $headers = getallheaders();
            $token = empty($headers['Authorization']) ? '' : $headers['Authorization'];
            if (!$token) {
                $headers1 = apache_request_headers();
                $token = empty($headers1['Authorization']) ? '' : $headers1['Authorization'];
                if (!$token) {
                    $token = empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) ? '' : $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
                }
            }
        }
        if (!$token) {
            $token = \Tools::getValue('token');
        }
        if (!$token) {
            $return['code'] = 234;
            $return['message'] = 'Unable to obtain Authorization parameters';
            $return['data'] = [
                'header' => $headers,
                'server' => $_SERVER,
                'header1' => $headers1
            ];
            return $return;
        }
        if (!preg_match('/Basic\s+(.*)$/i', $token, $matches)) {
            $return['code'] = 235;
            $return['message'] = 'Unable to obtain Authorization parameters';
            $return['data'] = $token;
            return $return;
        }
        $account_id = \Tools::getValue('account_id');
        list($token, $password) = explode(':', base64_decode($matches[1]));
        $isActive = \Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT active FROM ' . $this->accountTable . '
		WHERE `key` = "' . pSQL($token) . '" and id_webservice_account=' . (int)$account_id);
        if ($isActive) {
            $return['code'] = 0;
            $return['message'] = '';
            return $return;
        }
        $return['code'] = 236;
        $return['message'] = 'No permission';
        $return['data'] = '';
        return $return;
    }

    public function returnInfo($status, $message, $data = [])
    {
        $module = new \supdropshipping();
        $version = $module->version;
        return json_encode(
            [
                'status' => $status,
                'message' => $message,
                'data' => $data,
                'version' => $version
            ]
        );
    }

    public function getFiled($tableName)
    {
        $sql = 'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE table_name="' . $tableName . '"';
        $result = \Db::getInstance()->executeS($sql);
        return array_column($result, 'COLUMN_NAME');
    }

    public function setField($table)
    {
        $field = [];
        $fieldStr = [];
        foreach ($table as $val) {
            $res = $this->getFiled($val);
            $prefex = '';
            foreach ($res as &$rv) {
                $prefex = str_replace(_DB_PREFIX_, '', $val);
                $str = $val . '.`' . $rv . '` as ' . $prefex . '_' . $rv;
                $fieldStr[] = $str;
                $rv = $prefex . '_' . $rv;
            }
            $field[$prefex] = $res;
        }
        return [$field, $fieldStr];
    }

    public function setData($field, $data)
    {
        $return = [];
        foreach ($data as $arr) {
            $array = [];
            foreach ($field as $key => $valField) {
                foreach ($valField as $v) {
                    $array[$key][$v] = empty($arr[$v]) ? '' : $arr[$v];
                }
            }
            $return[] = $array;
        }
        return $return;
    }

    public function getShop($id = '')
    {
        $sql = 'select * from ' . $this->shopTable;
        if ($id) {
            $sql .= ' where id_shop=' . (int)$id;
        }
        $shop = \Db::getInstance()->executeS(
            $sql
        );
        return $shop;
    }

    public function getLang($id = '')
    {
        $sql = 'select * from ' . $this->langTable;
        if ($id) {
            $sql .= ' where id_lang=' . (int)$id;
        }
        $lang = \Db::getInstance()->executeS(
            $sql
        );
        return $lang;
    }
}
