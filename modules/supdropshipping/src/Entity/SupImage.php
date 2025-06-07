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

class SupImage
{
    protected $productAttributeTable = _DB_PREFIX_ . 'product_attribute';

    public function getImagePath()
    {
        $id_image = \Tools::getValue('image_id');
        $imageData = new \Image($id_image);
        $path = _THEME_PROD_DIR_ . $imageData->getImgPath();
        $format = $imageData->image_format;
        $service = new SupdropShippingService();
        return $service->returnInfo('true', 'success', ['path' => $path . '.' . $format]);
    }

    public function getImageArr()
    {
        $id_image = \Tools::getValue('image_id');
        $id_images = explode(',', $id_image);
        $pathArr = [];
        foreach ($id_images as $val) {
            $imageData = new \Image($val);
            $path = _THEME_PROD_DIR_ . $imageData->getImgPath();
            $format = $imageData->image_format;
            $pathArr[] = $path . '.' . $format;
        }
        $service = new SupdropShippingService();
        return $service->returnInfo('true', 'success', $pathArr);
    }

    public function uploadImage()
    {
        $image = new \Image(null);
        $image->id_product = \Tools::getValue('product_id');
        $image->save();
        $this->saveImg($image->id, \Tools::getValue('url'), \Tools::getValue('product_id'));
        $sourceFile = _PS_IMG_ . 'p/' . $image->getImgPath() . '.jpg';
        $service = new SupdropShippingService();
        $attribute = \Db::getInstance()->executeS(
            'select `id_product_attribute` from ' . $this->productAttributeTable . ' where id_product=' . (int)\Tools::getValue('product_id')
        );
        if (count($attribute)) {
            $imageData = [];
            foreach ($attribute as $value) {
                $imageData[] = [
                    'id_product_attribute' => $value['id_product_attribute'],
                    'id_image' => $image->id,
                ];
            }
            \Db::getInstance()->insert('product_attribute_image', $imageData);
        }
        return $service->returnInfo('true', 'success', [$image->id, $sourceFile]);
    }

    public function saveImg($id_image, $url, $id_entity)
    {
        $ssl = [
            'peer_name' => 'generic-server',
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ];
        $defaultConfig = ['ssl' => $ssl];
        stream_context_set_default($defaultConfig);
        $tmpfile = tempnam(_PS_TMP_IMG_DIR_, 'ps_import') . '.jpg';
        $this->getContents($url, $tmpfile);
        if (!\ImageManager::checkImageMemoryLimit($tmpfile)) {
            return false;
        }
        $image_obj = new \Image($id_image);
        $path = $image_obj->getPathForCreation();
        $tgt_width = $tgt_height = 0;
        $src_width = $src_height = 0;
        $error = 0;
        $entity = 'products';
        \ImageManager::resize($tmpfile, $path . '.jpg', null, null, 'jpg', false, $error, $tgt_width, $tgt_height, 5, $src_width, $src_height);
        $images_types = \ImageType::getImagesTypes($entity, true);
        $previous_path = null;
        $jpgPanth = $path . '.jpg';
        $path_infos[] = [
            $tgt_width,
            $tgt_height,
            $jpgPanth,
        ];
        foreach ($images_types as $image_type) {
            self::get_best_path($image_type['width'], $image_type['height'], $path_infos);
            $jpgPath = $path . '-' . stripslashes($image_type['name']) . '.jpg';
            if (\ImageManager::resize(
                $tmpfile,
                $jpgPath,
                $image_type['width'],
                $image_type['height'],
                'jpg',
                false,
                $error,
                $tgt_width,
                $tgt_height,
                5,
                $src_width,
                $src_height
            )) {
                if ($tgt_width <= $src_width && $tgt_height <= $src_height) {
                    $path_infos[] = [
                        $tgt_width,
                        $tgt_height,
                        $jpgPath,
                    ];
                }
                if ($entity == 'products') {
                    if (is_file(_PS_TMP_IMG_DIR_ . 'product_mini_' . $id_entity . '.jpg')) {
                        unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . $id_entity . '.jpg');
                    }
                    if (is_file(_PS_TMP_IMG_DIR_ . 'product_mini_' . $id_entity . '_' . \Context::getContext()->shop->id . '.jpg')) {
                        unlink(_PS_TMP_IMG_DIR_ . 'product_mini_' . $id_entity . '_' . \Context::getContext()->shop->id . '.jpg');
                    }
                }
            }
            \Hook::exec(
                'actionWatermark',
                [
                    'id_image' => $id_image,
                    'id_product' => $id_entity,
                ]
            );
        }
        return $path_infos;
    }

    protected static function get_best_path($tgt_width, $tgt_height, $path_infos)
    {
        $path_infos = array_reverse($path_infos);
        $path = '';
        foreach ($path_infos as $path_info) {
            list($width, $height, $path) = $path_info;
            if ($width >= $tgt_width && $height >= $tgt_height) {
                return $path;
            }
        }
        return $path;
    }

    public function getContents($url, $path)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        $fp = fopen($path, 'w+');
        curl_setopt($curl, CURLOPT_FILE, $fp);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        curl_exec($curl);
        curl_close($curl);
        fclose($fp);
    }
}
