<?php
$moduleId = 'nkis.delivery.calc';
// автозагрузка классов
try {
    \Bitrix\Main\Loader::registerAutoLoadClasses($moduleId,
        [
            'NKis\DeliveryCalc\Location' => 'lib/entities/location.php',
            'NKis\DeliveryCalc\Delivery' => 'lib/entities/delivery.php',
            'NKis\DeliveryCalc\Iblock' => 'lib/entities/iblock.php',
            'NKis\DeliveryCalc\Calculate' => 'lib/calculate.php'
        ]
    );
} catch (\Bitrix\Main\LoaderException $e) {
    die($e->getMessage());
}
