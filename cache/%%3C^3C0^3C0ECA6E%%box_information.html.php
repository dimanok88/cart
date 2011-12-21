<?php /* Smarty version 2.6.25, created on 2011-12-21 15:39:00
         compiled from vamshop/boxes/box_information.html */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'vamshop/boxes/box_information.html', 1, false),)), $this); ?>
<?php echo smarty_function_config_load(array('file' => ($this->_tpl_vars['language'])."/lang_".($this->_tpl_vars['language']).".conf",'section' => 'boxes'), $this);?>

<!-- Бокс информация -->
<div id="boxInformation">
<h5><?php echo $this->_config[0]['vars']['heading_infobox']; ?>
</h5>

<div id="boxInformationContent">
<ul>
<?php echo $this->_tpl_vars['BOX_CONTENT']; ?>

</ul>
</div>

</div>
<!-- /Бокс информация -->