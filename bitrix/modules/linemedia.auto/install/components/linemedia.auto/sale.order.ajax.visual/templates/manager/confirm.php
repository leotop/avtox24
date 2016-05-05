<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if (!($arOrder = CSaleOrder::GetByID((int)$_REQUEST['ORDER_ID'])))
{
	echo GetMessage("SOA_TEMPL_ERROR_ORDER_LOST", array("#ORDER_ID#" => (int)$_REQUEST['ORDER_ID']));
}
else
{

	$rsUser = CUser::GetByID($arOrder['USER_ID']);
	$arUser = $rsUser->Fetch();
	
	?>
	<b><?=GetMessage("SOA_TEMPL_ORDER_COMPLETE")?></b><br /><br />
	<table class="sale_order_full_table">
		<tr>
			<td>
				<?= GetMessage("SOA_TEMPL_ORDER_USER_SUC", Array("#ORDER_DATE#" => $arOrder["DATE_INSERT"], "#ORDER_ID#" => $_REQUEST['ORDER_ID'], "#ORDER_USER#" =>$arUser['NAME']." ".$arUser['LAST_NAME']. '
				(' . $arUser['LOGIN'].')'))?>
			<td>
		</tr>
	</table>
<? /*
	* Бланк счета на оплату открываем в новом окне
	*/?>
	<script type='text/javascript'>
		//window.onload = function(){document.getElementById('open_link').click();}
	</script>
	<a href='payment/?ORDER_ID=<?=(int)$_REQUEST['ORDER_ID']?>' target="_blank" id='open_link'></a>
	<?
}?>
