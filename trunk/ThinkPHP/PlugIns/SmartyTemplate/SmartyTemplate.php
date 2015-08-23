<?php 
/*
Plugin Name: SmartyTemplate
Template URI: http://smarty.php.net
Description: Smarty模版引擎插件
Author: 流年 修正 云中雾
Version: 1.0
Author URI: http://blog.liu21st.com/
*/

function SmartyTemplate($templateFile,$templateVar,$charset,$varPrefix='')
{
    $templateFile=substr($templateFile,strlen(TMPL_PATH));
        include_once("Smarty.class.php");
        $tpl = new Smarty();
        $tpl->caching = true;
        $tpl->template_dir = TMPL_PATH;
        $tpl->compile_dir = CACHE_PATH ;
        $tpl->cache_dir = TEMP_PATH ;
        $tpl->assign($templateVar);
        $tpl->display($templateFile);
        return ;
}
if('SMARTY'== strtoupper(C('TMPL_ENGINE_TYPE'))) {
    add_compiler('Smarty','SmartyTemplate');
}
?>