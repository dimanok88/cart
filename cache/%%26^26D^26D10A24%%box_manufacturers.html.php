<?php /* Smarty version 2.6.25, created on 2011-12-21 15:39:00
         compiled from vamshop/boxes/box_manufacturers.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'vamshop/boxes/box_manufacturers.html', 1, false),)), $this); ?>
<?php echo smarty_function_config_load(array('file' => ($this->_tpl_vars['language'])."/lang_".($this->_tpl_vars['language']).".conf",'section' => 'boxes'), $this);?>

<!-- Бокс производители -->
<div id="boxManufacturers">
<h5><?php echo $this->_config[0]['vars']['heading_manufacturers']; ?>
</h5>

<div id="boxManufacturersContent">
<?php echo $this->_tpl_vars['BOX_CONTENT']; ?>

</div>

</div>
<!-- /Бокс производители -->