<?php
/* --------------------------------------------------------------
   $Id: stats_sales_report.php 1311 2007-02-08 11:13:01Z VaM $   

   VaM Shop - open source ecommerce solution
   http://vamshop.ru
   http://vamshop.com

   Copyright (c) 2007 VaM Shop
   --------------------------------------------------------------
   based on:
   (c) 2000-2001 The Exchange Project  (earlier name of osCommerce)
   (c) 2002-2003 osCommerce coding standards; www.oscommerce.com
   (c) 2004	 xt:Commerce (stats_sales_report.php,v 1.9 2003/08/18); xt-commerce.com

   Released under the GNU General Public License
   --------------------------------------------------------------
   Third Party contribution:

   stats_sales_report (c) Charly Wilhelm  charly@yoshi.ch

   possible views (srView):
  1 yearly
  2 monthly
  3 weekly
  4 daily

  possible options (srDetail):
  0 no detail
  1 show details (products)
  2 show details only (products)

  export
  0 normal view
  1 html view without left and right
  2 csv

  sort
  0 no sorting
  1 product description asc
  2 product description desc
  3 #product asc, product descr asc
  4 #product desc, product descr desc
  5 revenue asc, product descr asc
  6 revenue desc, product descr des

   Released under the GNU General Public License
   --------------------------------------------------------------*/


  require('includes/application_top.php');

  require(DIR_WS_CLASSES . 'currencies.php');
  $currencies = new currencies();

  // default detail no detail
  $srDefaultDetail = 0;
  // default view (daily)
  $srDefaultView = 2;
  // default export
  $srDefaultExp = 0;
  // default sort
  $srDefaultSort = 4;
  
  // report views (1: yearly 2: monthly 3: weekly 4: daily)
  if ( ($_GET['report']) && (vam_not_null($_GET['report'])) )
{    $srView = $_GET['report'];
  }
  if ($srView < 1 || $srView > 4) {
    $srView = $srDefaultView;
  }

  // detail
  if ( ($_GET['detail']) && (vam_not_null($_GET['detail'])) )
{    $srDetail = $_GET['detail'];
  }
  if ($srDetail < 0 || $srDetail > 2) {
    $srDetail = $srDefaultDetail;
  }
  
  // report views (1: yearly 2: monthly 3: weekly 4: daily)
  if ( ($_GET['export']) && (vam_not_null($_GET['export'])) )
{    $srExp = $_GET['export'];
  }
  if ($srExp < 0 || $srExp > 2) {
    $srExp = $srDefaultExp;
  }
  
  // item_level
  if ( ($_GET['max']) && (vam_not_null($_GET['max'])) ) {
    $srMax = $_GET['max'];
  }
  if (!is_numeric($srMax)) {
    $srMax = 0;
  }
      
  // order status
  if ( ($_GET['status']) && (vam_not_null($_GET['status'])) )
{    $srStatus = $_GET['status'];
  }
  if (!is_numeric($srStatus)) {
    $srStatus = 0;
  }
  
   // paymenttype
  if ( ($_GET['payment']) && (vam_not_null($_GET['payment'])) )
{    $srPayment = $_GET['payment'];
  } else {
 	$srPayment = 0;
  }
  
  // sort
  if ( ($_GET['sort']) && (vam_not_null($_GET['sort'])) ) {
    $srSort = $_GET['sort'];
  }
  if ($srSort < 1 || $srSort > 6) {
    $srSort = $srDefaultSort;
  }
    
  // check start and end Date
  $startDate = "";
  $startDateG = 0;
  if ( ($_GET['startD']) && (vam_not_null($_GET['startD'])) )
{    $sDay = $_GET['startD'];
    $startDateG = 1;
  } else {
    $sDay = 1;
  }
  if ( ($_GET['startM']) && (vam_not_null($_GET['startM'])) )
{    $sMon = $_GET['startM'];
    $startDateG = 1;
  } else {
    $sMon = 1;
  }
  if ( ($_GET['startY']) && (vam_not_null($_GET['startY'])) )
{    $sYear = $_GET['startY'];
    $startDateG = 1;
  } else {
    $sYear = date("Y");
  }
  if ($startDateG) {
    $startDate = mktime(0, 0, 0, $sMon, $sDay, $sYear);
  } else {
    $startDate = mktime(0, 0, 0, date("m"), 1, date("Y"));
  }
    
  $endDate = "";
  $endDateG = 0;
  if ( ($_GET['endD']) && (vam_not_null($_GET['endD'])) ) {
    $eDay = $_GET['endD'];
    $endDateG = 1;
  } else {
    $eDay = 1;
  }
  if ( ($_GET['endM']) && (vam_not_null($_GET['endM'])) ) {
    $eMon = $_GET['endM'];
    $endDateG = 1;
  } else {
    $eMon = 1;
  }
  if ( ($_GET['endY']) && (vam_not_null($_GET['endY'])) ) {
    $eYear = $_GET['endY'];
    $endDateG = 1;
  } else {
    $eYear = date("Y");
  }
  if ($endDateG) {
    $endDate = mktime(0, 0, 0, $eMon, $eDay + 1, $eYear);
  } else {
    $endDate = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
  }
  
  require(DIR_WS_CLASSES . 'sales_report.php');
  $sr = new sales_report($srView, $startDate, $endDate, $srSort, $srStatus, $srFilter,$srPayment);  
  $startDate = $sr->startDate;
  $endDate = $sr->endDate;  

  if ($srExp < 2) {
    // not for csv export
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo $_SESSION['language_charset']; ?>"> 
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
</head>
<body marginwidth="0" marginheight="0" topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" bgcolor="#FFFFFF">
<?php
    if ($srExp < 1) {
      require(DIR_WS_INCLUDES . 'header.php');
    }
?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<?php if (ADMIN_DROP_DOWN_NAVIGATION == 'false') { ?>
    <td width="<?php echo BOX_WIDTH; ?>" align="left" valign="top">
<!-- left_navigation //-->
<?php require(DIR_WS_INCLUDES . 'column_left.php'); ?>
<!-- left_navigation_eof //-->
    </td>
<?php } ?>
<!-- body_text //-->
<?php
    if ($srExp < 1) {
?>
<!-- body_text //-->
<?php
    } // end sr_exp
?>
    <td class="boxCenter" valign="top">
    
    <h1 class="contentBoxHeading"><?php echo HEADING_TITLE; ?></h1>
    
    <table border="0" width="100%" cellspacing="0" cellpadding="2">
<?php
    if ($srExp < 1) {
?>
        <tr>
          <td colspan="2">
            <form action="" method="get">
              <table border="0" style="border: 1px solid; border-color: #cccccc;" width="100%" cellspacing="0" cellpadding="0">
                <tr>
                  <td align="left" rowspan="2" class="menuBoxHeading">
                    <input type="radio" name="report" value="1" <?php if ($srView == 1) echo "checked"; ?>><?php echo REPORT_TYPE_YEARLY; ?><br />
                    <input type="radio" name="report" value="2" <?php if ($srView == 2) echo "checked"; ?>><?php echo REPORT_TYPE_MONTHLY; ?><br />
                    <input type="radio" name="report" value="3" <?php if ($srView == 3) echo "checked"; ?>><?php echo REPORT_TYPE_WEEKLY; ?><br />
                    <input type="radio" name="report" value="4" <?php if ($srView == 4) echo "checked"; ?>><?php echo REPORT_TYPE_DAILY; ?><br />
                  </td>
                  <td class="menuBoxHeading">
<?php echo REPORT_START_DATE; ?><br />
                    <select name="startD" size="1">
<?php
      if ($startDate) {
        $j = date("j", $startDate);
      } else {
        $j = 1;
      }
      for ($i = 1; $i < 32; $i++) {
?>
                        <option<?php if ($j == $i) echo " selected"; ?>><?php echo $i; ?></option>
<?php
      }
?>
                    </select>
                    <select name="startM" size="1">
<?php
      if ($startDate) {
        $m = date("n", $startDate);
      } else {
        $m = 1;
      }
      for ($i = 1; $i < 13; $i++) {
?>
                      <option<?php if ($m == $i) echo " selected"; ?> value="<?php echo $i; ?>"><?php echo strftime("%B", mktime(0, 0, 0, $i, 1)); ?></option>
<?php
      }
?>
                    </select>
                    <select name="startY" size="1">
<?php
      if ($startDate) {
        $y = date("Y") - date("Y", $startDate);
      } else {
        $y = 0;
      }
      for ($i = 10; $i >= 0; $i--) {
?>
                      <option<?php if ($y == $i) echo " selected"; ?>><?php echo date("Y") - $i; ?></option>
<?php
    }
?>
                    </select>
                  </td>
                  <td rowspan="2" align="left" class="menuBoxHeading">
                    <?php echo REPORT_DETAIL; ?><br />
                    <select name="detail" size="1">
                      <option value="0"<?php if ($srDetail == 0) echo "selected"; ?>><?php echo DET_HEAD_ONLY; ?></option>
                      <option value="1"<?php if ($srDetail == 1) echo " selected"; ?>><?php echo DET_DETAIL; ?></option>
                      <option value="2"<?php if ($srDetail == 2) echo " selected"; ?>><?php echo DET_DETAIL_ONLY; ?></option>
                    </select><br />
<?php echo REPORT_MAX; ?><br />
                    <select name="max" size="1">
                      <option value="0"><?php echo REPORT_ALL; ?></option>
                      <option<?php if ($srMax == 1) echo " selected"; ?>>1</option>
                      <option<?php if ($srMax == 3) echo " selected"; ?>>3</option>
                      <option<?php if ($srMax == 5) echo " selected"; ?>>5</option>
                      <option<?php if ($srMax == 10) echo " selected"; ?>>10</option>
                      <option<?php if ($srMax == 25) echo " selected"; ?>>25</option>
                      <option<?php if ($srMax == 50) echo " selected"; ?>>50</option>
                    </select>
                  </td>
                  <td rowspan="2" align="left" class="menuBoxHeading">
                    <?php echo REPORT_STATUS_FILTER; ?><br />
                    <select name="status" size="1">
                      <option value="0"><?php echo REPORT_ALL; ?></option>
<?php
                        foreach ($sr->status as $value) {
?>
                      <option value="<?php echo $value["orders_status_id"]?>"<?php if ($srStatus == $value["orders_status_id"]) echo " selected"; ?>><?php echo $value["orders_status_name"] ; ?></option>
<?php
                         }
?>
                    </select><br />
                   <?php echo REPORT_PAYMENT_FILTER; ?><br />
                    <select name="payment" size="1">
                      <option value="0"><?php echo REPORT_ALL; ?></option>
<?php

  $payments = preg_split('/;/', MODULE_PAYMENT_INSTALLED);
  for ($i=0; $i<count($payments); $i++){
  
  require(DIR_FS_LANGUAGES . $_SESSION['language'] . '/modules/payment/' . $payments[$i]);	
  
  $payment = substr($payments[$i], 0, strrpos($payments[$i], '.'));	
  $payment_text = constant(MODULE_PAYMENT_.strtoupper($payment)._TEXT_TITLE);
  
                         
?>                           
    <option value="<?php echo $payment; ?>"<?php if ($payment = $srPayment) echo " selected"; ?>><?php echo $payment_text; ?></option>
                           
  <?php                         
  }
                       
?>
                    </select><br />
                  </td>
                  <td rowspan="2" align="left" class="menuBoxHeading">
                    <?php echo REPORT_EXP; ?><br />
                    <select name="export" size="1">
                      <option value="0" selected><?php echo EXP_NORMAL; ?></option>
                      <option value="1"><?php echo EXP_HTML; ?></option>
                      <option value="2"><?php echo EXP_CSV; ?></option>
                    </select><br />
                    <?php echo REPORT_SORT; ?><br />
                    <select name="sort" size="1">
                      <option value="0"<?php if ($srSort == 0) echo " selected"; ?>><?php echo SORT_VAL0; ?></option>
                      <option value="1"<?php if ($srSort == 1) echo " selected"; ?>><?php echo SORT_VAL1; ?></option>
                      <option value="2"<?php if ($srSort == 2) echo " selected"; ?>><?php echo SORT_VAL2; ?></option>
                      <option value="3"<?php if ($srSort == 3) echo " selected"; ?>><?php echo SORT_VAL3; ?></option>
                      <option value="4"<?php if ($srSort == 4) echo " selected"; ?>><?php echo SORT_VAL4; ?></option>
                      <option value="5"<?php if ($srSort == 5) echo " selected"; ?>><?php echo SORT_VAL5; ?></option>
                      <option value="6"<?php if ($srSort == 6) echo " selected"; ?>><?php echo SORT_VAL6; ?></option>
                    </select><br />
                  </td>
                </tr>
                <tr>
                  <td class="menuBoxHeading">
<?php echo REPORT_END_DATE; ?><br />
                    <select name="endD" size="1">
<?php
    if ($endDate) {
      $j = date("j", $endDate - 60* 60 * 24);
    } else {
      $j = date("j");
    }
    for ($i = 1; $i < 32; $i++) {
?>
                      <option<?php if ($j == $i) echo " selected"; ?>><?php echo $i; ?></option>
<?php
    }
?>
                    </select>
                    <select name="endM" size="1">
<?php
    if ($endDate) {
      $m = date("n", $endDate - 60* 60 * 24);
    } else {
      $m = date("n");
    }
    for ($i = 1; $i < 13; $i++) {
?>
                      <option<?php if ($m == $i) echo " selected"; ?> value="<?php echo $i; ?>"><?php echo strftime("%B", mktime(0, 0, 0, $i, 1)); ?></option>
<?php
    }
?>
                    </select>
                    <select name="endY" size="1">
<?php
    if ($endDate) {
      $y = date("Y") - date("Y", $endDate - 60* 60 * 24);
    } else {
      $y = 0;
    }
    for ($i = 10; $i >= 0; $i--) {
?>
                      <option<?php if ($y == $i) echo " selected"; ?>><?php echo
date("Y") - $i; ?></option><?php
    }
?>
                    </select>
                  </td>
                </tr>
                <tr>
                  <td colspan="5" class="menuBoxHeading" align="right">
                  <?php echo '<span class="button"><button type="submit" value="' . BUTTON_UPDATE . '">' . BUTTON_UPDATE . '</button></span>'; ?>
                  </td>
              </table>
            </form>
          </td>
        </tr>
<?php
  } // end of ($srExp < 1)
?>
        <tr>
          <td width=100% valign=top>
            <table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr>
                <td valign="top">
                  <table border="0" width="100%" cellspacing="2" cellpadding="0" class="contentListingTable">
                    <tr class="dataTableHeadingRow">
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_DATE; ?></td>
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ORDERS;?></td>
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ITEMS; ?></td>
                      <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_REVENUE;?></td>
                      <td class="dataTableHeadingContent" align="right"><?php echo  TABLE_HEADING_SHIPPING;?></td>
                    </tr>
<?php
} // end of if $srExp < 2 csv export
$sum = 0;
while ($sr->actDate < $sr->endDate) {
  $info = $sr->getNext();
  $last = sizeof($info) - 1;
  if ($srExp < 2) {
?>
                    <tr class="dataTableRow" onmouseover="this.className='dataTableRowOver';this.style.cursor='hand'" onmouseout="this.className='dataTableRow'">
<?php
    switch ($srView) {
      case '3':
?>
                      <td class="dataTableContent" align="right"><?php echo vam_date_long(date("Y-m-d\ H:i:s", $sr->showDate)) . " - " . vam_date_short(date("Y-m-d\ H:i:s", $sr->showDateEnd)); ?></td>
<?php
        break;
      case '4':
?>
                      <td class="dataTableContent" align="right"><?php echo vam_date_long(date("Y-m-d\ H:i:s", $sr->showDate)); ?></td>
<?php
        break;
      default;
?>
                      <td class="dataTableContent" align="right"><?php echo vam_date_short(date("Y-m-d\ H:i:s", $sr->showDate)) . " - " . vam_date_short(date("Y-m-d\ H:i:s", $sr->showDateEnd)); ?></td>
<?php
    }
?>
                      <td class="dataTableContent" align="right"><?php echo $info[0]['order']; ?></td>
                      <td class="dataTableContent" align="right"><?php echo $info[$last - 1]['totitem']; ?></td>
                      <td class="dataTableContent" align="right"><?php echo $currencies->format($info[$last - 1]['totsum']);?></td>
                      <td class="dataTableContent" align="right"><?php echo $currencies->format($info[0]['shipping']);?></td>
                    </tr>
<?php
  } else {
    // csv export
    echo date(DATE_FORMAT, $sr->showDate) . SR_SEPARATOR1 . date(DATE_FORMAT, $sr->showDateEnd) . SR_SEPARATOR1;
    echo $info[0]['order'] . SR_SEPARATOR1;
    echo $info[$last - 1]['totitem'] . SR_SEPARATOR1;
    echo $currencies->format($info[$last - 1]['totsum']) . SR_SEPARATOR1;
    echo $currencies->format($info[0]['shipping']) . SR_NEWLINE;
  }
  if ($srDetail) {
    for ($i = 0; $i < $last; $i++) {
      if ($srMax == 0 or $i < $srMax) {
        if ($srExp < 2) {
?>
                    <tr class="dataTableRow" onmouseover="this.className='dataTableRowOver';this.style.cursor='hand'" onmouseout="this.className='dataTableRow'">
                    <td class="dataTableContent">&nbsp;</td>
                    <td class="dataTableContent" align="left"><a href="<?php echo vam_catalog_href_link("product_info.php?products_id=" . $info[$i]['pid']) ?>" target="_blank"><?php echo $info[$i]['pmodel'].' : '.$info[$i]['pname']; ?></a>
<?php
  if (is_array($info[$i]['attr'])) {
    $attr_info = $info[$i]['attr'];
    foreach ($attr_info as $attr) {
      echo '<div style="font-style:italic;">&nbsp;' . $attr['quant'] . 'x ' ;
      //  $attr['options'] . ': '
      $flag = 0;
      foreach ($attr['options_values'] as $value) {
        if ($flag > 0) {
          echo "," . $value;
        } else {
          echo $value;
          $flag = 1;
        }
      }
      $price = 0;
      foreach ($attr['price'] as $value) {
        $price += $value;
      }
      if ($price != 0) {
        echo ' (';
        if ($price > 0) {
          echo "+";
        }
        echo $currencies->format($price). ')';
      }
      echo '</div>';
    }
  }
?>                    </td>
                    <td class="dataTableContent" align="right"><?php echo $info[$i]['pquant']; ?></td>
<?php
          if ($srDetail == 2) {?>
                    <td class="dataTableContent" align="right"><?php echo $currencies->format($info[$i]['psum']); ?></td>
<?php
          } else { ?>
                    <td class="dataTableContent">&nbsp;</td>
<?php
          }
?>
                    <td class="dataTableContent">&nbsp;</td>
                  </tr>
<?php
        } else {
        // csv export
          if (is_array($info[$i]['attr'])) {
            $attr_info = $info[$i]['attr'];
            foreach ($attr_info as $attr) {
              echo $info[$i]['pname'] . "(";
              $flag = 0;
              foreach ($attr['options_values'] as $value) {
                if ($flag > 0) {
                  echo ", " . $value;
                } else {
                  echo $value;
                  $flag = 1;
                }
              }
              $price = 0;
              foreach ($attr['price'] as $value) {
                $price += $value;
              }
              if ($price != 0) {
                echo ' (';
                if ($price > 0) {
                  echo "+";
                } else {
                  echo " ";
                }
                echo $currencies->format($price). ')';
              }
              echo ")" . SR_SEPARATOR2;
              if ($srDetail == 2) {
                echo $attr['quant'] . SR_SEPARATOR2;
                echo $currencies->format( $attr['quant'] * ($info[$i]['price'] + $price)) . SR_NEWLINE;
              } else {
                echo $attr['quant'] . SR_NEWLINE;
              }
              $info[$i]['pquant'] = $info[$i]['pquant'] - $attr['quant'];
            }
          }
          if ($info[$i]['pquant'] > 0) {
            echo $info[$i]['pmodel'].SR_SEPARATOR2.$info[$i]['pname'] . SR_SEPARATOR2;
            if ($srDetail == 2) {
              echo $info[$i]['pquant'] . SR_SEPARATOR2;
              echo $currencies->format($info[$i]['pquant'] * $info[$i]['price']) . SR_NEWLINE;
            } else {
              echo $info[$i]['pquant'] . SR_NEWLINE;
            }
          }
        }
      }
    }
  }
}
if ($srExp < 2) {
?>
                  </table>
                </td>
              </tr>
            </table>
          </td>
        </tr>
      </table>
    </td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->

<!-- footer //-->
<?php
  if ($srExp < 1) {
    require(DIR_WS_INCLUDES . 'footer.php');
  }
?>
<!-- footer_eof //-->
</body>
</html>
<?php
  require(DIR_WS_INCLUDES . 'application_bottom.php');
} // end if $srExp < 2
?>