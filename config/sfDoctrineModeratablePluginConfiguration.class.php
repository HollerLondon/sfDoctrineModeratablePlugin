<?php
class sfDoctrineModeratablePluginConfiguration extends sfPluginConfiguration
{
  /**
   * @see sfPluginConfiguration
   */
  public function initialize()
  {
    $send_emails    = sfConfig::get('app_moderation_notify_sendemail', true);
    $enabledModules = sfConfig::get('sf_enabled_modules', array());
    
    if(in_array('report', $enabledModules) && $send_emails)
    {
      $this->dispatcher->connect('moderate.report', array('reportActions', 'notify'));
    }
  }
}
