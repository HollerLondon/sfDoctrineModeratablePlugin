<?php
class PluginsReportActions extends sfActions
{
  /**
   * 
   */
  public function executeReportItem(sfWebRequest $request)
  {
    $model = $request->getParameter('model');
    $hash  = $request->getParameter('hash');
    
    // store the referring page in the session if not already present
    if(!$this->getUser()->getAttribute('referring_page',false))
    {
      $this->getUser()->setAttribute('referring_page', $request->getReferer());
    }
    
    // set the referring page used in the template
    $this->referring_page = $this->getUser()->getAttribute('referring_page', '@homepage');
    
    try
    {
      $t = Doctrine::getTable($model);
      
      $object = $t->createQuery('o')
        ->andWhereIn('o.moderation_status',sfConfig::get('app_moderation_safe_statuses',array()))
        ->andWhere('SHA1(CONCAT(?,o.id)) = ?',array($model,$hash))
        ->limit(1)
        ->fetchOne();
      
      if(!$object)
      {
        throw new Exception(
          sprintf("Couldn't find %s that matched %s",$model,$hash)
        );
      }
      
    }
    catch(Exception $e)
    {
      $this->forward404(sprintf($e->getMessage()));
      return;
    }

    // these values are used to construct the route in the report form
    $this->route_name   = $this->getContext()->getRouting()->getCurrentRouteName();
    $this->route_params = array(
      'model'   => $model,
      'hash'    => $hash,
    );

    // We instantiate a new ReportLog and pass in the model and it's ID
    // as parameters to the form
    $this->form = new ReportLogForm(null, array('user' => $this->getUser()));
    
    if($request->isMethod('post'))
    {
      $formdata = $request->getParameter($this->form->getName());

      // only add reCAPTCHA is the user is anonymous
      if(!$this->getUser()->isAuthenticated())
      {
        $formdata['captcha'] = array(
          'recaptcha_challenge_field' => $request->getParameter('recaptcha_challenge_field'),
          'recaptcha_response_field'  => $request->getParameter('recaptcha_response_field'),
        );
      }
        
      $this->form->bind($formdata);
      
      if($this->form->isValid())
      {
        $ReportLog = $this->form->updateObject();
        $ReportLog['reported_object']     = $model;
        $ReportLog['reported_object_id']  = $object->getPrimaryKey();
        
        if($object->isVisible())
        {
          // raise event here so app can act upon reported items if need be 
          // TODO document this in the wiki
          $this->dispatcher->notify(new sfEvent($this, 'moderate.report', array(
            'reported_item' => $object,
            'report_log'    => $ReportLog,
          )));          
          
          $object['moderation_status'] = 'flagged';
          $object->save();
        }
        
        $ReportLog->save();
        
        $this->setTemplate('reported');
        
        // remove the referer so that if this user reports again in the
        // same session, it wil be correctly set because it doesn't exist (see top of this method).
        $this->getUser()->getAttributeHolder()->remove('referring_page');
      }
    }
    
    return sfView::SUCCESS;
  }

  /**
   * Plugin's bundled notification email sender (handles the event)
   */
  static public function notify(sfEvent $e)
  {
    $mailer            = $e->getSubject()->getMailer();
    $reported_item     = $e['reported_item'];
    $report_log        = $e['report_log'];
    $recipients        = sfConfig::get('app_moderation_emails');
    
    $default_from_name = sprintf('moderation-bot@%s', $e->getSubject()->getRequest()->getHost());

    $from = array(
      sfConfig::get('app_moderation_notify_from_email', $default_from_name) => sfConfig::get('app_moderation_notify_from_name', 'Moderation Bot')
    );
    
    // throw some helpful messages, unlike the original sfDoctrineApply plugin!
    if($recipients === false || empty($recipients))
    {
      throw new Exception('You need to define app_moderation_emails emails in your configuration');  
    }
    
    // load the partial helper, as this method will be called statically
    // so $this->getPartial() is out of the question.
    sfProjectConfiguration::getActive()->loadHelpers(array('Partial'));
    
    $mailer->composeAndSend(
      $from,
      $recipients,
      sfConfig::get('app_moderation_notify_subject', 'An item was reported'),
      get_partial('report/email', array(
        'reported_item' => $reported_item,
        'report_log'    => $report_log
      ))
    );    
  }
}