<?php
class ModeratableListener extends Doctrine_Record_Listener
{
    /**
     * If the object's moderation has not been set already then perform
     * moderation, using all plugin options for this model
     *
     * To override this behaviour, create a preInsert method on your
     * Moderatable model that sets the moderation_status so this
     * preInsert ends up doing nothing
     *
     * @param Doctrine_Event $event
     */
    public function preInsert(Doctrine_Event $event)
    {
        $object = $event->getInvoker();
        
        // allows the default behaviour to be overidden. if a moderation_status
        // is already present for an insert then do nothing.
        if(empty($object['moderation_status']))
        {
            $object->doModeration();
        }

        return $object;
    }
    
    /**
     * Tack on the necessary checks for moderation issues to all DQL Selects
     *
     * @param Doctrine_Event $event
     **/
    public function preDqlSelect(Doctrine_Event $event)
    {
        $params = $event->getParams();
        $field  = $params['alias'] . '.' . 'moderation_status';
        $query  = $event->getQuery();
        $obj    = $event->getInvoker();
        
        // We only need to add the restriction if:
        // 1 - We are in the root query
        // 2 - We are in the subquery and it defines the component with that alias
        if ((
          !$query->isSubquery()
          || (
            $query->isSubquery()
            && $query->contains(' ' . $params['alias'] . ' ')
          ))
          && ! $query->contains($field))
        {
          $clause =
            '%a%.moderation_status = "safe"
            OR (%a%.moderation_status = "flagged" AND %a%.updated_at > "%now%")';
          
          if($obj->isPostMod())
          {
            $clause.= ' OR %a%.moderation_status = "unmoderated"';
          }
          
          $clause = strtr($clause,
          array(
            '%a%'   => $params['alias'],
            '%now%' => $obj->flaggedThreshold()
          )
        );
          
          $query->addPendingJoinCondition($params['alias'],$clause);
        }
    }
}