$(document).ready(function() {
	$('#lm-auto-vin-frm').submit(function(){
		var act = $(this).attr('action');
		document.location = act + $('#lm-auto-vin-inp').val() + '/';
		return false;
	})
});