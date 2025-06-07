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
class SupProduct
{
    protected $attributeTable = _DB_PREFIX_ . 'attribute';
    protected $attributeGroupTable = _DB_PREFIX_ . 'attribute_group';
    protected $attributeGroupLang = _DB_PREFIX_ . 'attribute_group_lang';
    protected $attributeLang = _DB_PREFIX_ . 'attribute_lang';
    protected $cattgoryTable = _DB_PREFIX_ . 'category';
    protected $cattgiryLang = _DB_PREFIX_ . 'category_lang';

    public function getProductOption()
    {
        $attribute_id = \Tools::getValue('attribute_id');
        $attribute = \Db::getInstance()->executeS(
            'select * from ' . $this->attributeTable . ' as ar
                left join ' . $this->attributeGroupTable . ' as gr  on ar.id_attribute_group=gr.id_attribute_group
                left join ' . $this->attributeGroupLang . ' as gl on ar.id_attribute_group=gl.id_attribute_group
                where ar.id_attribute=' . (int)$attribute_id
        );
        $attribute[0]['option'] = \Db::getInstance()->executeS(
            'select * from ' . $this->attributeLang . ' where id_attribute=' . (int)$attribute_id . ' and id_lang=' . (int)$attribute[0]['id_lang']
        );
        $service = new \supdropshipping\Entity\SupdropShippingService();
        return $service->returnInfo('true', 'success', $attribute[0]);
    }

    public function getProductOptions()
    {
        return $this->getOneProductOption();
    }

    public function getOneProductOption($id = '')
    {
        $sql = 'select * from ' . $this->attributeGroupLang;
        if ($id) {
            $sql .= ' where id_attribute_group=' . (int)$id;
        }
        $attributeGroup = \Db::getInstance()->executeS($sql);
        foreach ($attributeGroup as &$val) {
            $option = [];
            $attribute = \Db::getInstance()->executeS(
                'select * from ' . $this->attributeTable . ' where id_attribute_group=' . (int)$val['id_attribute_group']
            );
            foreach ($attribute as $at) {
                $sql = 'select * from ' . $this->attributeLang . ' where id_attribute=' . (int)$at['id_attribute'] . ' and id_lang=' . (int)$val['id_lang'];
                $res = \Db::getInstance()->executeS(
                    $sql
                );
                if($res)$option[] = $res[0];
            }
            $val['option'] = $option;
        }
        $service = new \supdropshipping\Entity\SupdropShippingService();
        return $service->returnInfo('true', 'success', $attributeGroup);
    }

    public function addProductOptin()
    {
        $idGrooup = \Db::getInstance()->executeS('select * from ' . $this->attributeGroupLang . ' where name="' . pSQL(\Tools::getValue('attribute_name')) . '"  order by `id_attribute_group` desc limit 1');
        $service = new SupdropShippingService();
        $lang = $service->getLang();
        $shop = $service->getShop();
        if (count($idGrooup)) {
            $id = $idGrooup[0]['id_attribute_group'];
            \Db::getInstance()->update('attribute_group', ['is_color_group' => 0, 'group_type' => 'select'],'id_attribute_group='.$id);
        } else {
            $position = \Db::getInstance()->getValue('select max(position) from ' . $this->attributeGroupTable);
            +$position += 1;
            $data = [
                'is_color_group' => 0,
                'group_type' => 'select',
                'position' => $position,
            ];
            \Db::getInstance()->insert('attribute_group', $data);
            $id = \Db::getInstance()->Insert_ID();
            if (!count($lang)) {
                return $service->returnInfo('false', 'Failed to add attribute,no language settings found');
            }
            $this->addAttributeGroupLang($id, $lang);
            $this->addAttributeGroupShop($id, $shop);
        }
        $this->addAttribute($id, $lang, $shop);
        return $this->getOneProductOption($id);
    }

    public function addAttributeGroupLang($id, $lang)
    {
        $groupLang = [];
        foreach ($lang as $lan) {
            $groupLang[] = [
                'id_lang' => $lan['id_lang'],
                'id_attribute_group' => $id,
                'name' => \Tools::getValue('attribute_name'),
                'public_name' => \Tools::getValue('attribute_name'),
            ];
        }
        \Db::getInstance()->insert('attribute_group_lang', $groupLang);
        return true;
    }

    public function addAttributeGroupShop($id, $shop)
    {
        $groupShop = [];
        foreach ($shop as $sh) {
            $groupShop[] = [
                'id_attribute_group' => $id,
                'id_shop' => $sh['id_shop'],
            ];
        }
        \Db::getInstance()->insert('attribute_group_shop', $groupShop);
        return true;
    }

