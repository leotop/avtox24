<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<div class="section">
<?
function PrintPropsForm($arSource=Array(), $locationTemplate = ".default")
{
	if (!empty($arSource))
	{
		foreach($arSource as $arProperties)
		{
			?>
			<tr>
				<td class="name">
					<?= $arProperties["NAME"] ?>:<?
					if($arProperties["REQUIED_FORMATED"]=="Y")
					{
						///<span class="sof-req">*</span>/
					}
					?>
				</td>
				<td>
					<?
					if($arProperties["TYPE"] == "CHECKBOX")
					{
						?>

						<input type="hidden" name="<?=$arProperties["FIELD_NAME"]?>" value="">
						<input type="checkbox" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" value="Y"<?if ($arProperties["CHECKED"]=="Y") echo " checked";?>>
						<?
					}
					elseif($arProperties["TYPE"] == "TEXT")
					{
						?>
						<input type="text" maxlength="250" size="<?=$arProperties["SIZE1"]?>" value="<?=$arProperties["VALUE"]?:'---'?>" name="<?=$arProperties["FIELD_NAME"]?>"
						       id="<?=$arProperties["FIELD_NAME"]?>">
						<?
					}
					elseif($arProperties["TYPE"] == "SELECT")
					{
						?>
						<select name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" size="<?=$arProperties["SIZE1"]?>">
						<?
						foreach($arProperties["VARIANTS"] as $arVariants)
						{
							?>
							<option value="<?=$arVariants["VALUE"]?>"<?if ($arVariants["SELECTED"] == "Y") echo " selected";?>><?=$arVariants["NAME"]?></option>
							<?
						}
						?>
						</select>
						<?
					}
					elseif ($arProperties["TYPE"] == "MULTISELECT")
					{
						?>
						<select multiple name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>" size="<?=$arProperties["SIZE1"]?>">
						<?
						foreach($arProperties["VARIANTS"] as $arVariants)
						{
							?>
							<option value="<?=$arVariants["VALUE"]?>"<?if ($arVariants["SELECTED"] == "Y") echo " selected";?>><?=$arVariants["NAME"]?></option>
							<?
						}
						?>
						</select>
						<?
					}
					elseif ($arProperties["TYPE"] == "TEXTAREA")
					{
						?>
						<textarea rows="<?=$arProperties["SIZE2"]?>" cols="<?=$arProperties["SIZE1"]?>" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>"><?=$arProperties["VALUE"]?></textarea>
						<?
					}
					elseif ($arProperties["TYPE"] == "LOCATION")
					{
						$value = 0;
						if (is_array($arProperties["VARIANTS"]) && count($arProperties["VARIANTS"]) > 0)
						{
							foreach ($arProperties["VARIANTS"] as $arVariant)
							{
								if ($arVariant["SELECTED"] == "Y")
								{
									$value = $arVariant["ID"];
									break;
								}
							}
						}

						$GLOBALS["APPLICATION"]->IncludeComponent(
							"bitrix:sale.ajax.locations",
							$locationTemplate,
							array(
								"AJAX_CALL" => "N",
								"COUNTRY_INPUT_NAME" => "COUNTRY",
								"REGION_INPUT_NAME" => "REGION",
								"CITY_INPUT_NAME" => $arProperties["FIELD_NAME"],
								"CITY_OUT_LOCATION" => "Y",
								"LOCATION_VALUE" => $value,
								"ORDER_PROPS_ID" => $arProperties["ID"],
								"ONCITYCHANGE" => ($arProperties["IS_LOCATION"] == "Y" || $arProperties["IS_LOCATION4TAX"] == "Y") ? "submitForm()" : "",
								"SIZE1" => $arProperties["SIZE1"],
							),
							null,
							array('HIDE_ICONS' => 'Y')
						);

					}
					elseif ($arProperties["TYPE"] == "RADIO")
					{
						foreach($arProperties["VARIANTS"] as $arVariants)
						{
							?>
							<input type="radio" name="<?=$arProperties["FIELD_NAME"]?>" id="<?=$arProperties["FIELD_NAME"]?>_<?=$arVariants["VALUE"]?>" value="<?=$arVariants["VALUE"]?>"<?if($arVariants["CHECKED"] == "Y") echo " checked";?>> <label for="<?=$arProperties["FIELD_NAME"]?>_<?=$arVariants["VALUE"]?>"><?=$arVariants["NAME"]?></label><br />
							<?
						}
					}

					if (strlen($arProperties["DESCRIPTION"]) > 0)
					{
						?><div class="desc"><?echo $arProperties["DESCRIPTION"] ?></div><?
					}
					?>
				</td>
			</tr>
			<?
		}
		?>
		<?
		return true;
	}
	return false;
}
/*?>

<div class="title"><?=GetMessage("SOA_TEMPL_PROP_INFO")?></div>

<?*/
$bHideProps = false;
?>
<?/* if(!empty($arResult["ORDER_PROP"]["USER_PROFILES"])):?>
<table class="sale_order_table">
<tr>
	<td class="name"><?=GetMessage("SOA_TEMPL_PROP_CHOOSE")?></td>
	<td>
	<select name="PROFILE_ID" id="ID_PROFILE_ID" onChange="SetContact(this.value)">
		<option value="0"><?=GetMessage("SOA_TEMPL_PROP_NEW_PROFILE")?></option>
		<?
		foreach($arResult["ORDER_PROP"]["USER_PROFILES"] as $arUserProfiles)
		{
			if ($arUserProfiles["CHECKED"]=="Y")
				$bHideProps = true;
			?>
			<option value="<?= $arUserProfiles["ID"] ?>"<?if ($arUserProfiles["CHECKED"]=="Y") echo " selected";?>><?=$arUserProfiles["NAME"]?></option>
			<?
		}
		?>
	</select>
	</td>
</tr>
</table>
<?endif;*/

