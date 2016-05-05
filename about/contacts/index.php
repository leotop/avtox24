<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Avtox24");
/*$APPLICATION->SetTitle("Задайте вопрос");*/
?><p>
 <b>Телефоны отдела продаж:</b>
</p>
<p>
 <span style="font-size: large; text-align: right;"><b>8 (499) 350-36-04 </b></span>
</p>
<p>
 <b>Адрес:  </b><b style="font-size: large;"> </b><b>127238</b> <b>г. Москва, Ильменский пр-д., д.1.</b>
</p>
<p>
 <a href="mailto:info@avtox24.ru">info@</a><a href="mailto:info@avtox24.ru">avtox24.ru</a>
<p>
<script type="text/javascript" charset="utf-8" src="https://api-maps.yandex.ru/services/constructor/1.0/js/?sid=dh_w6LkreZTlkCG5jiHFW7PQqpfecPnf&width=-1&height=450"></script>
</p>
 <br>
<p>
	 <div style="page-break-after: always"><span style="display: none"> </span></div>Уважаемые покупатели! <br>
	 Прежде чем задать свой вопрос, обратите внимание на раздел <a href="../faq/">Помощь покупателю</a>. Возможно, там уже есть исчерпывающая информация по решению вашей проблемы.
</p>
 <?$APPLICATION->IncludeComponent(
	"bitrix:main.feedback", 
	"lm-auto", 
	array(
		"USE_CAPTCHA" => "Y",
		"OK_TEXT" => "Спасибо, ваше сообщение принято.",
		"EMAIL_TO" => "info@avtox24.ru",
		"REQUIRED_FIELDS" => array(
		),
		"EVENT_MESSAGE_ID" => array(
			0 => "7",
		),
		"COMPONENT_TEMPLATE" => "lm-auto"
	),
	false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php")?>