<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/**
 * @var string $componentPath
 * @var string $componentName
 * @var array $arCurrentValues
 * @global CUserTypeManager $USER_FIELD_MANAGER
 */

/**
 * @todo Add cookie attention
 */

use Bitrix\Main\Loader,
    Bitrix\Main\Web\Json,
    Bitrix\Iblock,
    Bitrix\Main\UserConsent\Internals\AgreementTable;

$arComponentParameters = array(
    "PARAMETERS" => array(
        'EVENT' => array(
            'PARENT' => 'BASE',
            'NAME' => 'Тип вызова',
            'TYPE' => 'LIST',
            'VALUES' => array(
                'BUTTON' => 'Нажатие на элемент',
                'TARGET' => 'Клик по селектору',
                'READY' => 'При загрузке страницы',
                'CLOSE' => 'При попытке закрыть страницу',
            ),
            'REFRESH' => 'Y',
            'DEFAULT' => 'BUTTON'
        ),
        'POPUP_TYPE' => array(
            'PARENT' => 'ADDITIONAL_SETTINGS',
            'NAME' => 'Тип инициализации',
            'TYPE' => 'LIST',
            'VALUES' => array(
                'AJAX' => 'Подгрузить при событии',
                'INLINE' => 'Загрузить и спрятать сразу',
                'IMAGE' => 'Изображение',
                'VIDEO' => 'Видео',
                'IFRAME' => 'Фрэйм',
            ),
            'DEFAULT' => 'INLINE',
            'REFRESH' => 'Y',
        ),
        'POPUP_CLASS' => array(
            'PARENT' => 'VISUAL',
            'NAME' => 'Класс всплывающего окна',
            'TYPE' => 'STRING',
            'DEFAULT' => 'fancy-modal',
        ),
        'POPUP_WIDTH' => array(
            'PARENT' => 'VISUAL',
            'NAME' => 'Ширина всплывающего окна',
            'TYPE' => 'STRING',
            'DEFAULT' => '650px',
        ),
        'POPUP_CONTENT_HEIGHT' => array(
            'PARENT' => 'VISUAL',
            'NAME' => 'Макс. высота контента',
            'TYPE' => 'STRING',
            'DEFAULT' => '75vh', // For example: 420px, 50vh, calc(50vh - 420px)
        ),
        'ENQUEUE_LIB' => array(
            'PARENT' => 'ADDITIONAL_SETTINGS',
            'NAME' => 'Подключить библиотеку',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
        'ENQUEUE_THEME' => array(
            'PARENT' => 'ADDITIONAL_SETTINGS',
            'NAME' => 'Подключить демо стили',
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'N',
        ),
        // 'CACHE_TIME' => array('DEFAULT' => 120),
    ),
);

/**
 * Event triggers
 */
switch ( $arCurrentValues['EVENT'] ) {
    case 'TARGET':
        $arComponentParameters['PARAMETERS']['TARGET'] = array(
            'PARENT' => 'BASE',
            'NAME' => 'CSS/jQuery селектор',
            'TYPE' => 'STRING',
            'DEFAULT' => '#callback.cb--1',
        );
        break;

    case 'READY':
        $arComponentParameters['PARAMETERS']['READY'] = array(
            'PARENT' => 'BASE',
            'NAME' => 'Кол-во секунд ожидания',
            'TYPE' => 'STRING',
            'DEFAULT' => '4',
        );
        break;

    case 'CLOSE':
        /**
         * @todo add future
         */
        // $arComponentParameters['PARAMETERS']['CLOSE'] = array(
        //  'PARENT' => 'EVENT',
        //  'NAME' => 'Тип события',
        //  'TYPE' => 'LIST',
        //  'VALUES' => array(
        //      'LINK' => 'При попытке убрать курсор',
        //      'HOVER_TOP' => 'При наведении мышки вверх',
        //  ),
        // );
        break;

    case 'BUTTON':
    default:
        $arComponentParameters['PARAMETERS']['TARGET_TYPE'] = array(
            'PARENT' => 'BASE',
            'NAME' => 'Стиль элемента',
            'TYPE' => 'LIST',
            'VALUES' => array(
                'LINK' => 'Ссылка',
                'BUTTON' => 'Кнопка',
            ),
        );

        $arComponentParameters['PARAMETERS']['TARGET_MSG'] = array(
            'PARENT' => 'BASE',
            'NAME' => 'Текст ссылки/кнопки',
            'TYPE' => 'STRING',
            'DEFAULT' => 'Заказать звонок' // for example
        );
        break;
}

/**
 * Query element
 */

/** @var bool if is type not query exists */
$urlOnly = in_array($arCurrentValues['POPUP_TYPE'], array('IMAGE', 'VIDEO', 'IFRAME'));

if( $urlOnly ) {
    $arComponentParameters['PARAMETERS']['QUERY'] = array(
        'PARENT' => 'DATA_SOURCE',
        'NAME' => 'Ссылка на источник',
        'TYPE' => 'STRING',
    );
}
else {
    $arComponentParameters['PARAMETERS']['QUERY_TYPE'] = array(
        'PARENT' => 'DATA_SOURCE',
        'NAME' => 'Тип источника',
        'TYPE' => 'LIST',
        'VALUES' => array(
            'IBLOCK' => 'Инфоблок',
            'AGREEMENT' => 'Соглашение',
        ),
        'REFRESH' => 'Y',
    );

    switch ( $arCurrentValues['QUERY_TYPE'] ) {
        case 'AGREEMENT':
            $arAgreements = array();
            $rsAgreements = AgreementTable::getList( array(
                'select' => array('ID', 'NAME'),
            ) );

            foreach ($rsAgreements as $agreement) {
                $arAgreements[ $agreement['ID'] ] = sprintf('[%d] %s',
                    $agreement['ID'],
                    $agreement['NAME']
                );
            }

            $arComponentParameters['PARAMETERS']['AGREEMENT_ID'] = array(
                'PARENT' => 'DATA_SOURCE',
                'NAME' => 'Условие',
                'TYPE' => 'LIST',
                'VALUES' => $arAgreements,
            );
            break;

        case 'IBLOCK':
        default:
            if (!Loader::includeModule('iblock')) return;

            $arIBlockTypes = CIBlockParameters::GetIBlockTypes();
            reset($arIBlockTypes);

            /**
             * @var array List iblocks id array by selected IBLOCK_TYPE
             */
            $arIblocks = array();
            $rsIblocks = Iblock\IblockTable::getList( array(
                'select' => array('ID', 'NAME'),
                'filter' => array(
                    'IBLOCK_TYPE_ID' => !empty( $arCurrentValues['IBLOCK_TYPE'] ) ?
                        (string) $arCurrentValues['IBLOCK_TYPE'] :
                        key($arIBlockTypes)
                ),
            ) );

            foreach ($rsIblocks as $iblock) {
                $arIblocks[ $iblock['ID'] ] = sprintf('[%d] %s',
                    $iblock['ID'],
                    $iblock['NAME']
                );
            }

            /**
             * @var array List sections id array by selected IBLOCK_ID
             */
            $arSections = array();
            if( !empty($arCurrentValues['IBLOCK_ID']) ) {
                $rsSections = Iblock\SectionTable::getList( array(
                    'select' => array('ID', 'NAME'),
                    'filter' => array('IBLOCK_ID' => (int) $arCurrentValues['IBLOCK_ID']),
                ) );

                foreach ($rsSections as $section) {
                    $arSections[ $section['ID'] ] = sprintf('[%d] %s',
                        $section['ID'],
                        $section['NAME']
                    );
                }
            }

            /**
             * @var array List elements by selected IBLOCK_ID && SECTION_ID (if is selected)
             */
            $arElements = array();
            if( !empty($arCurrentValues['IBLOCK_ID']) ) {
                /** @var $arCurrentValues['IBLOCK_ID'] Int | @todo Array */
                $arElementsFilter = array('IBLOCK_ID' => (int) $arCurrentValues['IBLOCK_ID']);

                if( !empty($arCurrentValues['SECTION_ID']) ) {
                    /** @var $arCurrentValues['SECTION_ID'] Int | Array */
                    $arElementsFilter[] = array(
                        'IBLOCK_SECTION_ID' => is_array($arCurrentValues['SECTION_ID']) ?
                            array_map('intval', $arCurrentValues['SECTION_ID']) :
                            (int) $arCurrentValues['SECTION_ID']
                    );
                }

                $rsElements = Iblock\ElementTable::getList( array(
                    'select' => array('ID', 'NAME'),
                    'filter' => $arElementsFilter,
                ) );

                foreach ($rsElements as $element) {
                    $arElements[ $element['ID'] ] = sprintf('[%d] %s',
                        $element['ID'],
                        $element['NAME']
                    );
                }
            }

            $arComponentParameters['PARAMETERS']['IBLOCK_TYPE'] = array(
                'PARENT' => 'DATA_SOURCE',
                'NAME' => 'Тип инфоблока', // GetMessage('IBLOCK_TYPE'),
                'TYPE' => 'LIST',
                'VALUES' => $arIBlockTypes,
                'REFRESH' => 'Y',
            );

            $arComponentParameters['PARAMETERS']['IBLOCK_ID'] = array(
                'PARENT' => 'DATA_SOURCE',
                'NAME' => 'Инфоблок',// GetMessage('IBLOCK_IBLOCK'),
                'TYPE' => 'LIST',
                'ADDITIONAL_VALUES' => 'Y',
                'VALUES' => $arIblocks,
                'REFRESH' => 'Y',
            );

            $arComponentParameters['PARAMETERS']['SECTION_ID'] = array(
                'PARENT' => 'DATA_SOURCE',
                'NAME' => 'Раздел', // GetMessage('IBLOCK_SECTION_ID')
                'TYPE' => 'LIST',
                'DEFAULT' => '={$_REQUEST["SECTION_ID"]}',
                'ADDITIONAL_VALUES' => 'Y',
                'VALUES' => $arSections,
                'REFRESH' => 'Y',
                // MULTIPLE
            );

            $arComponentParameters['PARAMETERS']['ELEMENT_ID'] = array(
                'PARENT' => 'DATA_SOURCE',
                'NAME' => 'Элемент',
                'TYPE' => 'LIST',
                'DEFAULT' => '={$_REQUEST["ELEMENT_ID"]}',
                'ADDITIONAL_VALUES' => 'Y',
                'VALUES' => $arElements,
            );
            break;
    }
}
