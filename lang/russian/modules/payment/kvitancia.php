<?php
/* -----------------------------------------------------------------------------------------
   $Id: eustandardtransfer.php 998 2007/02/07 13:24:46 VaM $
витанц
   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   -----------------------------------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(ptebanktransfer.php,v 1.4.1 2003/09/25 19:57:14); www.oscommerce.com
   (c) 2004	 xt:Commerce (eustandardtransfer.php,v 1.5 2003/08/13); xt-commerce.com

   Released under the GNU General Public License
   ---------------------------------------------------------------------------------------*/

  define('MODULE_PAYMENT_KVITANCIA_TEXT_TITLE', 'Квитанция СБ РФ');
  define('MODULE_PAYMENT_KVITANCIA_TEXT_DESCRIPTION', '<br /><strong>Квитанцию для оплаты Вы сможете распечатать на следующей странице.</strong><br /><br />Информация для оплаты:<br />' .
                                                         '<br />Название банка: ' . MODULE_PAYMENT_KVITANCIA_1 .
                                                         '<br />Расчётный счёт: ' . MODULE_PAYMENT_KVITANCIA_2 .
                                                         '<br />БИК: ' . MODULE_PAYMENT_KVITANCIA_3 .
                                                         '<br />Кор./счет: ' . MODULE_PAYMENT_KVITANCIA_4 .
                                                         '<br />ИНН: ' . MODULE_PAYMENT_KVITANCIA_5 .
                                                         '<br />Получатель: ' . MODULE_PAYMENT_KVITANCIA_6 .
                                                         '<br />КПП: ' . MODULE_PAYMENT_KVITANCIA_7 .
                                                         '<br /><br />Ваш заказ будет выполнен только после получения оплаты.<br />');
  define('MODULE_PAYMENT_KVITANCIA_TEXT_EMAIL_FOOTER', str_replace('<br />','\n',MODULE_PAYMENT_KVITANCIA_TEXT_DESCRIPTION));

  define('MODULE_PAYMENT_KVITANCIA_STATUS_TITLE','Разрешить модуль Квитанция СБ РФ');
  define('MODULE_PAYMENT_KVITANCIA_STATUS_DESC','Разрешить использование модуля Квитанция СБ РФ при оформлении заказа в магазине?');

  define('MODULE_PAYMENT_KVITANCIA_TEXT_INFO','');

  define('MODULE_PAYMENT_KVITANCIA_1_TITLE','Название банка');
  define('MODULE_PAYMENT_KVITANCIA_1_DESC','Укажите название банка.');

  define('MODULE_PAYMENT_KVITANCIA_2_TITLE','Расчётный счёт');
  define('MODULE_PAYMENT_KVITANCIA_2_DESC','Укажите Ваш расчетный счет.');

  define('MODULE_PAYMENT_KVITANCIA_3_TITLE','БИК');
  define('MODULE_PAYMENT_KVITANCIA_3_DESC','Укажите БИК.');

  define('MODULE_PAYMENT_KVITANCIA_4_TITLE','Кор./счет');
  define('MODULE_PAYMENT_KVITANCIA_4_DESC','Укажите Кор./счет.');

  define('MODULE_PAYMENT_KVITANCIA_5_TITLE','ИНН');
  define('MODULE_PAYMENT_KVITANCIA_5_DESC','Укажите ИНН.');

  define('MODULE_PAYMENT_KVITANCIA_6_TITLE','Получатель');
  define('MODULE_PAYMENT_KVITANCIA_6_DESC','Укажите получателя платежа.');

  define('MODULE_PAYMENT_KVITANCIA_7_TITLE','КПП');
  define('MODULE_PAYMENT_KVITANCIA_7_DESC','Укажите КПП.');

  define('MODULE_PAYMENT_KVITANCIA_8_TITLE','Назначение платежа');
  define('MODULE_PAYMENT_KVITANCIA_8_DESC','Укажите название платежа.');

  define('MODULE_PAYMENT_KVITANCIA_SORT_ORDER_TITLE','Порядок сортировки');
  define('MODULE_PAYMENT_KVITANCIA_SORT_ORDER_DESC','Укажите порядок сортировки модуля.');

  define('MODULE_PAYMENT_KVITANCIA_ALLOWED_TITLE' , 'Разрешённые страны');
  define('MODULE_PAYMENT_KVITANCIA_ALLOWED_DESC' , 'Укажите коды стран, для которых будет доступен данный модуль (например RU,DE (оставьте поле пустым, если хотите что б модуль был доступен покупателям из любых стран))');

  define('MODULE_PAYMENT_KVITANCIA_ZONE_TITLE' , 'Зона');
  define('MODULE_PAYMENT_KVITANCIA_ZONE_DESC' , 'Если выбрана зона, то данный модуль оплаты будет виден только покупателям из выбранной зоны.');

  define('MODULE_PAYMENT_KVITANCIA_ORDER_STATUS_ID_TITLE' , 'Статус заказа');
  define('MODULE_PAYMENT_KVITANCIA_ORDER_STATUS_ID_DESC' , 'Заказы, оформленные с использованием данного модуля оплаты будут принимать указанный статус.');

define('MODULE_PAYMENT_KVITANCIA_NAME_TITLE','Информация о плательщике');
define('MODULE_PAYMENT_KVITANCIA_NAME_DESC','');
define('MODULE_PAYMENT_KVITANCIA_NAME','ФИО:');
define('MODULE_PAYMENT_KVITANCIA_ADDRESS','Адрес:');
define('MODULE_PAYMENT_KVITANCIA_ADDRESS_HELP',' Пример: г. Ставрополь, ул. Мира 111, оф. 11');

?>