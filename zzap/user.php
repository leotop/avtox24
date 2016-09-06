<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
	class APIUser {
		
		/**
		 * 
		 * Ищет пользователя по переденному параметру
		 * 
		 * @param string parameter
		 * @param mixed value
		 * @return bool|array
		 * 
		 * */
		public static function get($parameter, $value) {
			$users = CUser::GetList(
				($by="id"),
				($order="asc"),
				array(
					$parameter => $value
				)
			);
			if ($user = $users->Fetch()) {
				return $user;
			}
		}
		
		/**
		 * 
		 * Обновляем данные для юзера
		 * 
		 * @param int $user_id
		 * @param array $data
		 * @return void
		 * 
		 * */
		
		public static function update($user_id, $data) {
			$user = new CUser;
			$user->Update($user_id, $data);
		}
		
		
		/**
		 * 
		 * Добавляем нового пользователя
		 * 
		 * @param array fields
		 * @return int new_user_id
		 * 
		 * */
		public static function add($fields) {
			$user = new CUser;
			$new_user_id = $user->Add($fields);
			return $new_user_id;
		}
	}
?>