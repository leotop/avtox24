
$( document ).ready(function() {

    var tvuser='';

    /*
    Select user
     */
    function Chuser() {
        var DV_user;
        DV_user = BX("div_user");
        if (!!DV_user) {
            if (tvuser!=document.ORDER_FORM['user'].value) {
                tvuser=document.ORDER_FORM['user'].value;
                if (tvuser!='') {
                  DV_user.innerHTML = '<i>ждите...</i>';
                  BX("hiddenframeuser").src=templateFolder+'/get_user.php?ID=' + tvuser+'&strName=user&lang=s1';
                }
                else {
                    DV_user.innerHTML = '';
                }
            }
        }
        setTimeout(function(){Chuser()},1000);
    }

    Chuser();

    /*
    Update profile for sellected user
     */
    $('#user').on('keyup change', function(e) {
        var user = $('div.user #user').val();
        var person_type = $("input[type='radio'][name='PERSON_TYPE']:checked").val();

        if ( user > 0) {
            $.ajax({
                type: 'POST',
                url: templateFolder +'/ajax.php',
                data: { action: 'getProfiles', user_id: user, sessid: sessid, PERSON_TYPE: person_type}
            })
            .done(function( msg ) {
                    $('.ajax_sale_table').html(msg);
                    $(".u_inputs").hide();
                    var profile_id = $(msg).find('#ID_PROFILE_ID').val();
                    SetContact(profile_id);
            });
        }
    });
});