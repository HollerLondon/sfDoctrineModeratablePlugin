<label>
  <input type="radio" value="<?php echo $status ?>" name="<?php printf('ids[%s]', $object['id']) ?>" <?php echo ($object['moderation_status'] == $status) ? 'checked="checked" ' : '' ?>/>
  <?php echo $status ?>
</label>
&nbsp;