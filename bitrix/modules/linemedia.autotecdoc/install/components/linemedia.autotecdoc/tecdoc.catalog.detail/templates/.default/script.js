$(document).ready(function() {
    $('#lmTemplatePage .applicability-firm').click(function() {
        
        var manuId 			= $(this).data('manuid');
        var article_id 		= $('#article_id').val();
        var article_link_id = $('#article_link_id').val();
        var sessid          = $('#sessid').val();
        var id              = $(this).attr('rel');
        
        $('#lmTemplatePage #lm-auto-applicability').html('<img class="lm-auto-appl-loader" src="/bitrix/components/linemedia.autotecdoc/tecdoc.catalog.detail/images/ajax.gif" alt="">');

        $('#lmTemplatePage .applicability-firm').removeClass('selected');
        $(this).addClass('selected');
        
        $.ajax({
		  url: "/bitrix/components/linemedia.autotecdoc/tecdoc.catalog.detail/ajax.php?applicability=Y",
		  data: {'article_id':article_id, 'article_link_id':article_link_id, 'manuId':manuId, 'sessid':sessid},
		  type:'post'
		}).done(function(html) {
		  $('#lmTemplatePage #lm-auto-applicability').html(html);
		});

        
        $('#lmTemplatePage .applicability-models').hide();
        $('#lmTemplatePage .applicability-modifications').hide();
        $('#lmTemplatePage #applicability-model-' + id).show();
    });
    
    
    $("#lmTemplatePage .applicability-model").on('click', function(event) {
    	$('#lmTemplatePage .applicability-model').removeClass('selected');
        $(this).addClass('selected');
    	
	  	var id = $(this).attr('rel');
        $('#lmTemplatePage .applicability-modifications').hide();
        $('#lmTemplatePage #applicability-modification-' + id).show();
	});
    
});
