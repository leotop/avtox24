<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if(strlen($arResult["ERROR_MESSAGE"])<=0):?>
<table class="equipment ordersmore" style="width:726px">
	<thead>
		<tr>
			<td colspan="2"><?=GetMessage("SPOD_ORDER_NO")?>&nbsp;<?=$arResult["ID"]?>&nbsp;<?=GetMessage("SPOD_FROM")?> <?=$arResult["DATE_INSERT"] ?></td>
		</tr>
	</thread>

	<tbody>
		<tr>
			<td><strong><?echo GetMessage("SPOD_ORDER_STATUS")?></strong></td>
			<td><?=$arResult["STATUS"]["NAME"]?><?=GetMessage("SPOD_ORDER_FROM")?><?=$arResult["DATE_STATUS"]?>)</td>
		</tr>
		<tr>
			<td><strong><?=GetMessage("P_ORDER_PRICE")?>:</strong></td>
			<td><?
				echo "<b>".$arResult["PRICE_FORMATED"]."</b>";
				if (DoubleVal($arResult["SUM_PAID"]) > 0)
					echo "(".GetMessage("SPOD_ALREADY_PAID")."&nbsp;<b>".$arResult["SUM_PAID_FORMATED"]."</b>)";
				?></td>
		</tr>
		<tr>
			<td><strong><?= GetMessage("P_ORDER_CANCELED") ?>:</strong></td>
			<td><?echo (($arResult["CANCELED"] == "Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO"));
				if ($arResult["CANCELED"] == "Y")
				{
					echo GetMessage("SPOD_ORDER_FROM").$arResult["DATE_CANCELED"].")";
					if (strlen($arResult["REASON_CANCELED"]) > 0)
						echo "<br />".$arResult["REASON_CANCELED"];
				}
				elseif ($arResult["CAN_CANCEL"]=="Y")
				{
					?>&nbsp;<a href="<?=$arResult["URL_TO_CANCEL"]?>"><?=GetMessage("SALE_CANCEL_ORDER")?></a><?
				}?>
			</td>
		</tr>


	<?if (IntVal($arResult["USER_ID"])>0):?>
		<tr>
			<td colspan="2"><h4><?=GetMessage("SPOD_ACCOUNT_DATA")?></h4></td>
		</tr>
		<?if(strlen($arResult["USER_NAME"]) > 0):?>
			<tr>
				<td><strong><?=GetMessage("SPOD_ACCOUNT") ?>:</strong></td>
				<td><?=$arResult["USER_NAME"]?></td>
			</tr>
		<?endif;?>
		<tr>
			<td><strong><?= GetMessage("SPOD_LOGIN") ?></strong></td>
			<td><?=$arResult["USER"]["LOGIN"]?></td>
		</tr>
		<tr>
			<td><strong><?echo GetMessage("SPOD_EMAIL")?></strong></td>
			<td><a href="mailto:<?=$arResult["USER"]["EMAIL"]?>"><?=$arResult["USER"]["EMAIL"]?></a></td>
		</tr>
	<?endif;?>

		<tr>
			<td colspan="2"><h4><?=GetMessage("P_ORDER_USER")?></h4></td>
		</tr>
		<?if(!empty($arResult["ORDER_PROPS"]))
		{
			foreach($arResult["ORDER_PROPS"] as $val)
			{
				if ($val["SHOW_GROUP_NAME"] == "Y")
				{
					?>
					<tr>
						<td colspan="2"><strong><?=$val["GROUP_NAME"];?></strong></td>
					</tr>
					<?
				}
				?>
				<tr>
					<td><strong><?echo $val["NAME"] ?>:</strong></td>
					<td><?
						if ($val["TYPE"] == "CHECKBOX")
						{
							if ($val["VALUE"] == "Y")
								echo GetMessage("SALE_YES");
							else
								echo GetMessage("SALE_NO");
						}
						else
							echo $val["VALUE"];
						?></td>
				</tr>
				<?
			}
		}
		if (strlen($arResult["USER_DESCRIPTION"])>0)
		{
			?>
			<tr>
				<td><strong><?=GetMessage("P_ORDER_USER_COMMENT")?>:</strong></td>
				<td><?=$arResult["USER_DESCRIPTION"]?></td>
			</tr>
			<?
		}?>

		<tr>
			<td colspan="2"><h4><?=GetMessage("P_ORDER_PAYMENT")?></h4></td>
		</tr>
 		<tr>
			<td><strong><?=GetMessage("P_ORDER_PAY_SYSTEM")?>:</strong></td>
			<td><?
				if (IntVal($arResult["PAY_SYSTEM_ID"]) > 0)
					echo $arResult["PAY_SYSTEM"]["NAME"];
				else
					echo GetMessage("SPOD_NONE");
				?>
			</td>
		</tr>
		<tr>
			<td><strong><?echo GetMessage("P_ORDER_PAYED") ?>:</strong></td>
			<td><?
				echo (($arResult["PAYED"] == "Y") ? GetMessage("SALE_YES") : GetMessage("SALE_NO"));
				if ($arResult["PAYED"] == "Y")
					echo GetMessage("SPOD_ORDER_FROM").$arResult["DATE_PAYED"].")";
				if ($arResult["CAN_REPAY"]=="Y")
				{
					if ($arResult["PAY_SYSTEM"]["PSA_NEW_WINDOW"] == "Y")
					{
						?>
						<a href="<?=$arResult["PAY_SYSTEM"]["PSA_ACTION_FILE"]?>" target="_blank"><?=GetMessage("SALE_REPEAT_PAY")?></a>
						<?
					}
					else
					{
						$ORDER_ID = $ID;
						include($arResult["PAY_SYSTEM"]["PSA_ACTION_FILE"]);
					}
				}?>
				</td>
		</tr>
		<tr>
			<td><strong><?=GetMessage("P_ORDER_DELIVERY")?>:</strong></td>
			<td><?
				if (strpos($arResult["DELIVERY_ID"], ":") !== false || IntVal($arResult["DELIVERY_ID"]) > 0)
				{
					echo $arResult["DELIVERY"]["NAME"];
				}
				else
				{
					echo GetMessage("SPOD_NONE");
				}
				?>
			</td>
		</tr>
	</tbody>
</table>

<h2><?=GetMessage("P_ORDER_BASKET")?></h2>
<table class="equipment" rules="rows" style="width:726px">
	
</table>
<table class="myorders_itog">
	<tbody>
		
		<tr>
			<td><?=GetMessage("SPOD_CLEAR_PRICE").":";?></td>
			<td><?=SaleFormatCurrency($arResult["PRICE"] - $arResult["TAX_VALUE"]-$arResult["PRICE_DELIVERY"], $arResult["CURRENCY"])?></td>
		</tr>
		<?if(DoubleVal($arOrder["DISCOUNT_VALUE"]) > 0):?>
			<tr>
				 <td><?=GetMessage("SPOD_DISCOUNT").":";?></td>
				<td><?=$arResult["DISCOUNT_VALUE_FORMATED"];?></td>
			</tr>
		<?endif?>
		<tr>
			<td><?=GetMessage("SPOD_ITOG")?>:</td>
			<td><?=$arResult["PRICE_FORMATED"]?></td>
		</tr>
	</tbody>
</table>

<?else:?>
	<?=ShowError($arResult["ERROR_MESSAGE"]);?>
<?endif;?>
