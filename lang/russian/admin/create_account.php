<?php
/* --------------------------------------------------------------
   $Id: create_account.php 985 2007-02-07 17:36:57 VaM $

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   --------------------------------------------------------------
   based on: 
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce(create_account.php,v 1.13 2003/05/19); www.oscommerce.com 
   (c) 2003	 nextcommerce (create_account.php,v 1.4 2003/08/14); www.nextcommerce.org
   (c) 2004	 xt:Commerce (create_account.php,v 1.4 2003/08/14); xt-commerce.com

   Released under the GNU General Public License 
   --------------------------------------------------------------*/

define('NAVBAR_TITLE', 'Создать аккаунт');

define('HEADING_TITLE', 'Мой кабинет');

define('TEXT_ORIGIN_LOGIN', '<font color="#FF0000"><small><b>ЗАМЕТКА:</b></font></small> Если у Вас уже есть в нашем магазине учетная запись, пожалуйста просто войдите здесь <a href="%s"><u>ВОЙТИ</u></a>.');

define('EMAIL_SUBJECT', 'Добро пожаловать в ' . STORE_NAME);
define('EMAIL_GREET_MR', 'Уважаемый господин. ' . stripslashes($HTTP_POST_VARS['lastname']) . ',' . "\n\n");
define('EMAIL_GREET_MS', 'Уважаемая госпожа. ' . stripslashes($HTTP_POST_VARS['lastname']) . ',' . "\n\n");
define('EMAIL_GREET_NONE', 'Дорогой (ая) ' . stripslashes($HTTP_POST_VARS['firstname']) . ',' . "\n\n");
define('EMAIL_WELCOME', 'Добро пожаловать в  <b>' . STORE_NAME . '</b>.' . "\n\n");
define('EMAIL_TEXT', 'Вы можете теперь использовать <b>различные услуги </b>, которые мы предлагаем Вам. Некоторые из услуг включают:'. "\n\n". '<li><b>Временную корзину</b> - Любые товары добавленные к вашей корзине покупок остаются там пока Вы не удалите их или не закажете эти товары или услуги.'. "\n". '<li><b>Адресная книга</b> - Мы можем теперь доставить ваши заказы по любому другому адресу кроме вашего основного! Это отличное предложение, чтобы например посылать подарки ко дню рождения или к праздникам, с доставкой в указанное Вами время!'. "\n". '<li><b>История заказов</b> - Рассматривать вашу историю приобретений, которыми Вы сделали унас.'. "\n". '<li><b>Отзывы о товарах и услугах</b> - Размещать ваше  мнение и отзывы о товарах и поделиться этим мнением с нашими другими клиентами.' . "\n\n");
define('EMAIL_CONTACT', 'Для помощи в использовании услуг, если есть проблемы или затруднения в использовании, Вы можете обратиться к владельцу магазина: ' . STORE_OWNER_EMAIL_ADDRESS . '.' . "\n\n");
define('EMAIL_WARNING', '<b>Внимание:</b> Этот E-Mail адрес был дан нам одним из наших клиентов. Если Вы не подписывались и не регистрировались и не являетесь клиентом нашего магазина, отправьте, пожалуйста, e-mail администрации нашего магазина, чтобы Ваш адрес был удален ' . STORE_OWNER_EMAIL_ADDRESS . '.' . "\n");
define('ENTRY_PAYMENT_UNALLOWED','Запрещённые модули оплаты:');
define('ENTRY_SHIPPING_UNALLOWED','Запрещённые модули доставки:');

define('CATEGORY_EXTRA_FIELDS','Дополнительная информация');

define('PULL_DOWN_DEFAULT','Выберите');

?>