<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)die();

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader,
    Bitrix\Main\Web\Json,
    Bitrix\Main\UserConsent\Internals\AgreementTable;

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
        /** $var $arParams['IS_AJAX'] custom param */
        $arParams['IS_AJAX'] = ( isset($arParams['IS_AJAX']) ) ? $arParams['IS_AJAX'] : false;

        /**
         * Set defaults if value is not exists
         */
        $arComponentParameters = array();
        include __DIR__ . '/.parameters.php';

        if( isset($arComponentParameters['PARAMETERS']) && is_array($arComponentParameters['PARAMETERS']) ) {
            foreach ($arComponentParameters['PARAMETERS'] as $ID => $_arParam) {
                if( !isset($_arParam['DEFAULT']) || !empty($arParams[ $ID ]) ) continue;

                $arParams[ $ID ] = $_arParam['DEFAULT'];
            }
        }

        return $arParams;
    }

    function queryAgreement()
    {
        if( !(int) $this->arParams['AGREEMENT_ID'] ) {
            $this->errors['-1'] = 'Fail AGREEMENT_ID validation.';
            return false;
        }

        $arAgreement = array();
        $rsAgreement = AgreementTable::getList( array(
            'select' => array('ID', 'NAME', 'ACTIVE', 'AGREEMENT_TEXT'),
            'filter' => array('ID' => (int) $this->arParams['AGREEMENT_ID']),
            'limit'  => 1,
        ) );

        return $rsAgreement->fetch();
    }

    function queryIblock()
    {
    }

    function executeComponent()
    {
        global $APPLICATION;

        switch ($this->arParams['QUERY_TYPE']) {
            case 'AGREEMENT':
                if( $arAgreement = $this->queryAgreement() ) {
                    $this->arResult['POPUP_ID']   = "modalAgreement{$rand}";
                    $this->arResult['POPUP_NAME'] = $arAgreement['NAME'];
                    $this->arResult['POPUP_TEXT'] = $arAgreement['AGREEMENT_TEXT'];
                }
                break;

            case 'IBLOCK':
            default:
                if( $arElement = $this->queryIblock() ) {
                    $this->arResult['POPUP_ID']   = "modalElement{$rand}";
                    $this->arResult['POPUP_NAME'] = $arElement['NAME'];
                    $this->arResult['POPUP_TEXT'] = $arElement['PREVIEW_TEXT'];
                }
                break;
        }

        $this->arResult['POPUP_WIDTH'] = htmlspecialcharsEx($this->arParams['POPUP_WIDTH']);

        $this->arResult['TARGET'] = '';
        if( 'BUTTON' === $this->arParams['EVENT'] ) {
            if( 'BUTTON' === $this->arParams['TARGET_TYPE'] ) {
                $_target = '<button%s type="button">%s</button>';
            }
            else {
                $_target = '<a%s href="javascript:;" rel="nofollow">%s</a>';
            }

            $arAttrs = array();
            $arAttrs['data-type'] = strtolower($this->arParams['POPUP_TYPE']);
            $arAttrs['data-src'] = '#' . $this->arResult['POPUP_ID'];

            if( 'ajax' == $arAttrs['data-type'] ) {
                $arAttrs['data-filter'] = $arAttrs['data-src'];
                $arAttrs['data-src'] = $this->GetPath() . '/ajax.php';
            }

            $strAttrs = '';
            $strAttrs .= ' data-fancybox';

            foreach ($arAttrs as $arAttrKey => $arAttr) {
                $strAttrs .= " {$arAttrKey}=\"{$arAttr}\"";
            }

            $this->arResult['TARGET'] = sprintf($_target, $strAttrs, $this->arParams['TARGET_MSG']);
        }
        elseif( 'TARGET' === $this->arParams['EVENT'] ) {
            $this->arResult['TARGET'] = htmlspecialcharsEx($this->arParams['TARGET']);
        }

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

            $type = strtolower($arParams['POPUP_TYPE']);
            $this->includeComponentTemplate( in_array($type, array('ajax')) ? $type : null );
        }
    }
}