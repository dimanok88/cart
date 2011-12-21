<?php /* Smarty version 2.6.25, created on 2011-12-21 15:50:53
         compiled from vamshop/boxes/box_order_history.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'vamshop/boxes/box_order_history.html', 1, false),)), $this); ?>
<?php echo smarty_function_config_load(array('file' => ($this->_tpl_vars['language'])."/lang_".($this->_tpl_vars['language']).".conf",'section' => 'boxes'), $this);?>


<table width="100%" border="0" cellpadding="2" cellspacing="0">
  <tr>
    <td class="infoBoxHeading_right"><table width="100%"  border="0" cellpadding="0" cellspacing="0">
  <tr>
    <td height="14" class="infoBoxHeading"><img src="<?php echo $this->_tpl_vars['tpl_path']; ?>
img/infobox/corner_right_left.gif" border="0" alt="" /></td>
    <td width="100%" height="14" class="infoBoxHeading"><span class="orderhistoryBox"><?php echo $this->_config[0]['vars']['heading_order_history']; ?>
</span></td>
    <td height="14" class="infoBoxHeading"><img src="<?php echo $this->_tpl_vars['tpl_path']; ?>
img/pixel_trans.gif" border="0" alt="" width="11" height="14" /></td>
  </tr>
    </table></td>
  </tr>
  <tr>
    <td class="infoBox_right" align="left"><table width="95%"  border="0" cellpadding="2" cellspacing="0">
        <tr>
          <td class="blockTitle"><?php echo $this->_tpl_vars['BOX_CONTENT']; ?>
</td>
        </tr>
    </table></td>
  </tr>
</table>