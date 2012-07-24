<?php 
// send through as partial var - or use from config
if (empty($javascript)) $javascript = sfConfig::get('app_moderation_javascript', 'JQuery');

slot('moderation_js', get_partial('sfDoctrineModeratableAdmin/javascript' . $javascript));

if (!sfConfig::get('app_moderation_use_slots', false)) include_slot('moderation_js');
?>