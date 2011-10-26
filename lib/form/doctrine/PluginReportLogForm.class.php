<?php

/**
 * Report an item of content. This requires the sfDoctrineGuard version 1.5 and the custom
 * reCAPTCHA class, mySfWidgetFormReCaptcha.
 * 
 * @uses mySfWidgetFormReCaptcha
 */
abstract class PluginReportLogForm extends BaseReportLogForm
{
  public function configure()
  {
    $choices = array(
      ''          => 'Choose...',
      'offensive' => 'Offensive Content', 
      'copyright' => 'Copyright Infringement', 
      'other'     => 'Other'
    );

    $this->getWidget('reporter')->setAttribute('size', 40);
    $this->getWidget('email')->setAttribute('size', 40);
    $this->getWidget('message')->setAttribute('cols', 60)->setAttribute('rows', 3);
  
    $this->setWidget('reason', new sfWidgetFormChoice(array('choices' => $choices)))
      ->setValidator('id', new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)))
      ->setValidator('email', new sfValidatorEmail(array('required'=>true)))
      ->setValidator('reporter', new sfValidatorString(array('required'=>true)))
      ->setValidator('message', new sfValidatorString(array('required' => false)))
      ->setValidator('reason', new sfValidatorChoice(array('choices'=>array_keys($choices))));
      
    $this->getWidgetSchema()->setLabel('reporter', 'Your name');
      
    $this->useFields(array(
      'reporter',
      'email',
      'message',
      'reason'
    ));
    
    if($user = $this->getOption('user', false))
    {
      if(!$user->isAuthenticated())
      {
        $this->addCaptcha();
      }
    }
  }
  
  /**
   * Add reCAPTCHA widget and validator.
   */
  protected function addCaptcha()
  {
    $this->setWidget('captcha', new mySfWidgetFormReCaptcha(array(
      'public_key' => sfConfig::get('app_recaptcha_public'),
      'use_ssl'    => sfContext::getInstance()->getRequest()->isSecure()
    )));
    
    $this->setValidator('captcha', new sfValidatorReCaptcha(array(
      'private_key' => sfConfig::get('app_recaptcha_private')
    )));
    
    $this->getWidgetSchema()->setLabel('captcha', 'Spam Prevention');
  }
}
