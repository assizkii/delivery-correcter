<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use NKis\DeliveryCalc;

$moduleId = 'nkis.delivery.calc';

Loc::loadMessages($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");
Loc::loadMessages(__FILE__);

if ($APPLICATION->GetGroupRight($moduleId)<"S")
{
    $APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));
}

try {
    \Bitrix\Main\Loader::includeModule($moduleId);
} catch (\Bitrix\Main\LoaderException $e) {
    die($e->getMessage());
}

$request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

$locations = new DeliveryCalc\Location();
$delivery = new DeliveryCalc\Delivery();
$iblock = new DeliveryCalc\Iblock();


#Описание опций
$options = [
    Loc::getMessage("DELIVERY_SECTION_SETTINGS_TITLE"),
    ['switch',  Loc::getMessage('DELIVERY_SWITCH_PROP_TITLE'), '', ['checkbox']],
    ['regions',  Loc::getMessage('DELIVERY_REGIONS_PROP_TITLE'), '', ['multiselectbox', $locations->getRegionList()]],
    ['deliveries',  Loc::getMessage('DELIVERY_DELIVERIES_PROP_TITLE'), '', ['multiselectbox', $delivery->getDeliveryList()]],
    ['categories',  Loc::getMessage('DELIVERY_CATEGORIES_PROP_TITLE'), '', ['multiselectbox', $iblock->getCategoryList()]],
    ['price',  Loc::getMessage('DELIVERY_PRICE_PROP_TITLE'), '0', ['text']],
    ['text',  Loc::getMessage('DELIVERY_TEXT_PROP_TITLE'), '', ['text']],
    ['sync',  Loc::getMessage('DELIVERY_TERMINAL_SYNC_TITLE'), '', ['text']],
    ['note'	=>	Loc::getMessage("DELIVERY_NOTE_PROP_TITLE")],
];

$tab = [
    'DIV' => 'edit1',
    "TAB" => Loc::getMessage("DELIVERY_TAB_SETTINGS"),
    "TITLE" => Loc::getMessage("DELIVERY_TAB_SETTINGS"),
    'OPTIONS' => $options
];



#Сохранение

if ($request->isPost() && $request['Update'] && check_bitrix_sessid())
{

    foreach ($options as $option)
    {

        if (!is_array($option)) //Строка с подсветкой. Используется для разделения настроек в одной вкладке
            continue;

        if ($option['note']) //Уведомление с подсветкой
            continue;

        $optionName = $option[0];

        $optionValue = $request->getPost($optionName);

        Option::set($moduleId, $optionName, is_array($optionValue) ? implode(",", $optionValue):$optionValue);

    }
}

#Визуальный вывод

$tabControl = new CAdminTabControl('tabControl', [$tab]);

?>
<? $tabControl->Begin(); ?>
<form method='post' action='<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($request['mid'])?>&amp;lang=<?=$request['lang']?>' name='academy_d7_settings'>
    <? $tabControl->BeginNextTab(); ?>
    <? __AdmSettingsDrawList($moduleId, $options); ?>
    <? $tabControl->Buttons(); ?>
    <input type="submit" name="Update" value="<?echo GetMessage('MAIN_SAVE')?>">
    <?=bitrix_sessid_post();?>

</form>
<? $tabControl->End(); ?>

