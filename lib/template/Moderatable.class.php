<?php
/**
 * Profanity checker and moderation status plugin.
 * 
 * OPTIONS:
 * ========
 * 
 * strip:        If true then remove all profane strings.
 * replace:      Replace profane strings with given value. if strip: true then this has no effect.
 *               ~ means use default replacement string ('*****')
 * profane_flag: If a field is profane, set the object's moderation_status column to this value.
 * default_flag: Default moderation state.
 *             
 * 'flag' and 'default_flag' options can have values: 'unmoderated', 'safe', 'followup', 'flagged' or 'rejected'
 * 
 * safe     - no problems here
 * followup - needs investigation (use this as default state for pre-moderation)
 * flagged  - reported by user or profanities detected 
 * flagged_threshold - number of seconds after which a reported (but as yet, unmoderated) item should be 
 *                     ommitted from results sets. 
 * 
 * EXAMPLE:
 * ========
 *  
 * actAs:
 *     Moderatable:
 *          fields:                 [title, sub_title, description]
 *          strip:                  true
 *          replace:                ~
 *          flag:                   'flagged'
 *          default_flag:           'followup'
 *          flagged_threshold:      3600
 *          
 * @author John Grimsey
 * @author Ben Lancaster -- Why did I get demoted to second place?
 **/
class Moderatable extends Doctrine_Template
{
    private $supported_actions = array('strip', 'replace', 'flag'); 

    public function flaggedThresholdTableProxy($format = 'Y-m-d H:i:00')
    {
      return $this->flaggedThreshold($format);
    }
    
    public function flaggedThreshold($format = 'Y-m-d H:i:00')
    {
      $threshold = strtotime(
        sprintf('-%u seconds',$this->getOption('flagged_threshold',600))
      );
      if($format == 'U')
      {
        return (int) $threshold;
      }
      return date($format,$threshold);
    }
    
    /**
     * This is here to be overloaded by the model should you wish to make
     * the default moderation flag contextual based on custom criteria.
     * By default, it uses the default flag as specified in schema.yml
     *
     * @return string
     * @author Ben Lancaster
     **/ 
    public function getDefaultFlag()
    {
      return $this->getOption('default_flag','unmoderated');
    }
    
    public function isPostMod()
    {
      return $this->getOption('post_mod',true);
    }
    
    public function isPreMod()
    {
      return !$this->isPostMod();
    }
    
    /**
     * Add a moderation_status column to model.
     * 
     * NOTE: Set use_native_enum if DBMS supports it
     */
    public function setTableDefinition()
    {
        $this->hasColumn('moderation_status', 'enum', null, array(
             'type'   => 'enum',
             'values' => 
             array(
              'unmoderated'   => 'unmoderated',
              'safe'          => 'safe',
              'followup'      => 'followup',
              'flagged'       => 'flagged',
              'rejected'      => 'rejected',
             ),
             'length' => 15
             ));
        
        $this->index('moderation_idx',
          array(
            'fields' => array(
              'moderation_status',
              'updated_at'
            )
          )
        );

        $this->addListener(new ModeratableListener());
    }
    
    /**
     * Perform check on each field specified in options, and modify 
     * values where necessary according to options.
     */
    public function doModeration()
    {
        $object     = $this->getInvoker();
        $is_profane = false;
        
        // if no fields to check have been specified in the schema.yml
        // then moan.
        $fields = (array) $this->getOption('fields', array());

        if(empty($fields))
        {
            throw new Exception('"fields" option cannot be empty');
        }

        // moderate each of the specified fields
        foreach($fields as $field_name)
        {
            if($object->moderateField($field_name))
            {
                $is_profane = true;
            }
        }

        // store profane status on object
        $object->mapValue('_is_profane', $is_profane);
        
        // set default moderation status?
        if($d = $object->getDefaultFlag())
        {
            $object['moderation_status'] = $d;
        }
        
        // set moderation status if profane?
        if($this->getOption('profane_flag', true) && $is_profane)
        {
          $object['moderation_status'] = $this->getOption('profane_flag','rejected');
        }
    }
    
    /**
     * Check and alter value of field if profane (if options set to replace/strip).
     * 
     * @param string $field_name Name of field to check and alter if necessary
     * @return boolean true if specified field's value was found to be profane
     */
    public function moderateField($field_name)
    {
        $object = $this->getInvoker();
        
        // check whether field is profane or not, return if not
        if(!RudieFilter::check($object[$field_name]))
        {
            return false;
        }
        
        // strip profanities
        if($this->getOption('strip', false))
        {
            $object[$field_name] = RudieFilter::strip($object[$field_name]);
        }
        
        // or, replace profanities
        elseif($r = $this->getOption('replace', false))
        {
            // if user has specified 'replace' option as 'true', then we'll give them a nice default :D
            $r = ($r === true) ? '*****' : $r;
            
            $object[$field_name] = RudieFilter::replace($object[$field_name], $r);
        }
        
        // field was fucking profane
        return true;
    }
    
    /**
     * Is the invoker object seen as profane. Use this method to perform
     * checks and get result. 
     * 
     * If this method is called on an unsaved method, the checks will be performed
     * first.
     * 
     * @return boolean
     */
    public function isProfane()
    {
        // if object has not been tested then perform checks
        if(!$this->getInvoker()->hasMappedValue('_is_profane'))
        {
            $this->doModeration();
        }
        
        return $this->getInvoker()->_is_profane;
    }
    
    /**
     * Has object been set as flagged within the time threshold within which flagged
     * objects will be displayed (and that outside of which, flagged objects are hidden)
     * 
     * @return boolean
     */
    public function isRecentlyFlagged()
    {
      $obj = $this->getInvoker();
      return ($obj->moderation_status == 'flagged' && strtotime($obj['created_at']) > $obj->flaggedThreshold('U'));
    }
    
    /**
     * Is the object visible?
     * 
     * @return boolean
     */
    public function isVisible()
    {
      $obj = $this->getInvoker();
      
      $hidden_statuses  = array('followup', 'rejected');
      
      // pre-moderation means hide unmoderated stuff
      if($obj->isPreMod())
      {
        $hidden_statuses[] = 'unmoderated';
      }

      return $obj->isRecentlyFlagged() || !in_array($obj->moderation_status, $hidden_statuses);
    }
}
