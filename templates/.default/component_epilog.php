<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Vars cloned from template. @todo check this.
 */
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var customOrderComponent $component */

if('TARGET' === $arParams['EVENT']) {
    ?>
    <script type="text/javascript">
        $('<?= $arResult['TARGET'] ?>').on('click', function(event) {
            event.preventDefault();

            $.fancybox.open({
                src  : '#<?= $arResult['POPUP_ID'] ?>',
                type : 'inline'
            });
        });
    </script>
    <?php
}
