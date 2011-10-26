<?php
class sfValidatorProfanity extends sfValidatorString
{
  public function configure($options = array(), $messages = array())
  {
    parent::configure($options,$messages);
    $this->addMessage('profane', 'Keep it clean, please!');
  }
  protected function doClean($value)
  {
    $value = parent::doClean($value);
    if(RudieFilter::check($value))
    {
      throw new sfValidatorError($this,'profane');
    }
    return $value;
  }
}