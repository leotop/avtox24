<?php
/**
 * Linemedia Autoportal
 * Statistics module
 * Connection to Linemedia statistics
 *
 * @author  Linemedia
 * @since   16/05/2014
 *
 *
 * @deprecated
 *
 * @link    http://auto.linemedia.ru/
 */



IncludeModuleLangFile(__FILE__);

class LinemediaAutoStat
{
	static $additional = array();
	
	
	// пока не ясно использование
	// http://piwik.org/docs/event-tracking/
	public static function addEvent($category, $action, $name, $value = null)
	{
		return;
		self::$additional[] = "_paq.push(['trackEvent', '$category', '$action', '$name', '$value']);";
	}
	
	
	
	/*
	*	Просмотр модификаций (напр какие есть Audi A3)
	*
	*	http://piwik.org/docs/ecommerce-analytics/#ecommerce-tracking
	
		 all parameters are optional, but we recommend to set at minimum productSKU and productName
		_paq.push(['setEcommerceView',
			"9780786706211", // (required) SKU: Product unique identifier
			"Endurance: Shackleton's Incredible Voyage", // (optional) Product name
			"Adventure Books", // (optional) Product category, or array of up to 5 categories
			20.11 // (optional) Product Price as displayed on the page
		]);
	*/
	public static function addTecdocModificationsView($arResult)
	{
		//self::$additional[] = "_paq.push(['setEcommerceView', '$brand_id|$model_id', $full_title, 'View car']);";
	}
	
	
	public static function addTecdocGroupsView($arResult)
	{
		//self::$additional[] = "_paq.push(['setEcommerceView', '$brand_id|$model_id', $full_title, 'View car']);";
	}
	
	
	public static function addTecdocPartsView($arResult)
	{
		//self::$additional[] = "_paq.push(['setEcommerceView', '$brand_id|$model_id', $full_title, 'View car']);";
	}
	
	
	
	/**
	 * Просмотр карточки текдока
	 * /auto/part-detail/3395460/6568281/
	 */
	public static function addTecdocArticleView($arResult)
	{
		return;
		$article 			= $arResult['DATA']['directArticle']['articleNo'];
		$brand_title 		= $arResult['DATA']['directArticle']['brandName'];
		$title 				= $arResult['DATA']['directArticle']['articleName'].' '.$arResult['DATA']['directArticle']['articleNo'];
		$generic_article_id = $arResult['DATA']['directArticle']['genericArticleId'];
		
		
		if ($brand_title != '') {
			$article = $brand_title . '|' . $article;
		}
		$article 			= json_encode($article);
		$title 				= json_encode($title);
		$generic_article_id	= (int) $generic_article_id;
		
		self::$additional[] = "_paq.push(['setEcommerceView', $article, $title, $generic_article_id]);";
	}
	
	
	/**
	 * Код на странице созданного заказа
	 * /auto/order/?ORDER_ID=491
	 */
	public static function addNewOrder($arResult)
	{
		return;
		$baskets_res = CSaleBasket::GetList(array(), array('ORDER_ID' => $arResult['ORDER_ID']));
		while($basket = $baskets_res->Fetch()) {
			$props = array();
			$res = CSaleBasket::GetPropsList(array(), array("BASKET_ID" => $basket['ID'], 'CODE' => array('article', 'brand_title')));
			while ($ar_res = $res->Fetch()) {
				$props[$ar_res['CODE']] = $ar_res;
			}
			
			self::addOrderedItem(
				$props['brand_title']['VALUE'] . '|' .$props['article']['VALUE'],
				$basket['NAME'],
				null,
				$basket['PRICE'],
				$basket['QUANTITY']
			);
		}
		
		self::applyOrder(
			$arResult['ORDER_ID'],
			$arResult['ORDER']['PRICE'],
			$arResult['ORDER']['PRICE'],
			$arResult['ORDER']['TAX_PRICE'],
			$arResult['ORDER']['DELIVERY_ID'],
			$arResult['ORDER']['DISCOUNT_PRICE']
		);
		
	}
	
	
	/**
	 * Вызывается, когда оформлен заказ, для каждого товара в заказе
	 * http://piwik.org/docs/ecommerce-analytics/#ecommerce-tracking
	 */
	public static function addOrderedItem($productSKU, $productName, $productCategory, $price, $quantity)
	{
		return;
		$price = (float) $price;
		$productSKU = json_encode($productSKU);
		$productName = json_encode($productName);
		$productCategory = json_encode($productCategory);
		
		self::$additional[] = "_paq.push(['addEcommerceItem', $productSKU, $productName, $productCategory, '$price', '$quantity']);";
	}
	
	
	/**
	 * Вызывается, когда оформлен заказ, после добавления всех корзин
	 * http://piwik.org/docs/ecommerce-analytics/#ecommerce-tracking
	 */
	public static function applyOrder($orderId, $grandTotal, $subTotal, $tax, $shipping, $discount)
	{
		return;
		$orderId 		= (string) $orderId;
		$grandTotal 	= (float) $grandTotal;
		$subTotal 		= (float) $subTotal;
		$tax 			= (float) $tax;
		$shipping 		= (float) $shipping; // !!!
		$discount 		= (float) $discount;
		self::$additional[] = "_paq.push(['trackEcommerceOrder', '$orderId', '$grandTotal', '$subTotal', '$tax', '$shipping', '$discount']);";
	}
	
	
	/**
	 * Для события, которое вставляет этот код перед </body>
	 */
	public static function OnEndBufferContent($html)
    {
    	//return - на выходе получается белый экран!!!;
    	// disable in admin panel
    	if (strpos($_SERVER['REQUEST_URI'], '/bitrix/') === 0) {
    		return $html;
    	}
    	
    	/*
    	 * Check if html
    	 */
    	foreach (headers_list() AS $header) {
    		// Content-Type: application/x-javascript; charset=UTF-8
	    	if (preg_match('#Content-Type:(.+)#is', $header, $matches)) {
		    	if (strpos($matches[1], 'html') === false) {
			    	return $html;
		    	}
	    	}
    	}
    	
    	
    	
	    $prepend = '
<script type="text/javascript">
var _paq = _paq || [];
_paq.push(["setDocumentTitle", document.domain + "/" + document.title]);
'.join("\n", self::$additional).'
_paq.push([\'trackPageView\']);
_paq.push([\'enableLinkTracking\']);
(function() {
var u=(("https:" == document.location.protocol) ? "https" : "http") + "://stat.auto-expert.info/";
_paq.push([\'setTrackerUrl\', u+\'piwik.php\']);
_paq.push([\'setSiteId\', 1]);
var d=document, g=d.createElement(\'script\'), s=d.getElementsByTagName(\'script\')[0]; g.type=\'text/javascript\';
g.defer=true; g.async=true; g.src=u+\'lm_stat.js\'; s.parentNode.insertBefore(g,s);
})();
</script>
<noscript><p><img src="http://stat.auto-expert.info/lm_stat.php?idsite=1" style="border:0;" alt="" /></p></noscript>


';
	    
	    $html = str_replace('</body>', $prepend.'</body>', $html);
	    
	    return $html;
    }
	
}