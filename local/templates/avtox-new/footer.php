<?//?if ($curPage !== SITE_DIR."index.php"):?> 				
</div>
</div>
</div>
</div>
<?//endif?> 	 	 
<!-- Footer -->

<div class="row-fluid footer"> 		 
    <div class="container"> 			 
        <div class="row menu_bottom"> 				 
            <div class="span2"> 					 
                <h5>О магазине</h5>
                <?$APPLICATION->IncludeComponent(
                    "bitrix:menu",
                    "bottom_lm",
                    Array(
                        "ROOT_MENU_TYPE" => "top",
                        "MENU_CACHE_TYPE" => "N",
                        "MENU_CACHE_TIME" => "3600",
                        "MENU_CACHE_USE_GROUPS" => "Y",
                        "MENU_CACHE_GET_VARS" => "",
                        "MAX_LEVEL" => "1",
                        "CHILD_MENU_TYPE" => "left",
                        "USE_EXT" => "N",
                        "DELAY" => "N",
                        "ALLOW_MULTI_SELECT" => "N"
                    )
                );?> 				</div>

            <div class="span3"> 					 
                <h5>Каталог товаров</h5>
                <?$APPLICATION->IncludeComponent(
                    "bitrix:menu",
                    "bottom_lm",
                    Array(
                        "ROOT_MENU_TYPE" => "left",
                        "MENU_CACHE_TYPE" => "N",
                        "MENU_CACHE_TIME" => "3600",
                        "MENU_CACHE_USE_GROUPS" => "Y",
                        "MENU_CACHE_GET_VARS" => array(),
                        "MAX_LEVEL" => "1",
                        "CHILD_MENU_TYPE" => "left",
                        "USE_EXT" => "N",
                        "DELAY" => "N",
                        "ALLOW_MULTI_SELECT" => "N"
                    )
                );?> 				</div>

            <div class="span4 offset3 contacts_bottom"> 					 
                <div class="row"> 						 
                    <div class="span12"> 							 
                        <h4><?$APPLICATION->IncludeComponent(
                                "bitrix:main.include",
                                "",
                                Array(
                                    "AREA_FILE_SHOW" => "file",
                                    "PATH" => SITE_TEMPLATE_PATH."/include/telephone.php"
                                )
                            );?></h4>
                        <div class="mobile-items-phone2">
                            <a href="tel:+79264150010"><img class="" width="50" alt="phone" src="/bitrix/templates/fast-start_blue_copy/images/watsapp.png" height="32" title="i.jpg"></a>
                            <a href="tel:+79264150010"><img class="" width="50" alt="phone" src="/bitrix/templates/fast-start_blue_copy/images/sms.png" height="32" title="i.jpg"></a>
                            <a href="tel:+79264150010"><img class="" width="50" alt="phone" src="/bitrix/templates/fast-start_blue_copy/images/viber.png" height="32" title="i.jpg"></a>
                            <a href="skype:avtox24.ru?chat"><img class="" width="50" alt="phone" src="/bitrix/templates/fast-start_blue_copy/images/skype.png" height="32" title="i.jpg"></a>
                            <a href="mailto:info@avtox24.ru"><img class="" width="50" alt="phone" src="/bitrix/templates/fast-start_blue_copy/images/email.png" height="32" title="i.jpg"></a>


                        </div>           				


                    </div>
                </div>

                <div class="row"> 						 
                    <div class="span12"> 							<?$APPLICATION->IncludeComponent(
                            "bitrix:main.include",
                            "",
                            Array(
                                "AREA_FILE_SHOW" => "file",
                                "PATH" => SITE_TEMPLATE_PATH."/include/address.php"
                            )
                        );?> 						</div>
                </div>

                <br />

                <br />

                <div class="row"> 						 
                    <div class="span12 social_bottom"> 							<span>Оставайтесь на связи:</span> 							 
                        <p><?$APPLICATION->IncludeComponent(
                                "bitrix:main.include",
                                "",
                                Array(
                                    "AREA_FILE_SHOW" => "file",
                                    "PATH" => SITE_TEMPLATE_PATH."/include/social.php"
                                )
                            );?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="row sign"> 				 
            <div class="span12"> 					 
                <div class="row"> 						 
                    <div class="span8"> 							<span>Лучшие цены на запчасти для вашего авто в Интернете. <b>Подпишитесь и будьте в курсе!</b></span> 						</div>

                    <div class="span4"> 							<?$APPLICATION->IncludeComponent(
                            "bitrix:subscribe.form",
                            "lm_bottom",
                            Array(
                                "USE_PERSONALIZATION" => "Y",
                                "SHOW_HIDDEN" => "N",
                                "PAGE" => "/personal/subscribe/",
                                "CACHE_TYPE" => "A",
                                "CACHE_TIME" => "3600"
                            )
                        );?> 						</div>
                </div>
            </div>
        </div>

        <div class="row"> 				 
            <div class="span6 сopyright"> 					<span><?$APPLICATION->IncludeComponent(
                    "bitrix:main.include",
                    "",
                    Array(
                        "AREA_FILE_SHOW" => "file",
                        "PATH" => SITE_TEMPLATE_PATH."/include/сopyright.php"
                    )
                );?></span> 				</div>

            <div class="span6 development"> 					<span><?$APPLICATION->IncludeComponent(
                    "bitrix:main.include",
                    "",
                    Array(
                        "AREA_FILE_SHOW" => "file",
                        "PATH" => SITE_TEMPLATE_PATH."/include/development.php"
                    )
                );?></span> 				</div>
        </div>
    </div>
