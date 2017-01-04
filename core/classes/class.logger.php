<?php

namespace Forge\Core\Classes;

class Logger {
    public static $levels = array("DEBUG", "INFO", "WARN", "ERROR");
    public static $log_level = "DEBUG";

    public static function timer() {
        return microtime(true);
    }

    public static function stop($start) {
        $time_post = microtime(true);
        $exec_time = round(($time_post - $start) * 1000);
        self::debug("Execution Time: ".$exec_time." ms");
    }

    public static function log($text, $level=null) {
        if(is_null($level)) {
            $level = "INFO";
        }
        if(! in_array($level, self::$levels)) {
            self::log("Logged on uknown Level '".$message."'", "WARN");
        }
        $log_level = self::$log_level;
        if(! is_null(LOG_LEVEL)) {
            $log_level = LOG_LEVEL;
        }
        $log_level_key = array_search($log_level, self::$levels);
        $log_output_key = array_search($level, self::$levels);

        if($log_level_key <= $log_output_key) {
          if(! is_array($text))
            $text = array($text);
          foreach($text as $key => $value) {
            if(is_array($value)) {
              $value = implode(", ", $value);
            }
            $output = date("Y-m-d H:i:s")." - ".$level." - ".$key." => ".$value."\n";
            if($level == 'DEBUG') {
                if(!Utils::isAjax()) {
                    echo '<script>console.log("'.$level. " - ".$key." => ".$value.'")</script>';
                }
            }
            $filename = "error-".date("Y-m-d").".log";

            $file = fopen(DOC_ROOT."logs/".$filename, "a+") or die("Unable to open Log File!");
            fwrite($file, $output);
            fclose($file);
          }
        }
    }

    public static function warn($message) {
        self::log($message, "WARN");
    }

    public static function error($message) {
        self::log($message, "ERROR");
    }

    public static function info($message) {
        self::log($message, "INFO");
    }

    public static function debug($message) {
        self::log($message, "DEBUG");
    }
}

?>
