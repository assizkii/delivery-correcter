<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

class nkis_delivery_calc extends CModule {

    var $exclusionAdminFiles;
    var $MODULE_ID = 'nkis.delivery.calc';
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;

    function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . "/version.php");

        $this->exclusionAdminFiles = [
            '..',
            '.',
            'menu.php',
            'operation_description.php',
            'task_description.php'
        ];

        if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
        {
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        }

        $this->MODULE_NAME = Loc::getMessage("DELIVERY_CALC_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("DELIVERY_CALC_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("DELIVERY_CALC_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("DELIVERY_CALC_PARTNER_URI");

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS='Y';
        $this->MODULE_GROUP_RIGHTS = "Y";
    }

    //Определяем место размещения модуля
    public function GetPath($notDocumentRoot = false)
    {
        if($notDocumentRoot)
            return str_ireplace(Application::getDocumentRoot(),'',dirname(__DIR__));
        else
            return dirname(__DIR__);
    }

    //Проверяем что система поддерживает D7
    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    //Проверяем что версия php выше 5.4
    public function isValidPhpVersion()
    {
        $phpVersion = phpversion();
        $shortVersion = explode('.', $phpVersion);
        $version = $shortVersion[0].'.'.$shortVersion[1];
        return ($version >= 5.4);
    }

    //регистрируем события
    function InstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->registerEventHandler("sale","onSaleDeliveryServiceCalculate", $this->MODULE_ID,"\NKis\DeliveryCalc\Calculate","onSaleDeliveryServiceCalculate");
    }

    //удаляем регистрацию событий
    function UnInstallEvents()
    {
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->unRegisterEventHandler("sale","onSaleDeliveryServiceCalculate", $this->MODULE_ID,"\NKis\DeliveryCalc\Calculate","onSaleDeliveryServiceCalculate");
    }

    //устанавливаем модуль
    function DoInstall()
    {
        global $APPLICATION;
        
        if (!$this->isValidPhpVersion()) {
            $APPLICATION->ThrowException(Loc::getMessage("DELIVERY_CALC_INSTALL_ERROR_PHP_VERSION"));
        }
        if($this->isVersionD7())
        {
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallEvents();
        }
        else
        {
            $APPLICATION->ThrowException(Loc::getMessage("DELIVERY_CALC_INSTALL_ERROR_VERSION"));
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage("DELIVERY_CALC_INSTALL_TITLE"), $this->GetPath()."/install/step.php");
    }

    //удаляем модуль
    function DoUninstall()
    {
        global $APPLICATION;

        $this->UnInstallEvents();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(Loc::getMessage("DELIVERY_CALC_UNINSTALL_TITLE"), $this->GetPath()."/install/step.php");

    }

    function GetModuleRightList()
    {
        return array(
            "reference_id" => array("D","K","S","W"),
            "reference" => array(
                "[D] ".Loc::getMessage("DELIVERY_CALC_DENIED"),
                "[K] ".Loc::getMessage("DELIVERY_CALC_READ_COMPONENT"),
                "[S] ".Loc::getMessage("DELIVERY_CALC_WRITE_SETTINGS"),
                "[W] ".Loc::getMessage("DELIVERY_CALC_FULL"))
        );
    }
}