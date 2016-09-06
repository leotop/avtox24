/**
 * Created with JetBrains PhpStorm.
 * User: altunint
 * Date: 7/23/13
 * Time: 11:23 AM
 * To change this template use File | Settings | File Templates.
 */


$(document).ready (function() {
    $(".guayaquil_table tr.g_vehicle").tooltip({
        track: true,
        delay: 0,
        showURL: false,
        fade: 250,
        bodyHandler: function() {
            var items = $ (this).find ('td.ttp .item');
            var tooltip = '';
            $.each (items, function () {
                tooltip+=$ (this).html()+'<br/>';
            });
            return tooltip;
        }
    });
    $(".guayaquil_table tr.g_unit").tooltip({
        track: true,
        delay: 0,
        showURL: false,
        fade: 250,
        bodyHandler: function() {
            var items = $ (this).find ('.g_hint');
            var tooltip = '';
            $.each (items, function () {

                if (tooltip != "")
                    tooltip += "<br>";

                tooltip+=$ (this).html();
            });
            if (tooltip == "")
                return null;

            return tooltip;
        }
    });
});