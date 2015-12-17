<?php
namespace PipelinesMicroserviceCLI\QuestionValidators;

class Validators
{
    public static function integerValidator( $required = false )
    {
        return function ($answer) use ($required)
        {
            if (!$required && empty($answer)) {
                return $answer;
            } elseif ( !is_numeric($answer) ){
                throw new \RuntimeException("Please provide a integer value");
            } else {
                $int = intval($answer);
                $double = doubleval($answer);
                if ($int != $double) {
                    throw new \RuntimeException("Please provide a integer value");
                }
            }
            
            return $answer;
        };
    }
    
    public static function stringValidator( $required = false )
    {
        return function ($answer) use ($required)
        {
            if (!$required && empty($answer)) {
                return $answer;
            } elseif ( strlen($answer) == 0){
                throw new \RuntimeException("Please provide a value");
            } else {
                return $answer;
            }
        };
    }
    
    public static function doubleValidator( $required = false )
    {
        return function ($answer) use ($required)
        {
            if (!$required && empty($answer)) {
                return $answer;
            } elseif ( !is_numeric($answer) ){
                throw new \RuntimeException("Please provide a double value");
            } else {
                $double = doubleval($answer);
                if ("$double" != $answer) {
                    throw new \RuntimeException("Please provide a double value");
                }
            }
    
            return $answer;
        };
    }
    
    public static function fileValidator( $required = false )
    {
        return function ($answer) use ($required)
        {
            if ($required) {
                $isLink = preg_match("@^https?://.*@", $answer);
                $isPath = preg_match("@^/@", $answer) && strlen($answer) > 4;
        
                if ( !$isLink && !$isPath ) {
                    throw new \RuntimeException("Please provide a valid path to the file.");
                }
            }
    
            return $answer;
        };
    }
    
    public static function filesValidator( $required = false )
    {
        $class = self::class;
        return function ($answer) use ($class, $required)
        {
            if ($required) {
                $files = explode(",", $answer);
                if (count($files) > 1){
                    foreach ($files as $file) {
                        $fileReturned = call_user_func($class::fileValidator(),$answer);
                    }
                } else {
                    throw new \RuntimeException("Please provide a comma separated list of files");
                }
            }
            
            return $answer;
        };
    }
    
}
