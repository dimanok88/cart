<?php
/*------------------------------------------------------------------------------
   $Id: affiliate_banners.php,v 1.1 2003/12/21 20:13:07 hubi74 Exp $

   XTC-Affiliate - Contribution for XT-Commerce http://www.xt-commerce.com
   modified by http://www.netz-designer.de

   Copyright (c) 2003 netz-designer
   -----------------------------------------------------------------------------
   based on:
   (c) 2003 OSC-Affiliate (affiliate_banners.php, v 1.3 2003/02/16);
   http://oscaffiliate.sourceforge.net/

   Contribution based on:

   osCommerce, Open Source E-Commerce Solutions
   http://www.oscommerce.com

   Copyright (c) 2002 - 2003 osCommerce

   Released under the GNU General Public License
   ---------------------------------------------------------------------------*/

define('HEADING_TITLE', 'Баннеры');

define('TABLE_HEADING_BANNERS', 'Баннеры');
define('TABLE_HEADING_GROUPS', 'Группы');
define('TABLE_HEADING_ACTION', 'Действие');
define('TABLE_HEADING_STATISTICS', 'Статистика');
define('TABLE_HEADING_PRODUCT_ID', 'Код товара');

define('TEXT_BANNERS_TITLE', 'Название Баннера:');
define('TEXT_BANNERS_GROUP', 'Группа Баннера:');
define('TEXT_BANNERS_NEW_GROUP', ', или укажите новую группу');
define('TEXT_BANNERS_IMAGE', 'Баннер:');
define('TEXT_BANNERS_IMAGE_LOCAL', ', выберите файл на диске или укажите путь до баннера ниже');
define('TEXT_BANNERS_IMAGE_TARGET', 'Баннер (Сохранить как):');
define('TEXT_BANNERS_HTML_TEXT', 'HTML Код:');
define('TEXT_AFFILIATE_BANNERS_NOTE', '<b>Примечание:</b><ul><li>Используйте для баннера только изображение или HTML Код, но не одновременно оба способа.</li><li>    * HTML Код имеет приоритет над изображением</li></ul>');

define('TEXT_BANNERS_LINKED_PRODUCT','Код товара:');
define('TEXT_BANNERS_LINKED_PRODUCT_NOTE','Если Вы хотите чтобы баннер ссылался на конкретный товар, введите код товара выше, если Вы хотите чтобы баннер ссылался на главную страницу, просто поставьте 0 (ноль) в поле "Код товара"');

define('TEXT_BANNERS_DATE_ADDED', 'Дата создания:');
define('TEXT_BANNERS_STATUS_CHANGE', 'Последние изменения: %s');

define('TEXT_INFO_DELETE_INTRO', 'Вы действительно хотите удалить этот баннер?');
define('TEXT_INFO_DELETE_IMAGE', 'Удалить баннер');

define('SUCCESS_BANNER_INSERTED', 'Выполнено: Баннер успешно добавлен.');
define('SUCCESS_BANNER_UPDATED', 'Выполнено: Информация успешно обновлена.');
define('SUCCESS_BANNER_REMOVED', 'Выполнено: Баннер успешно удалён.');

define('ERROR_BANNER_TITLE_REQUIRED', 'Ошибка: Вы забыли указать название баннера.');
define('ERROR_BANNER_GROUP_REQUIRED', 'Ошибка: Вы забыли указать группу баннера.');
define('ERROR_IMAGE_DIRECTORY_DOES_NOT_EXIST', 'Ошибка: Директория, в которую загружается баннер отсутствует.');
define('ERROR_IMAGE_DIRECTORY_NOT_WRITEABLE', 'Ошибка: Директория, в которую загружается баннер защищена от записи, установите верные права доступа.');
define('ERROR_IMAGE_DOES_NOT_EXIST', 'Ошибка: Изображение отсутствует.');
define('ERROR_IMAGE_IS_NOT_WRITEABLE', 'Ошибка: Изображение не может быть удалено.');
?>