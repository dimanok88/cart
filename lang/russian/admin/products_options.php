<?php
/* --------------------------------------------------------------
   $Id: products_options.php 1101 2007-02-07 17:36:57 VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(products_attributes.php,v 1.9 2002/03/30); www.oscommerce.com 
   (c) 2003	 nextcommerce (products_attributes.php,v 1.4 2003/08/1); www.nextcommerce.org
   (c) 2004	 xt:Commerce (products_attributes.php,v 1.4 2003/08/1); xt-commerce.com

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

define('HEADING_TITLE_OPT', 'Атрибуты товаров');
define('HEADING_TITLE_VAL', 'Названия атрибутов');
define('HEADING_TITLE_ATRIB', 'Атрибуты товаров');

define('TABLE_HEADING_ID', 'ID');
define('TABLE_HEADING_PRODUCT', 'Название товара');
define('TABLE_HEADING_OPT_NAME', 'Название атрибута');
define('TABLE_HEADING_OPT_VALUE', 'Значение атрибута');
define('TABLE_HEADING_OPT_PRICE', 'Цена');
define('TABLE_HEADING_OPT_PRICE_PREFIX', 'Префикс');
define('TABLE_HEADING_ACTION', 'Действие');
define('TABLE_HEADING_DOWNLOAD', 'Скачиваемые товары:');
define('TABLE_TEXT_FILENAME', 'Имя файла:');
define('TABLE_TEXT_MAX_DAYS', 'Дата окончания:');
define('TABLE_TEXT_MAX_COUNT', 'Максимум загрузок:');

define('MAX_ROW_LISTS_OPTIONS', 10);

define('TEXT_WARNING_OF_DELETE', 'Эта опция связана с товарами и не сохранится после ее удаления.');
define('TEXT_OK_TO_DELETE', 'Эта опция не связана с товарами и не сохранится после ее удаления.');
define('TEXT_SEARCH','Поиск: ');
define('TEXT_OPTION_ID', 'Код атрибута');
define('TEXT_OPTION_NAME', 'Название атрибута');

// VaM

define('TABLE_HEADING_OPT_IMAGE','Картинка');
define('TABLE_HEADING_OPT_DESC','Описание');
define('TABLE_TEXT_DELETE','Удалить картинку');
define('TEXT_OPTIONS_IMAGE','Картинка атрибута');
define('TEXT_NOTE','*ЗАМЕЧАНИЕ: Опции Ряды / Размер / Длина действительны только для атрибутов типа TEXT');
define('TEXT_ROWS','Ряды');
define('TEXT_SIZE','Размер');
define('TEXT_MAX_LENGTH','Длина');
define('TABLE_HEADING_OPT_TYPE','Тип атрибута');

define('TEXT_TYPE_SELECT','-- Выберите --');
define('TEXT_TYPE_DROPDOWN','Dropdown');
define('TEXT_TYPE_TEXT','Text');
define('TEXT_TYPE_TEXTAREA','Textarea');
define('TEXT_TYPE_RADIO','Radio');
define('TEXT_TYPE_CHECKBOX','Checkbox');
define('TEXT_TYPE_READ_ONLY','Только для чтения');

?>