<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
global $APPLICATION, $USER;
$APPLICATION->AddHeadScript($this->GetFolder().'/js/jquery.cookie.js');
?>

<script type="text/javascript">
    var ajaxrecalc  = '<?= $arParams['AJAX_RECALC'] ?>';
    var ajaxurl     = '<?= $arResult['AJAX_URL'] ?>/ajax.php?AJAX=Y&<?= bitrix_sessid_get() ?>';
    var ajaxparams  = {
        'COUNT_DISCOUNT_4_ALL_QUANTITY': '<?= $arParams['COUNT_DISCOUNT_4_ALL_QUANTITY'] ?>',
        'HIDE_COUPON': '<?= $arParams['HIDE_COUPON'] ?>',
        'QUANTITY_FLOAT': '<?= $arParams['QUANTITY_FLOAT'] ?>',
    };
</script>

<?



if (StrLen($arResult["ERROR_MESSAGE"]) <= 0) {
	$arUrlTempl = Array(
		"delete" => $APPLICATION->GetCurPage()."?lm_action=delete&id=#ID#",
		"shelve" => $APPLICATION->GetCurPage()."?lm_action=shelve&id=#ID#",
		"add" => $APPLICATION->GetCurPage()."?lm_action=add&id=#ID#",
	);
	?>
	<script>
    	function ShowBasketItems(val)
    	{
    		if (val == 2) {
    			if (document.getElementById("id-cart-list"))
    				document.getElementById("id-cart-list").style.display = 'none';
    			if (document.getElementById("id-shelve-list"))
    				document.getElementById("id-shelve-list").style.display = 'block';
    		} else if (val == 3) {
    			if (document.getElementById("id-cart-list"))
    				document.getElementById("id-cart-list").style.display = 'none';
    			if (document.getElementById("id-shelve-list"))
    				document.getElementById("id-shelve-list").style.display = 'none';
    		} else {
    			if (document.getElementById("id-cart-list"))
    				document.getElementById("id-cart-list").style.display = 'block';
    			if (document.getElementById("id-shelve-list"))
    				document.getElementById("id-shelve-list").style.display = 'none';
    		}
    	}
	</script>
	<form method="post" action="<?=POST_FORM_ACTION_URI?>" name="basket_form">
		<? include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items.php"); ?>
        <? include($_SERVER["DOCUMENT_ROOT"].$templateFolder."/basket_items_delay.php"); ?>
	</form>
    <!------------------------------------------ ADDITIONAL VIN HTML --------------------------------------------->
    <div id="vin_modal" class="modal hide fade">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            <h3><?=GetMessage('SALE_NEED_VIN_TITLE')?></h3>
        </div>
        <div class="modal-body">
            <?$APPLICATION->IncludeComponent(
                'linemedia.autogarage:admin.garage.select',
                'basket',
                array(
                    'USER_ID' => $USER->GetID(),
                )
            );?>
        </div>
        <div class="modal-footer">
            <a id="basket_vin_submit" href="#" class="btn btn-primary"><?=GetMessage("SALE_ORDER")?></a>
        </div>
    </div>
    <script>
        $(document).ready(function() {

            if($("input[name=auto_garage_use_auto]").length > 0) {

                $("#basketOrderButton2").on('click', function(e) {
                    var car = $("input[name=auto_garage_use_auto]").val();
                    if(car != undefined && car != '') {
                        //alert(car);
                        return true;
                    } else {
                        e.preventDefault();
                        $("#vin_modal").modal('show');
                        $("#vin_modal").css('opacity', 1);
                        return false;
                    }
                });
            }
        });
    </script>
    <!------------------------------------------ /ADDITIONAL VIN HTML --------------------------------------------->
<?
}
else {
	ShowNote($arResult["ERROR_MESSAGE"]);
}
?>
