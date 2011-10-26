h2. Usage

<pre><code>
actAs:
        Moderatable:
            fields:                 [title, sub_title, description]
            strip:                  false
            replace:                true
            flag_profane:           true
            default_flag:           'safe'
</code></pre>

h2. Options

||`fields`||YAML array of fields to check||
||`strip`||If true then remove all profane strings.||
||`replace`||Replace profane strings with given value. if strip: true then this has no effect.[[BR]]~ means use default replacement string ('*****')||
||`flag_profane`||true or false. If a field is profane and this is set to true its status will be set to 'flagged'||
||`default_flag`||Default moderation state.||

`flag` and `default_flag` options can have values: `moderate`, `safe`, `removed`.

h2. Adding more profanities to check for

You may want to add more strings to check for, personal information for example, or maybe just a load more profanities the plugin developers have never heard.

Your extra words should be defined in one or more files, following this format:

<pre><code>
#!php
<?php
// probably going to be a bigger array than this!
$extra_words = array('word1', 'word2');
</code></pre>

*Important* - the plugin looks for the variable `$extra_words` and will throw an exception if you use a different name. 

Define the location of these files in your `app.yml`:

<pre><code>
#!yaml
  moderatable_plugin:
    extra_profanities:
      - /full/path/to/file1.php
      - /full/path/to/file2.php
</code></pre>

It might be helpful to remember you can parse PHP in the YAML files. For example, specifying a file in the Symfony lib directory:

<pre><code>
#!yaml
  moderatable_plugin:
    extra_profanities:
      - <?php printf('%s/%s', sfConfig::get('sf_lib_dir'), 'file1.php') ?>
      - <?php printf('%s/%s', sfConfig::get('sf_lib_dir'), 'file2.php') ?>
</code></pre>

If the file is not found, the plugin will fail silently and your extra words will have been ignored (obviously).