jQuery(document).ready(function() {
    jQuery('.mob-nav').click(function() {
	if ( jQuery('.mob-menu').hasClass('active') ) {
		jQuery('.mob-menu').css('display','none');
		jQuery('.side_bar').css('display','none');
		jQuery('.mob-menu').removeClass('active');
		jQuery('.mob-nav').removeClass('mob-nav-active');
		}else {
			jQuery('.mob-menu').addClass('active');
			jQuery('.mob-nav').addClass('mob-nav-active');
			jQuery('.mob-menu').css('display','block');
			jQuery('.side_bar').css('display','block');
		}
    });
	});