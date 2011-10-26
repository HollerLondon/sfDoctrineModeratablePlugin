<?php
class sfDoctrineModeratableAdminActions extends sfActions
{
  /**
   * Batch update a bunch of objects' moderation status.
   * 
   * @todo Wrap all SQL into a transaction
   */
  public function executeModerate(sfWebRequest $request)
  {
    // we need these in log() below
    $this->ids        = $request->getParameter('ids');
    $this->class_name = $request->getParameter('class'); 

    // group ids in array by status
    foreach($this->ids as $id => $moderation_status)
    {
      $update_data[$moderation_status][] = $id;
    }
    
    // start transaction 
    $conn = Doctrine_Manager::getInstance()->getCurrentConnection();
    $conn->beginTransaction();

    // shall we log this activity? needs to happen
    // before the updates below, otherwise feck all will be logged. 
    if(sfConfig::get('app_moderation_audit', false))
    {
      $log_query = $this->log($update_data);
    }
    
    // now perform updates on each set of ids
    foreach($update_data as $status => $ids_array)
    {
      $query = Doctrine_Query::create()
        ->update($this->class_name)
        ->set('moderation_status', '?', $status)
        ->set('updated_at', 'NOW()')
        ->whereIn('id', $ids_array)
        ->andWhere('moderation_status != ? OR moderation_status IS NULL', $status);

      $query->execute();
    }
    
    // now execute everything or nothing, ensuring that logged
    // moderation activity actually took place (as the log query
    // has to be executed first)
    $conn->commit();
    
    // now raise an event for apps to hook into    
    $this->dispatcher->notify(new sfEvent(
      $this,
      'moderate.batch',
      array(
        'changeset'  => $this->ids,
        'class_name' => $this->class_name
      )
    ));
    
    $this->redirect($request->getReferer());
  } 

  /**
   * @param array $update_array - looks like: array('safe' => array(1,2,3), 'flagged' => array(5,6,7)) etc
   */  
  protected function log($update_data)
  {
    $collection = new Doctrine_Collection('ModerationLog');
    
    // fetch current moderation statuses
    $current = Doctrine::getTable($this->class_name)->createQuery('c')
      ->select('c.id, c.moderation_status')
      ->whereIn('c.id', array_keys($this->ids))
      ->execute(null, Doctrine::HYDRATE_SCALAR);
      
    foreach($current as $row)
    {
      $old_status = $row['c_moderation_status'];
      $new_status = $this->ids[$row['c_id']];

      // if the moderation state has changed
      if($old_status != $new_status)
      {
        $m = new ModerationLog();
        $m->fromArray(array(
          'object_type' => $this->class_name,
          'object_id'   => $row['c_id'],
          'old_status'  => $old_status,
          'new_status'  => $new_status,
          'updated_by'  => $this->getUser()->getGuardUser()->getId(),
        ));
        
        $collection[] = $m;
      }
    }
    
    $collection->save();
  }
}