?>

<br />
<? CJSCore::Init(array("jquery"));?>
<div class="title" id="create_new_user_text">
	<p onclick="toggleNewUserFields()" >
			<?=GetMessage("SOA_TEMPL_BUYER_INFO")?>
	</p>
		<?/*if ($bHideProps && $_POST["showProps"] != "Y"):?>
		<a href="#" onClick="fGetBuyerProps(this);return false;"><?=GetMessage('SOA_TEMPL_BUYER_SHOW');?></a>
	<?elseif ($bHideProps && $_POST["showProps"] == "Y"):?>
		<a href="#" onClick="fGetBuyerProps(this);return false;"><?=GetMessage('SOA_TEMPL_BUYER_HIDE');?></a>
	<?endif;*/?>
	<input type="hidden" name="showProps" id="showProps" value="N" />
</div>

<table class="sale_order_table_new_user props_new_user" id="sale_order_props_new_user" <?=empty($_REQUEST['PERSON_TYPE']) || !empty($_REQUEST['user'])? 'style="display: none"' :''?>
<?//=($bHideProps && $_POST["showProps"] != "Y")?"style='display:none;'":''?>>
<?
//PrintPropsForm($arResult["ORDER_PROP"]["USER_PROPS_N"], $arParams["TEMPLATE_LOCATION"]);
PrintPropsForm($arResult["ORDER_PROP"]["USER_PROPS_Y"], $arParams["TEMPLATE_LOCATION"]);
?>
</table>

<p class="title" id="choose_user_text" onclick="toggleChooseUser();">
		<?=GetMessage("LM_AUTO_CHOOSE_BUYER")?>
</p>

