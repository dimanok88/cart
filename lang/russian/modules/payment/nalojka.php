<?php
/* -----------------------------------------------------------------------------------------
   $Id: nalojka.php 998 2007/04/24 13:24:46 VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(cod.php,v 1.7 2002/04/17); www.oscommerce.com 
   (c) 2003	 nextcommerce (cod.php,v 1.5 2003/08/13); www.nextcommerce.org
   (c) 2004	 xt:Commerce (cod.php,v 1.5 2003/08/13); xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

define('MODULE_PAYMENT_NALOJKA_TEXT_TITLE', 'Наложенный платёж');
define('MODULE_PAYMENT_NALOJKA_TEXT_DESCRIPTION', 'Наложенный платёж');
define('MODULE_PAYMENT_NALOJKA_TEXT_INFO','');
define('MODULE_PAYMENT_NALOJKA_ZONE_TITLE' , 'Зона');
define('MODULE_PAYMENT_NALOJKA_ZONE_DESC' , 'Если выбрана зона, то данный модуль оплаты будет виден только покупателям из выбранной зоны.');
define('MODULE_PAYMENT_NALOJKA_ALLOWED_TITLE' , 'Разрешённые страны');
define('MODULE_PAYMENT_NALOJKA_ALLOWED_DESC' , 'Укажите коды стран, для которых будет доступен данный модуль (например RU,DE (оставьте поле пустым, если хотите что б модуль был доступен покупателям из любых стран))');
define('MODULE_PAYMENT_NALOJKA_STATUS_TITLE' , 'Разрешить модуль Оплата наличными при получении');
define('MODULE_PAYMENT_NALOJKA_STATUS_DESC' , 'Вы хотите разрешить использование модуля при оформлении заказов?');
define('MODULE_PAYMENT_NALOJKA_SORT_ORDER_TITLE' , 'Порядок сортировки');
define('MODULE_PAYMENT_NALOJKA_SORT_ORDER_DESC' , 'Порядок сортировки модуля.');
define('MODULE_PAYMENT_NALOJKA_ORDER_STATUS_ID_TITLE' , 'Статус заказа');
define('MODULE_PAYMENT_NALOJKA_ORDER_STATUS_ID_DESC' , 'Заказы, оформленные с использованием данного модуля оплаты будут принимать указанный статус.');
?>