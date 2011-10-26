<?php
/**
 * Here to help us debug by 'catching' warning and errors from preg_match()
 * 
 * @param $errno
 * @param $errstr
 * @param $errfile
 * @param $errline
 * @param $errcontext
 */
function handle_error($errno, $errstr, $errfile, $errline, array $errcontext)
{
    // error was suppressed with the @-operator
    if(0 === error_reporting())
    {
        return false;
    }

    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

/**
 * Profanity filter class. Can check for, strip or replace profane words from a given
 * chunk of text.
 * 
 * @author John Grimsey
 */
class RudieFilter
{
    private static $profanities;
    
    /**
     * Check haystack for presence of profane words.
     * 
     * @param string  $haystack
     * @param boolean $words_only
     */
    static public function check($haystack = false, $words_only = true)
    {
        set_error_handler('handle_error');
        
        $haystack = utf8_encode($haystack);
        $method   = ($words_only) ? 'hasWord' : 'hasString';
        $result   = self::$method($haystack);
        
        restore_error_handler();
        
        return $result;
    }
    
    /**
     * Check for presence of profane words in haystack.
     * 
     * @param string $haystack
     * @return boolean
     */
    static public function hasWord($haystack)
    {
        foreach(self::getProfanities() as $needle)
        {
            // use | to delimit so we don't need to escape forward slashes or percent signs
            $expr = sprintf('|\b(%s)\b|i', preg_quote($needle));
                
            // we'll look for an exception, helps us debug special characters
            // in our profanity list which might cause problems.
            try
            {
                if(preg_match($expr, $haystack))
                {
                    return true;
                }
            }
            catch(ErrorException $e)
            {
                printf("\nError parsing regex \"%s\"", $expr);                 
            }
        }
    }
    
    /**
     * Simply perform case-insensitive check for presence of a profane string in the haystack.
     * 
     * @param string $haystack
     * @return boolean
     */
    static public function hasString($haystack)
    {
        foreach(self::getProfanities() as $needle)
        {
            if(stripos($haystack, $needle))
            {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * return @array Array of profanities
     */
    public static function getProfanities()
    {
        if(!self::$profanities)
        {
            include('profanities.php');
            self::$profanities = $rwords;
            
            unset($rwords);
        }
        
        return self::$profanities; 
    }
    
    /**
     * Replace profane strings with placeholder characters, optionally matching length of
     * the offending string. Optionally search and replace within strings.
     * 
     * @param $haystack
     * @param $words_only
     */
    static function replace($haystack, $replacement = '*****', $match_length = true, $words_only = true)
    {
        $haystack = utf8_encode($haystack);
        
        if($words_only)
        {
            foreach(self::getProfanities() as $needle)
            {
                $replacement = ($match_length) ? str_repeat('*', strlen($needle)) : $replacement; 
                $expression  = sprintf('|\b(%s)\b|i', $needle);
                $haystack    = preg_replace($expression, $replacement, $haystack);
            }
        }
        
        else
        {
            foreach(self::getProfanities() as $needle)
            {
                $replacement = ($match_length) ? str_repeat('*', strlen($needle)) : $replacement; 
                $haystack    = str_ireplace($needle, $replacement, $haystack);   
            }
        }
        
        return $haystack;
    }
    
    /**
     * Remove profane strings.
     * 
     * @todo  Modify so that a profane's leading or trailing space is also trimmed.
     * @param string  $haystack
     * @param boolean $words_only
     */
    static public function strip($haystack, $words_only = true)
    {
        if($words_only)
        {
            foreach(self::getProfanities() as $needle)
            {
                $expression = sprintf('|\s?\b%s\b|', $needle);
                $haystack   = preg_replace($expression, '', $haystack);
            }
        }
        
        else
        {
            foreach(self::getProfanities() as $needle)
            {
                $haystack = str_ireplace($needle, '', $haystack);
            }
        }
        
        return $haystack;
    }
}
