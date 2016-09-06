jQuery(document).ready(function() {
    jQuery('.mob-nav').click(function() {
	jQuery('.mob-menu').addClass('active2')
	if ( jQuery('.mob-nav').hasClass('active') ) {
		jQuery('.mob-menu').css('display','none');
		jQuery('.side_bar').css('display','none');
		}else {
			jQuery('.mob-nav').addClass('active')
			jQuery('.mob-menu').css('display','block');
			jQuery('.side_bar').css('display','block');
		}
    });
	});