<div id="choose_user" <?=empty($_REQUEST['user'])? 'style="display: none"' :''?>>
	<?require_once($_SERVER["DOCUMENT_ROOT"] .$templateFolder.'/functions.php')?>

	<div class="user">
		<?=LMFindUserID(
			$tag_name = "user",
			$tag_value = (int) $_REQUEST['user']?:'',
			$user_name="",
			$form_name = "ORDER_FORM",
			$tag_size = "3",
			$tag_maxlength="",
			$button_value = "...",
			$tag_class="typeinput",
			$button_class="tablebodybutton",
			$search_page= $templateFolder."/user_search.php",
			$get_user_page = $templateFolder."/get_user.php",
			$templateFolder
		);
		?>
	</div>

	<?/*select profile for choose user*/?>
	<div <?=empty($_REQUEST['user'])? 'style="display: none"' :''?>>
		<?if(!empty($arResult["ORDER_PROP"]["USER_PROFILES"])) { ?>
            <?
            $profile_id = '';
            foreach($arResult["ORDER_PROP"]["USER_PROFILES"] as $arUserProfiles) {
                if ($arUserProfiles["CHECKED"]=="Y") {
                    $profile_id = $arUserProfiles["ID"];
                    break;
                }
            }
            ?>
            <?/* ѕри динамическом обновлении таблицы пропадает элемент формы, сделаем статичный input type="hidden" */?>
            <input type="hidden" id="PROFILE_ID" name="PROFILE_ID" value="<?=$profile_id?>" />
			<table class="sale_order_table ajax_sale_table">
				<tr>
					<td class="name"><?=GetMessage("LM_AUTO_USER_PROFILE")?></td>
					<td>
						<select name="ID_PROFILE_ID" id="ID_PROFILE_ID" onChange="SetContact(this.value)">
							<option value="0"><?=GetMessage("SOA_TEMPL_PROP_NEW_PROFILE")?></option>
							<?
							foreach($arResult["ORDER_PROP"]["USER_PROFILES"] as $arUserProfiles)
							{
								if ($arUserProfiles["CHECKED"]=="Y")
									$bHideProps = true;
								?>
								<option value="<?= $arUserProfiles["ID"] ?>"<?if ($arUserProfiles["CHECKED"]=="Y") echo " selected";?>><?=$arUserProfiles["NAME"]?></option>
							<?
							}
							?>
						</select>
					</td>
				</tr>
			</table>
		<? } else { ?>
            <input type="hidden" id="PROFILE_ID" name="PROFILE_ID" value="" />
        <? } ?>

		<br />

		<?if(!empty($_REQUEST['user'])) {?>
			<div class="title">
				<?=GetMessage("LM_AUTO_PROFILE_INFO")?>
				<? if(!empty($_REQUEST['user'])) {
					$bHideProps = false;
					$_POST["showProps"] = 'Y';
				}?>
				<?if ($bHideProps && $_POST["showProps"] != "Y"):?>
					<a href="#" onClick="fGetBuyerProps(this);return false;"><?=GetMessage('SOA_TEMPL_BUYER_SHOW');?></a>
				<?elseif ($bHideProps && $_POST["showProps"] == "Y"):?>
					<a href="#" onClick="fGetBuyerProps(this);return false;"><?=GetMessage('SOA_TEMPL_BUYER_HIDE');?></a>
				<?endif;?>
				<input type="hidden" name="showProps" id="showProps" value="N" />
			</div>

			<table class="sale_order_table props" id="sale_order_props" <?=($bHideProps && $_POST["showProps"] != "Y")?"style='display:none;'":''?>>
				<?
					PrintPropsForm($arResult["ORDER_PROP"]["USER_PROPS_Y"], $arParams["TEMPLATE_LOCATION"]);
				?>
			</table>
		<?}?>
	</div>
	<?/*!!!!!!!!!!!!!!!!!!!!!!!!!!!*/?>
</div>

<script type="text/javascript">


	function toggleChooseUser() {
		$( "#choose_user" ).toggle( "slow", function() {
			// Animation complete.
		});
	}
	function toggleNewUserFields() {
		$( "#sale_order_props_new_user" ).toggle( "slow", function() {
			// Animation complete.
		});
	}


	function fGetBuyerProps(el)
	{
		var show = '<?=GetMessageJS('SOA_TEMPL_BUYER_SHOW')?>';
		var hide = '<?=GetMessageJS('SOA_TEMPL_BUYER_HIDE')?>';
		var status = BX('sale_order_props').style.display;
		var startVal = 0;
		var startHeight = 0;
		var endVal = 0;
		var endHeight = 0;
		var pFormCont = BX('sale_order_props');
		pFormCont.style.display = "block";
		pFormCont.style.overflow = "hidden";
		pFormCont.style.height = 0;
		var display = "";

		if (status == 'none')
		{
			el.text = '<?=GetMessageJS('SOA_TEMPL_BUYER_HIDE');?>';

			startVal = 0;
			startHeight = 0;
			endVal = 100;
			endHeight = pFormCont.scrollHeight;
			display = 'block';
			BX('showProps').value = "Y";
			el.innerHTML = hide;
		}
		else
		{
			el.text = '<?=GetMessageJS('SOA_TEMPL_BUYER_SHOW');?>';

			startVal = 100;
			startHeight = pFormCont.scrollHeight;
			endVal = 0;
			endHeight = 0;
			display = 'none';
			BX('showProps').value = "N";
			pFormCont.style.height = startHeight+'px';
			el.innerHTML = show;
		}

		(new BX.easing({
			duration : 700,
			start : { opacity : startVal, height : startHeight},
			finish : { opacity: endVal, height : endHeight},
			transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),
			step : function(state){
				pFormCont.style.height = state.height + "px";
				pFormCont.style.opacity = state.opacity / 100;
			},
			complete : function(){
					BX('sale_order_props').style.display = display;
					BX('sale_order_props').style.height = '';
			}
		})).animate();
	}
</script>
</div>

<div style="display:none;">
<?
	$APPLICATION->IncludeComponent(
		"bitrix:sale.ajax.locations",
		$arParams["TEMPLATE_LOCATION"],
		array(
			"AJAX_CALL" => "N",
			"COUNTRY_INPUT_NAME" => "COUNTRY_tmp",
			"REGION_INPUT_NAME" => "REGION_tmp",
			"CITY_INPUT_NAME" => "tmp",
			"CITY_OUT_LOCATION" => "Y",
			"LOCATION_VALUE" => "",
			"ONCITYCHANGE" => "submitForm()",
		),
		null,
		array('HIDE_ICONS' => 'Y')
	);
?>
</div>