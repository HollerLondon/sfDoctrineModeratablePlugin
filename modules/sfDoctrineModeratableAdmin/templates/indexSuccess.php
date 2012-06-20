<?php use_helper('I18N', 'Date'); ?>
<?php use_stylesheet('/sfDoctrineModeratablePlugin/css/admin.css', 'last'); ?>

<?php include_partial($sf_params->get('module') . '/assets'); ?>
<?php include_partial('sfDoctrineModeratableAdmin/javascript'); ?>

<div id="sf_admin_container">
  <h1><?php echo __('Listing', array(), 'messages'); ?></h1>

  <?php include_partial($sf_params->get('module') . '/flashes'); ?>

  <div id="sf_admin_content">
    <?php include_partial($sf_params->get('module') . '/list_header', array('pager' => $pager)); ?>
    
    <!-- custom form for batch updating all in list -->
    <?php if ($pager->getNbResults()) : ?>
      <form action="<?php echo url_for('sfDoctrineModeratableAdmin/moderate'); ?>" action="post" id="moderation-list">
        <input type="hidden" name="class" value="<?php echo $pager->getClass(); ?>" />
    <?php endif; ?>
        
    <!-- normal admin generator list -->
    <?php include_partial($sf_params->get('module') . '/list', array('pager' => $pager, 'sort' => $sort, 'helper' => $helper)) ?>
    
    <?php if ($pager->getNbResults()) : ?>
      <?php include_partial('sfDoctrineModeratableAdmin/global'); ?>
    <?php endif; ?>
    
    <!-- close our custom form -->
    <?php if ($pager->getNbResults()) : ?>
        <input type="submit" value="Save changes" id="save-changes" />
      </form>
    
      <?php include_partial($sf_params->get('module') . '/list_footer', array('pager' => $pager)); ?>
    <?php endif; ?>    
  </div>
</div>
