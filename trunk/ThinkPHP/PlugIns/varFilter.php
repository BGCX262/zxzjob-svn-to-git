<?php 
    /*
    Plugin Name: varFilter 
    Plugin URI: http://thinkphp.cn
    Description: 变量安全过滤插件
    Author: 流年
    Version: 1.0
    Author URI: http://blog.liu21st.com/
    */ 
    
/**
 +----------------------------------------------------------
 * 变量过滤 
 +----------------------------------------------------------
 * @param mixed $value 变量
 +----------------------------------------------------------
 * @return mixed
 +----------------------------------------------------------
 */
function var_filter_deep($value) {
    if(is_array($value)) {
        $return = array_map('var_filter_deep', $value);
        return $return;
    }else {
        $value = htmlspecialchars(trim($value),ENT_NOQUOTES);
        $value = str_replace("javascript", "j avascript", $value);
        return $value;
    }
}

/**
 +----------------------------------------------------------
 * 变量安全过滤
 +----------------------------------------------------------
 * @static
 * @access public 
 +----------------------------------------------------------
 * @return string
 +----------------------------------------------------------
 */
function varFilter () 
{
   $_GET     = var_filter_deep($_GET);
   $_POST    = var_filter_deep($_POST);
   $_REQUEST = var_filter_deep($_REQUEST);
}

   add_filter('app_init','varFilter');
?>