/**
 * Created with JetBrains PhpStorm.
 * User: altunint
 * Date: 7/23/13
 * Time: 11:23 AM
 * To change this template use File | Settings | File Templates.
 */


$(document).ready (function() {
$(".guayaquil_table tr").tooltip({
    track: true,
    delay: 0,
    showURL: false,
    fade: 250,
    bodyHandler: function() {
        var items = $(this).find ('td.ttp .item');
        var tooltip = '';
        $.each (items, function () {
            tooltip+= $(this).html()+'<br/>';
        });
        return tooltip;
    }
});
});