<?php
namespace NKis\DeliveryCalc;

use Bitrix\Main\Loader;
use Bitrix\Iblock as BxIblock;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\LoaderException;

class Iblock  {

    const CATALOG_CODE = 'aspro_mshop_catalog';

    public function __construct()
    {
        try {
            Loader::includeModule('iblock');
        } catch (LoaderException $e) {
            die($e->getMessage());
        }
    }


    /**
     * @return array
     * @throws ArgumentException
     */
    public function getCategoryList()
    {
        $categoryList = [];

        $categories = bxIblock\SectionTable::getList([
           'select' => ['ID', 'NAME'],
           'filter' => ['IBLOCK_ID' => $this->getIblockIdByCode(), 'DEPTH_LEVEL' => 1]
        ]);

        while ($category = $categories->fetch()) {
            $categoryList[$category['ID']] = $category['NAME'];
        }
        return $categoryList;
    }

    /**
     * @return mixed
     */
    private function getIblockIdByCode()
    {
        try {
            $iblock = BxIblock\IblockTable::getList([
                'order' => ['NAME' => 'asc'],
                'select' => ['ID', 'NAME'],
                'filter' => ['CODE' => self::CATALOG_CODE]
            ])->fetch();
        } catch (ArgumentException $e) {
            die($e->getMessage());
        }

        return $iblock['ID'];
    }

    /**
     * @param $productId
     * @return array
     */
    public static function getProductCategory($productId)
    {
        $obCategory = \CIBlockElement::GetElementGroups($productId, false, ['ID', 'IBLOCK_SECTION_ID']);
        $categories = [];
        while($category = $obCategory->Fetch()) {
            $categories[] = $category;
        }
        return $categories;
    }
}