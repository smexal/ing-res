<?php

class Utils {
    public static function getUriComponents() {
        preg_match_all("/(.*)(\?.+)/", $_SERVER["REQUEST_URI"], $uri, PREG_PATTERN_ORDER);
        if(count($uri[0]) > 0) {
          $uri = $uri[1][0];
        } else {
          $uri = $_SERVER['REQUEST_URI'];
        }
        $uri = str_replace(WWW_ROOT, "",$uri);
        return explode("/", $uri);
    }

    public static function password($raw) {
        return password_hash($raw, PASSWORD_BCRYPT);
    }
    public static function passwordCheck($password, $hash) {
        return password_verify($password, $hash);
    }

    public static function getCurrentUrl() {
        return self::getUrl(self::getUriComponents());
    }

    public static function getUsername($id) {
      $user = new User($id);
      return $user->get('username');
    }

    public static function getUrl($params = array()) {
        return WWW_ROOT.implode("/", $params);
    }

    public static function checkdatabase($host, $user, $pw, $dbname) {
      $link = mysql_connect($host, $user, $pw);
      if (!$link) {
        die('<h1>Not connected : ' . mysql_error().'</h1>');
      }
      $db_selected = mysql_select_db($dbname, $link);
      if (!$db_selected) {
        die ('<h1>Cannot use '.$dbname.' : ' . mysql_error().'</h1>');
      }
      mysql_close($link);
    }

    public static function getProgressBar($id, $current, $text="") {
      return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "progressbar", array(
          "id" => $id,
          "current" => $current,
          "min" => "0",
          "max" => "100",
          "text" => $text
      ));
    }

    public static function barUpdater($id, $value) {
      return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "barupdater", array(
          "id" => $id,
          "value" => $value
      ));
    }

    public static function screenLog($message) {
      return App::instance()->render(CORE_TEMPLATE_DIR."assets/", "screenlog", array(
          "time" => date("H:i:s"),
          "message" => $message
      ));
    }

    public static function isAjax() {
        if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')
        || array_key_exists("forceAjax", $_GET)) {
            return true;
        }
        return false;
    }

    public static function json($array) {
        return htmlspecialchars(json_encode($array), ENT_QUOTES, 'UTF-8');
    }

    public static function buffer() {
      if(array_key_exists("buffer", $_GET)) {
        if($_GET['buffer'] == 'false') {
          return false;
        }
      }
      return true;
    }

    public static function octetStream() {
      header('Content-type: application/octet-stream');

      // Turn off output buffering
      ini_set('output_buffering', 'off');
      // Turn off PHP output compression
      ini_set('zlib.output_compression', false);
      // Implicitly flush the buffer(s)
      ini_set('implicit_flush', true);
      ob_implicit_flush(true);
      // Clear, and turn off output buffering
      while (ob_get_level() > 0) {
          // Get the curent level
          $level = ob_get_level();
          // End the buffering
          ob_end_clean();
          // If the current level has not changed, abort
          if (ob_get_level() == $level) break;
      }
      // Disable apache output buffering/compression
      if (function_exists('apache_setenv')) {
          apache_setenv('no-gzip', '1');
          apache_setenv('dont-vary', '1');
      }
    }

    public static function tableCell($content, $class=false, $id=false, $structure=false) {
      $data = array(
          'content' => $content,
          'class' => $class,
          'id' => $id
      );
      if($structure) {
        return App::instance()->render(CORE_TEMPLATE_DIR."/assets/", "table.cell", $data);
      }
      return $data;
    }

    public static function icon($name) {
      return '<span class="glyphicon glyphicon-'.$name.'" aria-hidden="true"></span>';
    }

    public static function error($error) {
      return '<div class="bs-callout bs-callout-danger"><p>'.$error.'</p></div>';

    }
}


?>