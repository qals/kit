<?php
namespace Als;

class Tools 
{
    static function uuid($data = null) 
    {
        $data = $data ?? openssl_random_pseudo_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    static function readableBytes($bytes) {
        $i = floor(log($bytes) / log(1024));
        $sizes = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    
        return sprintf('%.02F', $bytes / pow(1024, $i)) * 1 . ' ' . $sizes[$i];
    }
    
    static function msg($msg = 'empty', $return = true, $back = false)
    {
    	$time = time();
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $file = $trace[0]['file'];
        $line = $trace[0]['line'];

        $tmp = "[$time] $file ";
        if(isset($trace[1]))
        {
            $func = $trace[1]['function'];
            $class = $trace[1]['class'];
            $tmp .= "$class->$func ";
        }
        $tmp .= "($line): ";

        if(is_string($msg))
            $msg = $tmp . $msg;
        else
            $msg = $tmp . json_encode($msg);

        if($back)
            return $msg;
        else
            echo $msg."\n";

        return $return;
    }

    static function setToken($file , $data) 
    {
        if (is_dir(dirname($file)))
            file_put_contents($file,  $data);
        else
            return false;
    }

    static function getToken($file) 
    {
        if (is_file($file))
            return file_get_contents($file);
        else
            return false;
    }

    private static function handleJsonError($errno) 
    {
        return false;
        $messages = array(
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'Invalid or malformed JSON',
            JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
            JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters' //PHP >= 5.3.3
        );
        throw new \DomainException(
            isset($messages[$errno])
            ? $messages[$errno]
            : 'Unknown JSON error: ' . $errno
        );
    }

    static function jsonEn($input)
    {
        $json = json_encode($input);
        if ($errno = json_last_error()) 
        {
            self::handleJsonError($errno);
        } 
        elseif ($json === 'null' && $input !== null)
        {
            throw new \DomainException('Null result with non-null input');
        }
        return $json;
    }

    static function jsonDe($input, $arr = true)
    {
        if (version_compare(PHP_VERSION, '5.4.0', '>=') && !(defined('JSON_C_VERSION') && PHP_INT_SIZE > 4)) {
            $obj = json_decode($input, $arr, 512, JSON_BIGINT_AS_STRING);
        } 
        else 
        {
            $max_int_length = strlen((string) PHP_INT_MAX) - 1;
            $json_without_bigints = preg_replace('/:\s*(-?\d{'.$max_int_length.',})/', ': "$1"', $input);
            $obj = json_decode($json_without_bigints, $arr);
        }

        if ($errno = json_last_error()) 
        {
            self::handleJsonError($errno);
        } 
        elseif ($obj === null && $input !== 'null') 
        {
            throw new \DomainException('Null result with non-null input');
        }
        return $obj;
    }

    static function urlsafeB64Encode($input) 
    {
        return str_replace(array('+','/', '='),array('-','_', ''), base64_encode($input));
    }

    static function urlsafeB64Decode($input) 
    {
        $base_64 = str_replace(array('-','_'),array('+','/'), $input);
        return base64_decode($base_64);
    }
}
