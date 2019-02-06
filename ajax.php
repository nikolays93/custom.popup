<?

use Bitrix\Main;

/** @global \CMain $APPLICATION */
define('STOP_STATISTICS', true);
define('NOT_CHECK_PERMISSIONS', true);

$siteId = isset($_REQUEST['siteId']) && is_string($_REQUEST['siteId']) ? $_REQUEST['siteId'] : '';
$siteId = substr(preg_replace('/[^a-z0-9_]/i', '', $siteId), 0, 2);
if (!empty($siteId) && is_string($siteId))
{
	define('SITE_ID', $siteId);
}

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

$request = Main\Application::getInstance()->getContext()->getRequest();
$request->addFilter(new Main\Web\PostDecodeFilter);

if (!Main\Loader::includeModule('iblock'))
	return;

// $signer = new Main\Security\Sign\Signer;
// try
// {
// 	$template = $signer->unsign($request->get('template'), 'custom.popup');
// 	$paramString = $signer->unsign($request->get('parameters'), 'custom.popup');
// }
// catch (Main\Security\Sign\BadSignatureException $e)
// {
// 	die();
// }

// $parameters = unserialize(base64_decode($paramString));
// if (isset($parameters['PARENT_NAME']))
// {
// 	$parent = new CBitrixComponent();
// 	$parent->InitComponent($parameters['PARENT_NAME'], $parameters['PARENT_TEMPLATE_NAME']);
// 	$parent->InitComponentTemplate($parameters['PARENT_TEMPLATE_PAGE']);
// }
// else
// {
// 	$parent = false;
// }

$parameters['IS_AJAX'] = true;
// $APPLICATION->IncludeComponent(
// 	'bitrix:custom.popup',
// 	$template,
// 	$parameters,
// 	$parent
// );