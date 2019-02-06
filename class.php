<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)die();

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader,
    Bitrix\Main\Web\Json;

class customAuthComponent extends CBitrixComponent
{
    /** @const array (do not const for php version compatibility) */
    protected $needModules = array('iblock');

    /** @var array */
    private $errors = array();

    /** @var array Field for ajax request data */
    private $arResponse = array(
        'ERRORS' => array(),
        'HTML' => ''
    );

    function __construct($component = null)
    {
        parent::__construct($component);

        foreach ($this->needModules as $module) {
            if( !Loader::includeModule( $module ) ) {
                $this->errors[] = "No {$module} module.";
            }
        }
    }

    function onPrepareComponentParams($arParams)
    {
        $arParams['IS_AJAX'] = ( isset($arParams['IS_AJAX']) ) ? $arParams['IS_AJAX'] : false;

        return $arParams;
    }

    function executeComponent()
    {
        global $APPLICATION;

        if ($this->arParams['IS_AJAX']) {
            $APPLICATION->RestartBuffer();

            if( "Y" == $this->arParams['NEED_HTML_RESPONSE'] ) {
                ob_start();
                $this->includeComponentTemplate();
                $this->arResponse['HTML'] = ob_get_contents();
                ob_end_clean();
            }

            $this->arResponse['ERRORS'] = $this->errors;

            header('Content-Type: application/json');
            echo Json::encode($this->arResponse);
            $APPLICATION->FinalActions();
            die();
        }
        else {
            $this->arResult['ERRORS'] = $this->errors;

            $this->includeComponentTemplate();
        }
    }
}