<?php include_partial('sfDoctrineModeratableAdmin/moderation', array('status' =>'unmoderated', 'object' => $object)) ?><br />
<?php include_partial('sfDoctrineModeratableAdmin/moderation', array('status' =>'safe', 'object' => $object)) ?><br />
<?php include_partial('sfDoctrineModeratableAdmin/moderation', array('status' =>'followup', 'object' => $object)) ?><br />
<?php include_partial('sfDoctrineModeratableAdmin/moderation', array('status' =>'flagged', 'object' => $object)) ?><br />
<?php include_partial('sfDoctrineModeratableAdmin/moderation', array('status' =>'rejected', 'object' => $object)) ?>

