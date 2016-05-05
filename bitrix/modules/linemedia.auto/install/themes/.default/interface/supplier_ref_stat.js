  window.google_charts_en=false;
        google.load("visualization", "1", {packages:["corechart"]});
        google.setOnLoadCallback(function(){window.google_charts_en=true;});
        var lm_auto_supplier_reliability_stat_dlg = false;
        function showRSRD(link, id) {


            if (!window.google_charts_en) {
                return;
            }

            var divId = 'chart_' + id;

            if (lm_auto_supplier_reliability_stat_dlg) {
                lm_auto_supplier_reliability_stat_dlg.close();
                lm_auto_supplier_reliability_stat_dlg.destroy();
            }
            BX.showWait();
            lm_auto_supplier_reliability_stat_dlg = new BX.PopupWindow("rs_reliability", null, {
                titleBar: {content: BX.create("span", {html: '<b>Summary</b>'})},
                buttons: [
                new BX.PopupWindowButton({
                    text: "Close",
                    className: "webform-button-link-cancel",
                    events: {click: function(){
                            $(divId + "_pie").remove();
                            $(divId + "_rely").remove();
                            this.popupWindow.close();
                            lm_auto_supplier_reliability_stat_dlg.destroy();
                            lm_auto_supplier_reliability_stat_dlg = false;
                        }}
                    })
                ]
            });
                var html = '<div style="min-width:500px;width:200px;">'+
                '<div id="' + divId + '_pie" style="display:block;width:500px; height:200px;"></div>'+
                '<div id="' + divId + '_rely" style="display:block;width:500px; height:200px;"></div></div>';
                lm_auto_supplier_reliability_stat_dlg.setContent(html);
                BX.ajax.loadJSON($(link).attr('data-url'),
                                    null,
                                    function(reply) {
                                        var data = google.visualization.arrayToDataTable(reply.pie);
                                        var chart = new google.visualization.PieChart(document.getElementById(divId + '_pie'));
                                        chart.draw(data, reply.pie_opts);
                                        if (reply.bars_exists) {
                                            data = google.visualization.arrayToDataTable(reply.bars);
                                            var chart2 = new google.visualization.ColumnChart(document.getElementById(divId + '_rely'));
                                            if(!reply.bars_opts) reply.bars_opts = {};
                                            reply.bars_opts.bar = {groupWidth:'10px'};
                                            reply.bars_opts.hAxis.gridlines={count:2};
                                            reply.bars_opts.hAxis.direction = 1;
                                            reply.bars_opts.hAxis.minValue = 0;
                                            reply.bars_opts.hAxis.viewWindow ={min:0};
                                            reply.bars_opts.hAxis.format = '#';
                                            reply.bars_opts.vAxis.minValue = 0;
                                            reply.bars_opts.vAxis.maxValue = 100;
                                            reply.bars_opts.legend = {position:'none'};
                                            chart2.draw(data, reply.bars_opts);
                                        } else {
                                            $(divId + '_rely').html('Data no found');
                                        }
                                        BX.closeWait();
                                        lm_auto_supplier_reliability_stat_dlg.show();
                                        });
                    BX.closeWait();
        }