<?php 
// send through as partial var - or use from config
if (empty($javascript)) $javascript = sfConfig::get('app_moderation_javascript', 'JQuery');

include_partial('sfDoctrineModeratableAdmin/javascript' . $javascript);
?>