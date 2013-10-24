<?php
class Log {
  public static $LOG_FILE = 'log.txt';
  private static $enabled = false;

  private static $OUTPUT_WIDTH = 80;

  private static $DEBUG = 1;
  private static $ERROR = 2;

  public static function setEnabled($enabled) {
    self::$enabled = $enabled;
  }

  public static function d($tag, $msg) {
    self::writeMessage(self::$DEBUG, $tag, $msg);
  }

  public static function e($tag, $msg) {
    self::writeMessage(self::$ERROR, $tag, $msg);
  }

  private static function writeMessage($level, $tag, $msg) {
    if (self::$enabled) {
      $output = date('d-m-Y H:i:s') . ' | ';

      switch ($level) {
        case self::$DEBUG:
          $output .= 'DEBUG | ';
          break;
        case self::$ERROR:
          $output .= 'ERROR | ';
          break;
        default:

      }

      $output .= $tag . ' ';

      $output .= str_repeat('=', self::$OUTPUT_WIDTH - strlen($output)) . "\n";

      $output .= wordwrap($msg, self::$OUTPUT_WIDTH) . "\n";

      $output .= str_repeat('=', self::$OUTPUT_WIDTH) . "\n\n";

      echo $output;

      file_put_contents(self::$LOG_FILE, $output, FILE_APPEND);
    }
  }
}

?>