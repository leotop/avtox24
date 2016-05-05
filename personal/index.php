<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Avtox24");
$APPLICATION->SetTitle("Персональный раздел");
?> 
<div class="personal-page-nav"> 	 
  <p>В личном кабинете Вы можете проверить текущее состояние корзины, ход выполнения Ваших заказов, просмотреть или изменить личную информацию, а также подписаться на новости и другие информационные рассылки. </p>
 	 
  <div> 		 
    <h4>Личная информация</h4>
   		 
    <ul class=""> 			 
      <li><a href="/personal/profile/" >Изменить регистрационные данные</a></li>
     
      <li><a href="/personal/garage/" >Мои автомобили</a></li>
     		</ul>
   	</div>
 	 
  <div> 		 
    <h4>Заказы</h4>
   		 
    <ul class=""> 			 
      <li><a href="/auto/vin/" >Запрос по VIN коду у менеджера</a></li>
    
      <li><a href="/personal/orders/" >Ознакомиться с состоянием заказов</a></li>
     
      <li><a href="/personal/notepad/" >Мой Блокнот</a></li>
     
      <li><a href="/personal/balance/" >История платежей</a></li>
     			 
      <li><a href="/auto/cart/" >Посмотреть содержимое корзины</a></li>
     		</ul>
   	</div>
 	 
  <div> 		 
    <h4>Подписка</h4>
   		 
    <ul class=""> 			 
      <li><a href="/personal/subscribe/" >Изменить подписку</a></li>
     		</ul>
   	</div>
 </div>
 <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>