<?php 
/*
Plugin Name: EaseTemplate
Template URI: http://www.systn.com
Description: EaseTemplate模版引擎插件
Author: 云中雾
Version: 1.0
Author URI: http://www.20488.com/
*/

function EaseTemplate($templateFile,$templateVar,$charset,$varPrefix='')
{
    $templateFile = substr($templateFile,strlen(TMPL_PATH),-5);
    $CacheDir = substr(CACHE_PATH,0,-1);
    $TemplateDir = substr(TMPL_PATH,0,-1);
    include "template.php";
	$tpl = new EaseTemplate(
	  array(
		'CacheDir'=>$CacheDir,
		'TemplateDir'=>$TemplateDir,
		'TplType'=>'html'
		 )
    );
    $tpl->set_var($templateVar);
    $tpl->set_file($templateFile);
    $tpl->p();
    //$tpl->inc_list();
     return ;
}
if('EASE'== strtoupper(C('TMPL_ENGINE_TYPE'))) {
    add_compiler('Ease','EaseTemplate');
}
?>