    public function addAttribute($id, $lang, $shop)
    {
        $option = \Tools::getValue('options');
        $attribute = \Db::getInstance()->executeS('select * from ' . $this->attributeTable . ' where id_attribute_group=' . (int)$id);
        $ids = '';
        if (count($attribute)) {
            $ids = array_column($attribute, 'id_attribute');
            $ids = implode(',', array_map('intval', $ids));
        }
        $attributeLang = [];
        $attributeShop = [];
        foreach ($option as $key => $val) {
            if ($ids) {
                $res = \Db::getInstance()->executeS('select * from ' . $this->attributeLang . ' where name="' . pSQL($val) . '"  and id_attribute in (' . $ids . ') order by `id_attribute` desc  limit 1');
            }
            if (empty($res)) {
                $data = [
                    'id_attribute_group' => $id,
                    'position' => $key,
                ];
                \Db::getInstance()->insert('attribute', $data);
                $attributeId = \Db::getInstance()->Insert_ID();
                foreach ($lang as $la) {
                    $attributeLang[] = [
                        'id_lang' => $la['id_lang'],
                        'name' => $val,
                        'id_attribute' => $attributeId,
                    ];
                }
                foreach ($shop as $sh) {
                    $attributeShop[] = [
                        'id_shop' => $sh['id_shop'],
                        'id_attribute' => $attributeId,
                    ];
                }
            }
        }
        if (count($attributeLang)) {
            \Db::getInstance()->insert('attribute_lang', $attributeLang);
            \Db::getInstance()->insert('attribute_shop', $attributeShop);
        }
    }

    public function getCategory()
    {
        $categoty = new \Category();
        $categoty->nleft = 0;
        $categoty->nright = 20;
        $langId = \Context::getContext()->language->id;
        $categoty = \Db::getInstance()->executeS(
            '
                    select * from ' . $this->cattgoryTable . ' as c
                    left join ' . $this->cattgiryLang . ' as l on c.id_category=l.id_category
                    where id_shop=' . (int)\Tools::getValue('id_shop') . ' and id_lang=' . (int)$langId
        );
        $service = new SupdropShippingService();
        return $service->returnInfo('true', 'success', $categoty);
    }

    public function addProduct()
    {
        $model = new \Product();
        $model->id_category_default = \Tools::getValue('category')[0];
        $model->id_shop_default = \Tools::getValue('id_shop');
        $model->id_tax_rules_group = \Tools::getValue('id_tax_rules_group');
        $model->minimal_quantity = 1;
        $model->price = \Tools::getValue('price');
        $model->date_add = $model->date_upd = date('Y-m-d H:i:s');
        $model->name = pSQL(\Tools::getValue('title'));
        $model->link_rewrite = \Tools::link_rewrite(pSQL(\Tools::getValue('title')));
        $description = pSQL(\Tools::getValue('description'), true);
        $model->description = $this->changeHtml($description);
        $model->delivery_in_stock = \Tools::getValue('quantity');
        if (!empty(\Tools::getValue('active'))) {
            $model->active = \Tools::getValue('active');
        }
        $model->save();
        $productId = $model->id;
        $this->saveProductCategory($productId);
        $service = new SupdropShippingService();
        list($imgId, $sourceFile) = $this->saveImage($productId);
        $link = $model->getLink();
        $data = [
            'product_id' => $productId,
            'image_id' => $imgId,
            'image_url' => $sourceFile,
            'link' => $link,
            'lang' => \Context::getContext()->language->id,
        ];
        return $service->returnInfo('true', 'success', $data);
    }

    public function saveProductCategory($productId)
    {
        $categorydata = [];
        $position = 1;
        foreach (\Tools::getValue('category') as $value) {
            $categorydata[] = [
                'id_category' => $value,
                'id_product' => $productId,
                'position' => $position,
            ];
        }
        \Db::getInstance()->delete('category_product', 'id_product=' . $productId);
        \Db::getInstance()->insert('category_product', $categorydata);
    }

    public function saveProdcutLang($lang, $productId)
    {
        $langdata = [];
        foreach ($lang as $val) {
            $langdata[] = [
                'description' => pSQL(\Tools::getValue('description')),
                'name' => pSQL(\Tools::getValue('title')),
                'id_shop' => \Tools::getValue('id_shop'),
                'id_lang' => \Tools::getValue('id_lang'),
                'id_product' => $productId,
                'link_rewrite' => pSQL(\Tools::getValue('link_rewrite')),
            ];
        }
        if (count($langdata)) {
            \Db::getInstance()->insert('product_lang', $langdata);
        }
    }

    public function saveProductShop($productId)
    {
        $data = [
            'id_product' => $productId,
            'id_shop' => \Tools::getValue('id_shop'),
            'id_tax_rules_group' => \Tools::getValue('id_tax_rules_group'),
            'minimal_quantity' => \Tools::getValue('quantity'),
            'price' => \Tools::getValue('price'),
            'show_price' => \Tools::getValue('price'),
            'active' => \Tools::getValue('active'),
            'date_add' => date('Y-m-d H:i:s'),
            'date_upd' => date('Y-m-d H:i:s'),
        ];
        \Db::getInstance()->delete('product_shop', 'id_product=' . $productId);
        \Db::getInstance()->insert('product_shop', $data);
    }

