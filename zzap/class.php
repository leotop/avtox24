<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
file_exists(dirname(__FILE__) . "/user.php") ? include_once(dirname(__FILE__) . "/user.php") : "";

CModule::IncludeModule("sale");

class ZzapOrder {

	const DEFAULT_USER_ID = 1067;
	const PAYSYSTEM_ID = 11;
	const PERSON_TYPE_ID = 1;
	const PRICE_TYPE_ID = 1;
	const LOG_FILE_PATH = "/log/requests.log";
	const ERROR_FILE_PATH = "/log/error.log";
	
	public $zzap_order_fields = array();
	private $user = array();
	
	function __construct($request) {
		if (!empty($request) && is_array($request)) {
			$this->zzap_order_fields = array(
				"client" => array(
					"name"    => trim($request['client_name']),
					"email"   => trim($request['client_email']),
					"phone"   => trim($request['client_phone']),
					"hash"    => trim($request['client_id_hash']),
					"comment" => $request['comment']
				),
				"order" => array(
					"code"            => trim($request['code_order']),
					"part_brand"      => trim($request['class_man']),
					"part_number"     => trim($request['partnumber']),
					"part_name"       => trim($request['class_cat']),
					"quantity"        => intval($request['qty_order']),
					"price"           => floatval($request['price']),
					"price_list_file" => $request['file_name']
				)
			);
		}
	}
	
	
	/**
	 * Добавляем запчасть в корзину
	 * 
	 * @return void
	 * 
	 * */
	private function addProductToBasket() {
		$basket_id = CSaleBasket::Add(
			array(
			    "PRODUCT_ID"       => $this->zzap_order_fields['order']['code'],
			    "PRODUCT_XML_ID"   => $this->zzap_order_fields['order']['code'],
			    "PRICE"            => $this->zzap_order_fields['order']['price'],
			    "PRODUCT_PRICE_ID" => self::PRICE_TYPE_ID,
			    "CURRENCY"         => "RUB",
			    "WEIGHT"           => 0,
			    "QUANTITY"         => $this->zzap_order_fields['order']['quantity'],
			    "LID"              => "s1",
			    "DELAY"            => "N",
			    "CAN_BUY"          => "Y",
			    "NAME"             => sprintf("%s [%s] %s", $this->zzap_order_fields['order']['part_brand'], $this->zzap_order_fields['order']['part_number'], $this->zzap_order_fields['order']['part_name']),
			    "MODULE"           => "linemedia.auto"
			)
		);
	}
	
	/**
	 * 
	 * Получим ID пользователя
	 *  
	 * @return int user_id 
	 * 
	 * */
	
	private function getUserID() {
		$user_id = 0;
		// проверяем, может быть пользователь уже существует
		$user_entity = APIUser::get("ADMIN_NOTES", $this->zzap_order_fields['client']['hash']);
		if (!empty($user_entity) && is_array($user_entity)) {
			$this->user = $user_entity;
			$user_id = $user_entity['ID'];
		} else {
			// пользователь не нашелся, нужно создать
			$id = APIUser::add(Array(
			  "NAME"              => $this->zzap_order_fields['client']['name'],
			  "EMAIL"             => $this->zzap_order_fields['client']['email'],
			  "LOGIN"             => $this->zzap_order_fields['client']['email'],
			  "PERSONAL_PHONE"    => $this->zzap_order_fields['client']['phone'],
			  "ACTIVE"            => "Y",
			  "GROUP_ID"          => array(7),
			  "PASSWORD"          => $this->zzap_order_fields['client']['email'],
			  "CONFIRM_PASSWORD"  => $this->zzap_order_fields['client']['email'],
			  "ADMIN_NOTES"       => $this->zzap_order_fields['client']['hash']
			));
			$user_id = $id;
		}
		return $user_id;
	}
	
	/**
	 * 
	 * Обновим свойства заказа
	 * 
	 * @param int $order_id
	 * @param int $property_id
	 * @param string $property_name
	 * @param string $property_code
	 * @param mixed $value
	 * @return void
	 * 
	 * */
	private function updateOrderProperties($order_id, $property_id, $property_name, $property_code, $value) {
		$fields = array(
		   "ORDER_ID"       => $order_id,
		   "ORDER_PROPS_ID" => $property_id,
		   "NAME"           => $property_name,
		   "CODE"           => $property_code,
		   "VALUE"          => $value
		);
		
		CSaleOrderPropsValue::Add($fields);
	}
	
	
	/**
	 * 
	 * Создаем заказ
	 * 
	 * @return int order_id
	 * 
	 * */
	public function putOrder() {
		
		$user_id = $this->getUserID();
		// если это уже существующий пользователь, то посмотрим, не обновились его персональные данные
		if (!empty($this->user) && is_array($this->user)) {
			if (
				$this->user['NAME'] != $this->zzap_order_fields['client']['name'] 
				|| $this->user['EMAIL'] != $this->zzap_order_fields['client']['email']
				|| $this->user['PERSONAL_PHONE'] != $this->zzap_order_fields['client']['phone']
			) {
				// если что-то изменилось, выполним update
				APIUser::update(
					$user_id,
					Array(
						"NAME"           => $this->zzap_order_fields['client']['name'],
						"EMAIL"          => $this->zzap_order_fields['client']['email'],
						"PERSONAL_PHONE" => $this->zzap_order_fields['client']['phone']
					)
				);
			}
		}

		$this->addProductToBasket();
		// поля заказа
		$order_fields = array(
		    "LID"            => 's1',
		    "PERSON_TYPE_ID" => self::PERSON_TYPE_ID,
		    "PAYED"          => "N",
		    "CANCELED"       => "N",
		    "STATUS_ID"      => "N",
		    "CURRENCY"       => "RUB",
		    "USER_ID"        => $user_id ? $user_id : self::DEFAULT_USER_ID,
		    "PAY_SYSTEM_ID"  => self::PAYSYSTEM_ID,
		    "COMMENTS"       => sprintf("Заказ с ZZAP - Прайс %s - %s комментарий покупателя к заказу", $this->zzap_order_fields['order']['price_list_file'], $this->zzap_order_fields['client']['comment'])
		);
		$order_id = CSaleOrder::Add($order_fields);
		CSaleBasket::OrderBasket($order_id, CSaleBasket::GetBasketUserID());
		// добавляем свойства
		$this->updateOrderProperties($order_id, 1, "Ф.И.О.", "FIO", $this->zzap_order_fields['client']['name']);
		$this->updateOrderProperties($order_id, 2, "E-Mail", "EMAIL", $this->zzap_order_fields['client']['email']);
		$this->updateOrderProperties($order_id, 3, "Телефон", "PHONE", $this->zzap_order_fields['client']['phone']);
	}
}
?>