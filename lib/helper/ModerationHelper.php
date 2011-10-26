<?php
/**
 * Balls to monty.
 * 
 * @deprecated use link_to_report_item() instead
 */
function link_to_report($text, $obj, $options = array())
{
  if($obj instanceof sfOutputEscaper)
  {
    return link_to_report($text,$obj->getRawValue(),$options);
  }
  
  if(!$obj instanceof Doctrine_Record)
  {
    return;
  }

  $model = get_class($obj);

  $options = _parse_attributes($options);
  $options['rel'] = array_key_exists('rel', $options)
    ? $options['rel']
    : 'nofollow';

  return link_to($text,'report_item',
    array(
      'model' => get_class($obj),
      'hash'  => sha1($model.$obj['id'])
    ),
    $options
  );
}

/**
 * This is a more portable function name, use this bad boy.
 */
function link_to_report_item($text, $obj, $options = array())
{
  return link_to_report($text, $obj, $options);
}