    public function saveImage($productId, $cover = 1)
    {
        $image = new \Image(null);
        $image->id_product = $productId;
        $image->cover = $cover;
        $image->save();
        $sourceFile = _PS_IMG_ . 'p/' . $image->getImgPath() . '.jpg';
        $imageService = new SupImage();
        $imageService->saveImg($image->id, \Tools::getValue('url'), $productId);
        return [$image->id, $sourceFile];
    }

    public function saveAttributeActive()
    {
        $service = new SupdropShippingService();
        if (empty(\Tools::getValue('id_attribute'))) {
            return $service->returnInfo('false', 'Please select attribute');
        }
        list($imageId, $url) = $this->saveImage(\Tools::getValue('product_id'), 0);
        $model = new \Product(\Tools::getValue('product_id'));
        $res = $model->addAttribute(
            0,
            \Tools::getValue('weight'),
            \Tools::getValue('price'),
            0,
            $imageId,
            pSQL(\Tools::getValue('sku')),
            pSQL(\Tools::getValue('attribute_ean13')),
            '',
            null,
            null,
            1,
            [\Tools::getValue('id_shop')],
            null,
            \Tools::getValue('quantity'),
            '',
            null,
            false,
            pSQL(\Tools::getValue('sku'))
        );
        $combination = new \Combination();
        $combination->id = $res;
        $combination->setAttributes(\Tools::getValue('id_attribute'));
        $insertImage = [
            'id_product_attribute' => $res,
            'id_image' => $imageId,
        ];
        \Db::getInstance()->insert('product_attribute_image', $insertImage);
        $ids = implode(',', array_map('intval', \Tools::getValue('id_attribute')));
        $lang = \Tools::getValue('lang');
        $attributeLangSql = 'select `name` from ' . $this->attributeLang . ' where id_attribute in (' . $ids . ') and id_lang=' . (int)$lang;
        $attribute = \Db::getInstance()->executeS($attributeLangSql);
        \Db::getInstance()->delete('stock_available', 'id_product_attribute=' . $res);
        $stock = [
            'id_product' => \Tools::getValue('product_id'),
            'id_product_attribute' => $res,
            'quantity' => \Tools::getValue('quantity'),
            'id_shop' => \Tools::getValue('id_shop'),
        ];
        \Db::getInstance()->insert('stock_available', $stock);
        $attribute = array_column($attribute, 'name');
        $data = [
            'id_product_attribute' => $res,
            'id_attribute' => \Tools::getValue('id_attribute'),
            'image_id' => $imageId,
            'image_url' => $url,
            'attribute' => $attribute,
        ];
        return $service->returnInfo('ture', 'success', $data);
    }

    public function delProduct()
    {
        $model = new \Product(\Tools::getValue('id_product'));
        $model->delete();
        $service = new SupdropShippingService();
        return $service->returnInfo('true', 'success');
    }

    public function changeHtml($content)
    {
        $pattern_imgTag = '/<img\b.*?(?:\>|\/>)/i';
        preg_match_all($pattern_imgTag, $content, $matchAll);
        if (!empty($matchAll[0])) {
            foreach ($matchAll[0] as $imgTag) {
                $resStr = str_replace('\\', '', $imgTag);
                $content = str_replace($imgTag, $resStr, $content);
            }
        }
        return $content;
    }

    public function getTaxRule()
    {
        $rule = \TaxRulesGroup::getTaxRulesGroups();
        $service = new SupdropShippingService();
        return $service->returnInfo('ture', 'success', $rule);
    }

    public function getProduct()
    {
        $product_id = \Tools::getValue('product_id');
        $product = new \Product();
        $product->id = $product_id;
        $data = $product->getFields();
        $link = parse_url($product->getLink());
        $domain = is_array($link) ? $link['scheme'] . '://' . $link['host'] : '';
        $langTable = _DB_PREFIX_ . 'product_lang';
        $lang = \Db::getInstance()->executeS('select * from ' . $langTable . ' where id_product=' . pSQL($product_id));
        $idLang = !empty($lang[0]['id_lang']) ? $lang[0]['id_lang'] : 0;
        $name = !empty($lang[0]['name']) ? $lang[0]['name'] : '';
        $sourceFile = '';
        $combinationImages = [];
        $combinations = $product->getAttributeCombinations();
        if ($idLang) {
            $images = $product->getImages($idLang);
            if (isset($images[0]['id_image'])) {
                $imagesService = new \Image($images[0]['id_image']);
                $sourceFile = $domain . _PS_IMG_ . 'p/' . $imagesService->getImgPath() . '.jpg';
            }
            $combinationImages = $product->getCombinationImages($idLang);
        }
        foreach ($combinations as &$val) {
            if (!empty($combinationImages[$val['id_product_attribute']][0])) {
                $imgId = $combinationImages[$val['id_product_attribute']][0]['id_image'];
                $imagesService = new \Image($imgId);
                $val['img_url'] = $domain . _PS_IMG_ . 'p/' . $imagesService->getImgPath() . '.jpg';
            }
        }
        $data['name'] = $name;
        $data['img_url'] = $sourceFile;
        $data['combinations'] = $combinations;
        $service = new SupdropShippingService();
        return $service->returnInfo('ture', 'success', $data);
    }
}
