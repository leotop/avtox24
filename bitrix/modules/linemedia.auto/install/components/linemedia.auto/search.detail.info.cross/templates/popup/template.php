<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
global $APPLICATION;
?>

<? $title = $arResult['DATA']['BRAND'] . ' ' . $arResult['DATA']['ARTICLE'] ?>

<h2><?= GetMessage('TITLE_ARTICLE_NUMBER') ?>: <?= $title ?></h2>

<?//_d($arResult);?>

<? if (strlen($arResult['IMAGE']) > 0) { ?>
    <div class="lm_popup_img"><img src="<?= $arResult['IMAGE'] ?>" alt="<?= $title ?>" title="<?= $title ?>" /></div>
<? } ?>

<? if (is_array($arResult['DATA']['info']['properties'])) { ?>
    <h3><?= GetMessage('TITLE_ADDITIONAL_FEATURES') ?></h3>
    <div class="standartTable">
        <table class="tecdoc_details_info" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th><?= GetMessage('HEAD_PROPERTY') ?></th>
                    <th><?= GetMessage('HEAD_VALUE') ?></th>
                </tr>
            </thead>
            <tbody>
                <? foreach ($arResult['DATA']['info']['properties'] as $prop) { ?>
                    <tr>
                        <td><?= $prop['CRI_DES'] ?>:</td>
                        <td><?= $prop['STR_VALUE'] ?></td>
                    </tr>
                <? } ?>
            </tbody>
        </table>
    </div>
    <br/>
<? } ?>

<? if ($arParams['SHOW_ORIGINAL_ITEMS'] == 'Y') { ?>
    <h3><?= GetMessage('TITLE_CONFORMITY_ORIGINAL_NUMBERS') ?></h3>
    <? if (is_array($arResult['DATA']['info']['oem'])) { ?>
        <div class="standartTable">
            <table class="tecdoc_details_info" cellpadding="0" cellspacing="0">
                <thead>
                    <tr>
                        <th><?= GetMessage('HEAD_MARK') ?></th>
                        <th><?= GetMessage('HEAD_NUMBER') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <? foreach ($arResult['DATA']['info']['oem'] as $oem) { ?>
                        <tr>
                            <td><?= $oem['BRAND'] ?></td>
                            <td><?= $oem['ARTICLE'] ?></td>
                        </tr>
                    <? } ?>
                </tbody>
            </table>
        </div>
         <br/>
    <? } ?>
<? } ?>
<? if ($arParams['SHOW_APPLICABILITY'] == 'Y') { ?>
    <br/>
    <h3><?= GetMessage('TITLE_CONFORMITY_SHOW_APPLICABILITY') ?></h3>
    <? if (is_array($arResult['DATA']['info']['appliance_brands'])) { ?>
        <div class="applicability" id="lmTemplatePopup" data-artid="<?=$arResult['DATA']['ID']?>" data-article="<?=$arResult['DATA']['ARTICLE']?>" data-brand="<?=$arResult['DATA']['BRAND']?>">
            <div class="applicability-firms">
                <? foreach ($arResult['DATA']['info']['appliance_brands'] as $appl_brand) { ?>
                    <a href="javascript:void(0)" class="applicability-firm" data-mfaid="<?=$appl_brand['id']?>"><?=$appl_brand['brand']?></a>
                <? } ?>
            </div>
            
            <input type="hidden" id="template" value="<?= $this->getName() ?>" />
            <input type="hidden" id="article_id" value="<?=$arResult['DATA']['ID']?>" />
            <input type="hidden" id="sessid" value="<?= bitrix_sessid() ?>" />
            
            <div class="clear"></div>
            
            <div id="lm-auto-applicability"></div>
        </div>
    <? } ?>
    <script>
        $(document).ready(function() {
            $('#lmTemplatePopup .applicability-firm').on('click', function(event) {
                var mfaId          = $(this).data('mfaid');
                var manufacturer    = $(this).text();
                var template        = $('#template').val();
                var article_id      = $('#article_id').val();
                //var article_link_id = $('#article_link_id').val();
                var sessid          = $('#sessid').val();
                //var id              = $(this).attr('rel');

                $('#lmTemplatePopup #lm-auto-applicability').html('<img class="lm-auto-appl-loader" src="/bitrix/components/linemedia.auto/search.detail.info.cross/images/ajax.gif" alt="">');

                $('#lmTemplatePopup .applicability-firm').removeClass('selected');
                $(this).addClass('selected');


                //brand_title = $(this).closest('#lmTemplatePopup').data('brand');
                //article_id = $(this).closest('#lmTemplatePopup').data('article');


                $.ajax({
                    url: "/bitrix/components/linemedia.auto/search.detail.info.cross/ajax.php?applicability=Y",
                    data: {
                        'template': template,
                        'article_id': article_id,
                        'mfa_id': mfaId,
                        'manufacturer' : manufacturer,
                        'sessid': sessid
                    },
                    type: 'post'
                }).done(function(html) {
                    $('#lmTemplatePopup #lm-auto-applicability').html(html);

                    $("#lmTemplatePopup .applicability-model").on('click', function(event) {
                        $('#lmTemplatePopup .applicability-model').removeClass('selected');
                        $(this).addClass('selected');

                        var id = $(this).attr('rel');
                        $('#lmTemplatePopup .applicability-modifications').hide();
                        $('#lmTemplatePopup #applicability-modification-' + id).show();
                    });
                });

                $('#lmTemplatePopup .applicability-models').hide();
                $('#lmTemplatePopup .applicability-modifications').hide();
                $('#lmTemplatePopup #applicability-model-' + id).show();
            });
        });
    </script>
<? } ?>

