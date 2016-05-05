$(document).ready(function() {
    $('#lmTemplatePopup .applicability-firm').on('click', function(event) {
        var manuId          = $(this).data('manuid');
        var manufacturer    = $(this).text();
        var template        = $('#template').val();
        var article_id      = $('#article_id').val();
        var article_link_id = $('#article_link_id').val();
        var sessid          = $('#sessid').val();
        var id              = $(this).attr('rel');
        
        $('#lmTemplatePopup #lm-auto-applicability').html('<img class="lm-auto-appl-loader" src="/bitrix/components/linemedia.auto/search.detail.info/images/ajax.gif" alt="">');
        
        $('#lmTemplatePopup .applicability-firm').removeClass('selected');
        $(this).addClass('selected');
        
		
		brand_title = $(this).closest('#lmTemplatePopup').data('brand');
		article_id = $(this).closest('#lmTemplatePopup').data('article');
		
		
        $.ajax({
            url: "/bitrix/components/linemedia.auto/search.detail.info/ajax.php?applicability=Y",
            data: {'template': template, 'article_id': article_id, 'article_link_id': article_link_id,brand_title: brand_title, 'manuId': manuId, 'manufacturer' : manufacturer, 'sessid': sessid},
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
