<?php
namespace NKis\DeliveryCalc;

use Bitrix\Sale\Location as bxLocation;
use Bitrix\Main\Loader;

class Location  {

    const REGION_TYPE_ID = 3;

    public function __construct()
    {
        Loader::includeModule('sale');
    }

    /**
     * @return array
     * @throws \Bitrix\Main\ArgumentException
     */
    public function getRegionList()
    {
        $regions = [];

        $res = bxLocation\LocationTable::getList(
            [
                'order' => ['NAME_RU' => 'asc'],
                'filter' => [
                    '=NAME.LANGUAGE_ID' => 'ru',
                    '=TYPE.ID' => self::REGION_TYPE_ID,
                ],
                'select' => [
                    'ID',
                    'NAME_RU' => 'NAME.NAME',
                ]
            ]
        );

        while ($item = $res->fetch()) {
            $regions[$item["ID"]] = $item['NAME_RU'];
        }

        return $regions;
    }

}