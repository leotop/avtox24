<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Avtox24");
$APPLICATION->SetTitle("Обработка VIN");
?>
<?if( in_array(11,CUser::GetUserGroup(CUser::GetID()))||in_array(12,CUser::GetUserGroup(CUser::GetID()))||in_array(1,CUser::GetUserGroup(CUser::GetID()))||in_array(5,CUser::GetUserGroup(CUser::GetID())) )
{
?>
<div><script type="text/javascript">(function (d, w, b, a, p, ph) {w[b + '_params_' + a] = {"license":{"appId":600110,"appKey":"3be11c3546dbde15f58ada7d3d0f8d360830e56d"}};var k = b + '_' + a;if (typeof document.currentScript !== 'undefined') {w[k] = document.currentScript;} else {w[k] = document.getElementsByTagName('script'); w[k] = w[k][w[k].length - 1];}var s = d.createElement('script'), f = function () {w[k].parentNode.insertBefore(s, w[k]);};s.type = 'text/javascript';s.async = true;s.setAttribute('data-main', p + ph + '/' + b + '/browser/bundles/' + b + '.browser.js');s.src = p + ph + '/' + b + '/browser/bower_components/requirejs/require.js';if (w.opera == '[object Opera]') {d.addEventListener('DOMContentLoaded', f, false)} else {f()}})(document, window, 'vinqu', 'f56b89e456db4a59b1679f40a73fd6b9', '//', 'staticfe.nodacdn.net');</script></div>
<?
} ?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>