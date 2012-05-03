sfDoctrineModeratablePlugin
===========================

Usage
-----

     actAs:
        Moderatable:
            fields:                 [title, description]
            strip:                  false
            replace:                false
            default_flag:           'unmoderated'
            post_mod:               false
            

And enable the preDQL in the application configuration (usually frontend):

    class [APPLICATION]Configuration extends sfApplicationConfiguration
    {
       ....

       public function configureDoctrine(Doctrine_Manager $manager)
       {
         $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);
       }
    }
    

If you want the field to be an enum (if you're using MySQL etc), then set the following in your databases.yml

      attributes:
        use_native_enum: true


Options
-------

 * `fields` - YAML array of fields to check
 * `strip` - If true then remove all profane strings.
 * `replace` - Replace profane strings with '*****'. If `strip: true` then this has no effect. 
 * `profane_flag` - Default status for items flagged as profane, can have the values 'flagged' or 'rejected' - default is 'rejected'
 * `default_flag` - Default moderation state. It can have the values 'unmoderated', 'safe' or 'followup' - default is 'unmoderated'
  * unmoderated - not yet been moderated
  * safe - no problems here
  * followup - needs investigation
  * flagged - reported by user or profanities detected (if profamne_flag set to `flagged`)
  * rejected - rejected by moderator or profanities detected (if profamne_flag set to `rejected`)
 * `post_mod` - true or false
  * Pre-moderated - means it's displayed only if someone moderates it first in the backend.
  * Post-moderated - means it's displayed once it's added by the user. It can be taken off the website by moderator on the backend app. It also can be flagged in the frontend app by anyone - in this case (usually) it's awaiting moderation, while still visible, but only for 1h (flagged_threshold). If no one moderates the flagged request within this time, the item usually is taken off the website.
 * `flagged_threshold` - number of seconds after which a reported (but as yet, unmoderated) item should be ommitted from results sets - default 1h.


Adding more profanities to check for
-------------------------------------

You may want to add more strings to check for, personal information for example, or maybe just a load more profanities the plugin developers have never heard.

Your extra words should be defined in one or more files, following this format:

    <?php
    // probably going to be a bigger array than this!
    $extra_words = array('word1', 'word2');

*Important* - the plugin looks for the variable `$extra_words` and will throw an exception if you use a different name. 

Define the location of these files in your `app.yml`:

    moderatable_plugin:
      extra_profanities:
        - /full/path/to/file1.php
        - /full/path/to/file2.php

It might be helpful to remember you can parse PHP in the YAML files. For example, specifying a file in the Symfony lib directory:

    moderatable_plugin:
      extra_profanities:
        - <?php printf('%s/%s', sfConfig::get('sf_lib_dir'), 'file1.php') ?>
        - <?php printf('%s/%s', sfConfig::get('sf_lib_dir'), 'file2.php') ?>

If the file is not found, the plugin will fail silently and your extra words will have been ignored (obviously).