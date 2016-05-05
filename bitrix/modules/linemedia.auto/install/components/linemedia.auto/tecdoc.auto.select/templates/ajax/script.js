var ajaxurl = '/bitrix/components/linemedia.auto/tecdoc.auto.select/templates/ajax/ajax.php';

$(document).ready(function() {
    $('#lm-auto-select-brands-id').on('change', function() {
        var BrandID         = $('#lm-auto-select-brands-id').val();
        var ModelID         = $('#lm-auto-select-models-id').val();
        var ModificationID  = $('#lm-auto-select-modifications-id').val();
        var action          = 'getModels';
        
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {'actions': action, 'BrandID': BrandID, 'ModelID': ModelID, 'ModificationID': ModificationID},
            beforeSend: function() {
                $('#lm-auto-select-models-wrap').html('<div class="lm-auto-auto-select-ajax"></div>');
                $('#lm-auto-select-modifications-wrap').html('');
            },
            success: function(data) {
                $('#lm-auto-select-models-wrap').html(data);
                $('#lm-auto-select-models-id').trigger('change');
            }
        });
    });
    
    $('#lm-auto-select-models-id').on('change', function() {
        var BrandID         = $('#lm-auto-select-brands-id').val();
        var ModelID         = $('#lm-auto-select-models-id').val();
        var ModificationID  = $('#lm-auto-select-modifications-id').val();
        var action          = 'getModifications';
        
        $.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {'actions': action, 'BrandID': BrandID, 'ModelID': ModelID, 'ModificationID': ModificationID},
            beforeSend: function() {
                $('#lm-auto-select-modifications-wrap').html('<div class="lm-auto-auto-select-ajax"></div>');
            },
            success: function(data) {
                $('#lm-auto-select-modifications-wrap').html(data);
                $('#lm-auto-select-modifications-id').trigger('change');
            }
        });
    });
});