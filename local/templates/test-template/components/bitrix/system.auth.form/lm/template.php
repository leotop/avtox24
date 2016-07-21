<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arResult["FORM_TYPE"] == "login"):
?>


<?/*
	if($arResult["NEW_USER_REGISTRATION"] == "Y")
	{
?>
	<a href="<?=$arResult["AUTH_REGISTER_URL"]?>" class="signup signin btn btn-small btn-warning"><?=GetMessage("AUTH_REGISTER")?></a>
<?
	}
    */
?>
	<!--a href="<?=$arResult["AUTH_URL"]?>" class="signin btn btn-warning" onclick='var modalH = $("#login").height(); $("#login").css({"display":"block","margin-top":"-"+(parseInt(modalH)/2)+"px" }); return false;'><?=GetMessage("AUTH_LOGIN")?></a-->
	
	<a href="<?=$arResult["AUTH_URL"]?>" class="signup signin btn btn-block btn-success"><strong><?=GetMessage("AUTH_LOGIN")?></strong></a>
<?
else:
?>
<div class="row-fluid">
	<div class="span9">
		<?
		$name = trim($USER->GetFullName());
		if (strlen($name) <= 0)
			$name = $USER->GetLogin();
	
		echo htmlspecialcharsEx($name);
		?>
	</div>
	<div class="span3">
		<a href="<?=$APPLICATION->GetCurPageParam("logout=yes", Array("logout"))?>" class="logout pull-right btn btn-mini"><?=GetMessage("AUTH_LOGOUT")?></a>
	</div>
</div>
<hr />
<div class="row-fluid">
	<div class="span12">
	    <span class="go_to_profile">
	    <a href="<?=$arResult['PROFILE_URL']?>" class="btn btn-info btn-mini">
	    <i class="icon icon-user icon-white"></i> 
	    <?=GetMessage("AUTH_MY_PROFILE")?></a></span>
	    <span class="pull-right">
	    <?$APPLICATION->IncludeComponent(
			"bitrix:sale.personal.account",
			"lm_invoice",
			Array(
				"SET_TITLE" => "N"
			)
		);?>
	    </span>
	</div>
</div>
<?
endif;
?>
