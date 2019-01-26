<?php
namespace NKis\DeliveryCalc;

use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;



class Delivery  {

    public function __construct()
    {
        try {
            Loader::includeModule('sale');
        } catch (LoaderException $e) {
            die($e->getMessage());
        }
    }

    /**
     * @return array
     */
    public function getDeliveryList()
    {
        $deliveryList = [];
        $deliveries=  \Bitrix\Sale\Delivery\Services\Manager::getActiveList();


        foreach ($deliveries as $delivery) {
            if ($delivery['PARENT_ID']) {
                $deliveryList[$delivery['ID']] = $deliveries[$delivery['PARENT_ID']]['NAME'].' ('. $delivery['NAME']. ')';
            }

        }
        return $deliveryList;
    }


}