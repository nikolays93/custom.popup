<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

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

if('BUTTON' === $arParams['EVENT']) echo $arResult['TARGET'];

?>
<style>
    #<?= $arResult['POPUP_ID'] ?> {
        display: none;
        max-width: <?= $arResult['POPUP_WIDTH'] ?>;
    }
    #<?= $arResult['POPUP_ID'] ?> > div {
        overflow-y: auto;
        <? if( $arParams['POPUP_CONTENT_HEIGHT'] ) echo 'max-height: ' . $arParams['POPUP_CONTENT_HEIGHT'] . ';'; ?>
        margin-right: -24px;
        padding-right: 24px;
    }
</style>

<div id="<?= $arResult['POPUP_ID'] ?>" class="<?= $arParams['POPUP_CLASS'] ?>">
    <h4 class="mb-2"><?= $arResult['POPUP_NAME'] ?></h4>

    <div class="<?= $arParams['POPUP_CLASS'] ?>__text">
        <?= $arResult['POPUP_TEXT'] ?>
    </div>
</div>
