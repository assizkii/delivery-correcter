<?php
namespace NKis\DeliveryCalc;
use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Diag\Debug;
use Bitrix\Main\Loader;
use Bitrix\Main\LoaderException;

class Calculate {

    private static $isTerminalCalc;

    private $settings = [
        'regions' => [],
        'deliveries' => [],
        'categories' => [],
        'price' => 0,
        'text' => 'Free',
        'switch' => 'Y',
        'sync'  => []
    ];

    private $moduleId = 'nkis.delivery.calc';

    public function __construct()
    {
        try {
            $this->getSettings();
            Loader::includeModule('sale');
        } catch (ArgumentNullException $e) {
            Debug::dumpToFile($e->getMessage());
        } catch (LoaderException $e) {
            Debug::dumpToFile($e->getMessage());
        }
    }

    public static function onSaleDeliveryServiceCalculate(\Bitrix\Main\Event $event)

    {
        $result = $event->getParameter('RESULT');
        $obShipmentItemCollection = $event->getParameter('SHIPMENT')->getShipmentItemCollection();
        $deliveryId =  $event->getParameter('DELIVERY_ID');

        $shipment = $obShipmentItemCollection->getShipment();
        $basePrice = $result->getDeliveryPrice();

        $order  = $shipment->getCollection()->getOrder();

        global  $USER;
        if ($USER->isAdmin()) {
            $calculate = new Calculate();
            $isFree = $calculate->check($order, $deliveryId);

            if ($isFree && !self::$isTerminalCalc) {

                $profiles = $calculate->getSyncProfile();
                if (isset($profiles[$deliveryId])) {
                    $terminalId = $profiles[$deliveryId];
                    self::$isTerminalCalc = true;
                    $terminalInfo = \Bitrix\Sale\Delivery\Services\Manager::calculateDeliveryPrice($shipment, $terminalId, []);
                    $terminalPrice = $terminalInfo->getDeliveryPrice();

                    $result->setDeliveryPrice($basePrice-$terminalPrice);
                } else {
                    $result->setDeliveryPrice(0);
                }
            }
        }

        self::$isTerminalCalc = false;

        $event->addResult(
            new \Bitrix\Main\EventResult(
                \Bitrix\Main\EventResult::SUCCESS, ['RESULT'=> $result]
            )
        );
    }

    public function check($order, $deliveryId)
    {
        $basket = $order->getBasket();
        $basketItems = $basket->getBasketItems();

        if (!$this->checkSwitch()) return false;
        if (!$this->checkDelivery($deliveryId)) return false;
        if (!$this->checkLocation($order)) return false;
        if (!$this->checkCategory($basketItems)) return false;

        return true;
    }

    private function getSyncProfile()
    {
        $result = [];
        foreach ($this->settings['sync'] as $profiles) {
            $profile = explode(':', $profiles);
            $result[$profile[0]] = $profile[1];
        }
        return $result;
    }

    private function checkSwitch()
    {
        return $this->settings['switch'] === 'Y';
    }

    /**
     * @param $order
     * @return bool
     */
    private function checkLocation($order)
    {
        $propertyCollection = $order->getPropertyCollection();
        $location   = $propertyCollection->getDeliveryLocation()->getValue();

        if (is_integer($location)) {
            $locationInfo = \Bitrix\Sale\Location\LocationTable::getById($location)->fetch();
        } else {
            $locationInfo = \Bitrix\Sale\Location\LocationTable::getByCode($location)->fetch();
        }

        if (in_array($locationInfo['REGION_ID'], $this->settings['regions'])) {
            return true;
        }

        return false;
    }

    /**
     * @param $deliveryId
     * @return bool
     */
    private function checkDelivery($deliveryId)
    {
        if (in_array($deliveryId, $this->settings['deliveries'])) {
            return true;
        }
        return false;
    }

    /**
     * @param $basketItems
     * @return bool
     */
    private function checkCategory($basketItems)
    {
        foreach ($basketItems as $basketItem) {
            $productId = $basketItem->getProductId();
            $productCategory = Iblock::getProductCategory($productId);

            foreach ($productCategory as $category) {
                if (in_array($category['ID'], $this->settings['categories']) || in_array($category['IBLOCK_SECTION_ID'], $this->settings['categories'])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @throws \Bitrix\Main\ArgumentNullException
     */
    private function getSettings()
    {
        $moduleSettings = Option::getForModule($this->moduleId);
        foreach ($moduleSettings as $optionsId => $optionVal) {
            if (is_array($this->settings[$optionsId])) {
                $this->settings[$optionsId] = explode(',', $optionVal);
            } else {
                $this->settings[$optionsId] = $optionVal;
            }
        }
    }


}