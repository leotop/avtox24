<?
$aMenuLinks = Array(
	Array(
		"Вход / Регистрация", 
		"/login/", 
		Array(), 
		Array(), 
		"!\$USER->IsAuthorized()" 
	),
	Array(
		"Мой кабинет", 
		"/personal/", 
		Array(), 
		Array("ICON"=>"catalogs"), 
		"\$USER->IsAuthorized()" 
	),
	Array(
		"Запрос по VIN", 
		"/auto/vin/vin-request.php", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Мои автомобили", 
		"/personal/garage/", 
		Array(), 
		Array(), 
		"\$USER->IsAuthorized()" 
	),
	Array(
		"Мои заказы", 
		"/personal/orders/", 
		Array(), 
		Array(), 
		"\$USER->IsAuthorized()" 
	),
	Array(
		"История платежей", 
		"/personal/balance/", 
		Array(), 
		Array(), 
		"\$USER->IsAuthorized()" 
	),
	Array(
		"Мой блокнот", 
		"/personal/notepad/", 
		Array(), 
		Array(), 
		"\$USER->IsAuthorized()" 
	),
	Array(
		"Корзина", 
		"/auto/cart/", 
		Array(), 
		Array(), 
		"\$USER->IsAuthorized()" 
	),
	Array(
		"Форум", 
		"/forum/forum2/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Блог", 
		"/blog/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Новая тестовая версия", 
		"/html/", 
		Array(), 
		Array(), 
		"" 
	)
);
?>