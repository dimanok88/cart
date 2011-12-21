<?php
/* -----------------------------------------------------------------------------------------
   $Id: table.php 899 2007/02/07 13:24:46 VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(table.php,v 1.6 2003/02/16); www.oscommerce.com 
   (c) 2003	 nextcommerce (table.php,v 1.4 2003/08/13); www.nextcommerce.org
   (c) 2004	 xt:Commerce (table.php,v 1.4 2003/08/13); xt-commerce.com

   Released under the GNU General Public License 
   ---------------------------------------------------------------------------------------*/

define('MODULE_SHIPPING_UKRPOST_TEXT_TITLE', 'Укрпочта');
define('MODULE_SHIPPING_UKRPOST_TEXT_DESCRIPTION', 'Укрпочта');
define('MODULE_SHIPPING_UKRPOST_TEXT_WAY', '«Укрпочта» в любую точку Украины до указанного почтового отделения (наложенный платеж) ');
define('MODULE_SHIPPING_UKRPOST_TEXT_WEIGHT', 'Вес');
define('MODULE_SHIPPING_UKRPOST_TEXT_AMOUNT', 'Сумма');

define('MODULE_SHIPPING_UKRPOST_STATUS_TITLE' , 'Разрешить модуль Укрпочта');
define('MODULE_SHIPPING_UKRPOST_STATUS_DESC' , 'Вы хотите разрешить модуль доставки Укрпочта?');
define('MODULE_SHIPPING_UKRPOST_ALLOWED_TITLE' , 'Разрешённые страны');
define('MODULE_SHIPPING_UKRPOST_ALLOWED_DESC' , 'Укажите коды стран, для которых будет доступен данный модуль (например RU,DE (оставьте поле пустым, если хотите что б модуль был доступен покупателям из любых стран))');
define('MODULE_SHIPPING_UKRPOST_COST_TITLE' , 'Таблица тарифов');
define('MODULE_SHIPPING_UKRPOST_COST_DESC' , 'Стоимость доставки рассчитывается на основе общего веса заказа или общей стоимости заказа. Например: 25:8.50,50:5.50,и т.д... Это значит, что до 25 доставка будет стоить 8.50, от 25 до 50 будет стоить 5.50 и т.д.');
define('MODULE_SHIPPING_UKRPOST_MODE_TITLE' , 'Метод расчёта');
define('MODULE_SHIPPING_UKRPOST_MODE_DESC' , 'Стоимость расчёта доставки исходя из общего веса заказа (weight) или исходя из общей стоимости заказа (price).');
define('MODULE_SHIPPING_UKRPOST_HANDLING_TITLE' , 'Стоимость использования данного модуля');
define('MODULE_SHIPPING_UKRPOST_HANDLING_DESC' , 'Стоимость использования данного способа доставки.');
define('MODULE_SHIPPING_UKRPOST_TAX_CLASS_TITLE' , 'Налог');
define('MODULE_SHIPPING_UKRPOST_TAX_CLASS_DESC' , 'Использовать налог.');
define('MODULE_SHIPPING_UKRPOST_ZONE_TITLE' , 'Зона');
define('MODULE_SHIPPING_UKRPOST_ZONE_DESC' , 'Если выбрана зона, то данный модуль доставки будет виден только покупателям из выбранной зоны.');
define('MODULE_SHIPPING_UKRPOST_SORT_ORDER_TITLE' , 'Порядок сортировки');
define('MODULE_SHIPPING_UKRPOST_SORT_ORDER_DESC' , 'Порядок сортировки модуля.');
?>