</div>
</div>

<!-- Yandex.Metrika counter -->

<script type="text/javascript">
    var yaParams = {/*Здесь параметры визита*/};
</script>

<script type="text/javascript">
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter22412722 = new Ya.Metrika({id:22412722,
                    webvisor:true,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true,
                    trackHash:true,params:window.yaParams||{ }});
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
        s = d.createElement("script"),
        f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<noscript> 
    <div><img src="//mc.yandex.ru/watch/22412722" style="position:absolute; left:-9999px;"  /></div>
</noscript> 
<!-- /Yandex.Metrika counter -->

<!-- Yandex.Metrika counter linemedia -->
<script type="text/javascript">
    (function (d, w, c) {
        (w[c] = w[c] || []).push(function() {
            try {
                w.yaCounter26006397 = new Ya.Metrika({id:26006397,
                    clickmap:true,
                    trackLinks:true,
                    accurateTrackBounce:true});
            } catch(e) { }
        });

        var n = d.getElementsByTagName("script")[0],
        s = d.createElement("script"),
        f = function () { n.parentNode.insertBefore(s, n); };
        s.type = "text/javascript";
        s.async = true;
        s.src = (d.location.protocol == "https:" ? "https:" : "http:") + "//mc.yandex.ru/metrika/watch.js";

        if (w.opera == "[object Opera]") {
            d.addEventListener("DOMContentLoaded", f, false);
        } else { f(); }
    })(document, window, "yandex_metrika_callbacks");
</script>
<noscript><div><img src="//mc.yandex.ru/watch/26006397" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
<!-- /Yandex.Metrika counter linemedia -->
<!-- Yandex.Metrika counter --><script type="text/javascript"> (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter33431328 = new Ya.Metrika({ id:33431328, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true, trackHash:true, ecommerce:"dataLayer" }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks");</script><!-- /Yandex.Metrika counter -->

<? $APPLICATION->IncludeComponent("beono:flashmessage", ".default", array(), "", array("HIDE_ICONS"=>"Y"));?> 
<!-- Traffic tracking code -->
<script type="text/javascript">
    (function(w, p) {
        var a, s;
        (w[p] = w[p] || []).push({
            counter_id: 432153140
        });
        a = document.createElement('script'); a.type = 'text/javascript'; a.async = true;
        a.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'autocontext.begun.ru/analytics.js';
        s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(a, s);
    })(window, 'begun_analytics_params');
</script>
<?if (!substr_count($APPLICATION->GetCurDir(), "acat.autodealer")) {?>
    <div class="mango-callback" data-settings='{"type":"", "id": "MTAwMDMwODg=","autoDial" : "0", "lang" : "ru-ru", "host":"lk.mango-office.ru/", "errorMessage": "В данный момент наблюдаются технические проблемы и совершение звонка невозможно"}'>
    </div>
    <script>!function(t){function e(){i=document.querySelectorAll(".button-widget-open");for(var e=0;e<i.length;e++)"true"!=i[e].getAttribute("init")&&(options=JSON.parse(i[e].closest('.'+t).getAttribute("data-settings")),i[e].setAttribute("onclick","alert('"+options.errorMessage+"(0000)'); return false;"))}function o(t,e,o,n,i,r){var s=document.createElement(t);for(var a in e)s.setAttribute(a,e[a]);s.readyState?s.onreadystatechange=o:(s.onload=n,s.onerror=i),r(s)}function n(){for(var t=0;t<i.length;t++){var e=i[t];if("true"!=e.getAttribute("init")){options=JSON.parse(e.getAttribute("data-settings"));var o=new MangoWidget({host:window.location.protocol+'//'+options.host,id:options.id,elem:e,message:options.errorMessage});o.initWidget(),e.setAttribute("init","true"),i[t].setAttribute("onclick","")}}}host=window.location.protocol+"//lk.mango-office.ru/";var i=document.getElementsByClassName(t);o("link",{rel:"stylesheet",type:"text/css",href:host+"widget/widget-button.css"},function(){},function(){},e,function(t){document.documentElement.insertBefore(t,document.documentElement.firstChild)}),o("script",{type:"text/javascript",src:host+"js/widget/m.c.w-min.js"},function(){("complete"==this.readyState||"loaded"==this.readyState)&&n()},n,e,function(t){document.documentElement.appendChild(t)})}("mango-callback");</script>
    <?}?>
</body>
</html>