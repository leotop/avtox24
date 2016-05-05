
jQuery(document).ready(function() { 
	jQuery("#form_1").submit(function(){ 
		var form = jQuery(this);
		var error = false; 
		form.find('textarea').each( function(){ 
			if (jQuery(this).val() == '') { 			
				error = true; 
		jQuery(this).css("border-color", "red");				
			}
			
		});
		form.find('input').each( function(){ 
			if (jQuery(this).val() == '') { 			
				/*error = true;*/
				/*jQuery(this).attr('placeholder','Заполните поле');*/
			/*jQuery(this).css("border-color", "red");			*/		
			}
			if (jQuery(this).val() !== '') {			
				/*error = false; */
				/*jQuery(this).val('');*/
				/*jQuery(this).css("border-color", "black");	*/
			}
		});
		
		if ($( "#email" ).val() == '') { 			
				/*error = true;*/
				/*jQuery(this).attr('placeholder','Заполните поле');*/
			/*jQuery(this).attr('placeholder','Заполните поле');*/				
			}
		
		
		
		if (!error) {
			var data = form.serialize(); 
			jQuery.ajax({ 
			   type: 'POST', 
			   url: '/js/send.php',
			   dataType: 'json', 
			   data: data, 
		       beforeSend: function(data) { 
		            form.find('input[type="submit"]').attr('disabled', 'disabled');
		          },
		       success: function(data){ 
		       		if (data['error']) { 
		       			alert(data['error']); 
		       		} else {					
					document.getElementById("ok-block").style.display = 'table';
					/*document.getElementById("button-1").style.visibility = 'hidden';	*/					
						jQuery('#form_1').trigger( 'reset' );
		       		}
		         },
		       error: function (xhr, ajaxOptions, thrownError) { 
		            alert(xhr.status); 
		            alert(thrownError);
		         },
		       complete: function(data) {
		            form.find('input[type="submit"]').prop('disabled', false); 
		         }
		                  
			     });
		}
		return false;
	});
});
