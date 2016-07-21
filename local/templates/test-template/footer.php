<?//?if ($curPage !== SITE_DIR."index.php"):?> 				</div>
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

<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-42834144-2', 'mosavtomag.ru');
    ga('send', 'pageview');

</script>

<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-42834144-3', 'xn--80aaff5bclcym.xn--p1ai');
    ga('send', 'pageview');

</script>

<!-- Yandex.Metrika counter --><script type="text/javascript"> (function (d, w, c) { (w[c] = w[c] || []).push(function() { try { w.yaCounter33431328 = new Ya.Metrika({ id:33431328, clickmap:true, trackLinks:true, accurateTrackBounce:true, webvisor:true, trackHash:true, ecommerce:"dataLayer" }); } catch(e) { } }); var n = d.getElementsByTagName("script")[0], s = d.createElement("script"), f = function () { n.parentNode.insertBefore(s, n); }; s.type = "text/javascript"; s.async = true; s.src = "https://mc.yandex.ru/metrika/watch.js"; if (w.opera == "[object Opera]") { d.addEventListener("DOMContentLoaded", f, false); } else { f(); } })(document, window, "yandex_metrika_callbacks");</script><!-- /Yandex.Metrika counter -->
<script>
    (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
        (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
        m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
    })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

    ga('create', 'UA-42834144-4', 'auto');
    ga('send', 'pageview');

</script>
<!-- START ME-TALK -->
<script type='text/javascript'>
    (function() {
        var s = document.createElement('script');
        s.type ='text/javascript';
        s.id = 'supportScript';
        s.charset = 'utf-8';
        s.async = true;
        s.src = '//me-talk.ru/support/support.js?h=d69fb0a94e3348422767ef6ae9e55e22';
        var sc = document.getElementsByTagName('script')[0];

        var callback = function(){
            /*
            Здесь вы можете вызывать API. Например, чтобы изменить отступ по высоте:
            supportAPI.setSupportTop(200);
            */
        };

        s.onreadystatechange = s.onload = function(){
            var state = s.readyState;
            if (!callback.done && (!state || /loaded|complete/.test(state))) {
                callback.done = true;
                callback();
            }
        };

        if (sc) sc.parentNode.insertBefore(s, sc);
        else document.documentElement.firstChild.appendChild(s);
    })();
</script>
<!-- END ME-TALK -->
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
</body>
</html>