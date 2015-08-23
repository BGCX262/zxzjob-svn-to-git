<?php 
/*
Plugin Name: TemplateLite
Template URI: http://templatelite.sourceforge.net
Description: TemplateLite模版引擎插件
Author: 云中雾
Version: 1.0
Author URI: http://www.20488.com/
*/

function TemplateLite($templateFile,$templateVar,$charset,$varPrefix='')
{
    $templateFile=substr($templateFile,strlen(TMPL_PATH));
    include_once("class.template.php");
        $tpl = new Template_Lite();
        $tpl->template_dir = TMPL_PATH;
        $tpl->compile_dir = CACHE_PATH ;
        $tpl->cache_dir = TEMP_PATH ;
        $tpl->assign($templateVar);
        $tpl->display($templateFile);
        return ;
}
if('LITE'== strtoupper(C('TMPL_ENGINE_TYPE'))) {
    add_compiler('Templite','TemplateLite');
}
?>