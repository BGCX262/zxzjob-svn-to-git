<?php 
// +----------------------------------------------------------------------+
// | ThinkPHP                                                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2006~2007 http://thinkphp.cn All rights reserved.      |
// +----------------------------------------------------------------------+
// | Licensed under the Apache License, Version 2.0 (the 'License');      |
// | you may not use this file except in compliance with the License.     |
// | You may obtain a copy of the License at                              |
// | http://www.apache.org/licenses/LICENSE-2.0                           |
// | Unless required by applicable law or agreed to in writing, software  |
// | distributed under the License is distributed on an 'AS IS' BASIS,    |
// | WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or      |
// | implied. See the License for the specific language governing         |
// | permissions and limitations under the License.                       |
// +----------------------------------------------------------------------+
// | Author: liu21st <liu21st@gmail.com>                                  |
// +----------------------------------------------------------------------+
// $Id$

/**
 +------------------------------------------------------------------------------
 * EXT标签库解析类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Template
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
import('Think.Template.TagLib');
class TagLibExt extends TagLib
{//类定义开始

    /**
     +----------------------------------------------------------
     * 架构函数
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param object $template  当前模板对象
     +----------------------------------------------------------
     */
    function __construct( &$template)
    {
        $this->tpl = $template;
		parent::__construct('ext');
    }

    /**
     +----------------------------------------------------------
     * init标签解析 初始化ExtJs
     * 包括加载核心类
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    function _init($attr) 
    {
        $tag        = $this->parseXmlAttr($attr,'init');
        $extPath   = !empty($tag['extPath'])?$tag['extPath']:WEB_PUBLIC_URL.'/Js/Ext';

		if(empty($tag['loadJs'])) {
			$parseStr = "<script language='JavaScript' src='".$extPath.'/adapter/yui/yui-utilities.js'."'></script> ";
			$parseStr .= "<script language='JavaScript' src='".$extPath.'/adapter/yui/ext-yui-adapter.js'."'></script> ";
			$parseStr .= "<script language='JavaScript' src='".$extPath.'/ext-all.js'."'></script> ";
		}else{
			$loadJs	=	explode(',',$tag['loadJs']);
			foreach ($loadJs as $js){
				$parseStr .= "<script language='JavaScript' src='".$extPath.'/'.str_replace('.','/',$js).'.js'."'></script> ";
			}
		}

		if(empty($tag['loadCss'])) {
			$parseStr .= "<link rel='stylesheet' type='text/css' href='".$extPath.'/resources/css/ext-all.css'."'>";
		}else{
			$loadCss = explode(',',$tag['loadCss']);
			foreach ($loadCss as $css){
				$parseStr .= "<link rel='stylesheet' type='text/css' href='".$extPath.'/'.str_replace('.','/',$css).'.css'."'>";
			}
		}

        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * dialog标签解析 
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
	function _dialog() {

	}

	function _confirm($attr) {
        $tag        = $this->parseXmlAttr($attr,'confirm');
		$event	=	$tag['event'];
		$id	=	$tag['id'];
		$text	=	$tag['text'];
		$callback	=	$tag['callback'];
		$parseStr	=	"<script language='JavaScript'>Ext.get(".$id.").on('".$event."', function(e){Ext.MessageBox.confirm('Confirm', '".$text."',".$callback.");});</script>";
		return $parseStr;
	}

	function _prompt($attr) {
        $tag        = $this->parseXmlAttr($attr,'prompt');
		$title	=	$tag['title'];
		$msg	 =	 $tag['msg'];
		$width	=	$tag['width'];
		$id	=	$tag['id'];
		$buttons	=	$tag['buttons'];
		$event	=	$tag['event'];
		$multiline	=	$tag['multiline'];
		$innnerScript	=	!empty($tag['innerscript'])?$tag['innerscript']:0;
		$parseStr .= "Ext.Msg.prompt('".$title."', '".$msg."',  ".$fn.",  '".$scrope."', ".$multiline.");";

		return $this->dealScript($parseStr,$innnerScript);
	}
	function _alert($attr) {
        $tag        = $this->parseXmlAttr($attr,'alert');
		$title	=	$tag['title'];
		$msg	=	$tag['msg'];
		$script = $tag['script'];
		if($script == 'false') {
			$parseStr	=	"Ext.Msg.alert('".$title."', '".$msg."');";
		}else{
			$parseStr	=	"<script language='JavaScript'>Ext.Msg.alert('".$title."', '".$msg."');</script>";
		}

		return $parseStr;
	}

	function _wait($attr) {
        $tag        = $this->parseXmlAttr($attr,'wait');
		$title	=	$tag['title'];
		$msg	=	$tag['msg'];
		$innnerScript	=	!empty($tag['innerscript'])?$tag['innerscript']:0;
		$parseStr	=	"Ext.Msg.alert('".$title."', '".$msg."');";

		return $this->dealScript($parseStr,$innerScript);
	}

	function _progress($attr) {
        $tag        = $this->parseXmlAttr($attr,'progress');
		$title	=	$tag['title'];
		$msg	=	$tag['msg'];
		$parseStr	=	"<script language='JavaScript'>Ext.Msg.progress(".$title.", '".$msg."');</script>";
		return $parseStr;
	}

	function _show($attr) {
        $tag        = $this->parseXmlAttr($attr,'show');
		$title	=	$tag['title'];
		$msg	 =	 $tag['msg'];
		$width	=	$tag['width'];
		$id	=	$tag['id'];
		$buttons	=	$tag['buttons'];
		$event	=	$tag['event'];
		$multiline	=	$tag['multiline'];
		$innnerScript	=	!empty($tag['innerscript'])?$tag['innerscript']:0;
		$parseStr .= "Ext.Msg.show({ title: '".$title."', msg: '".$msg."', width: ".$width.", buttons: ".$buttons.", multiline: ".$multiline.", fn: ".$fn.", animEl: '".$animEL."'});";

		return $this->dealScript($parseStr,$innnerScript);

	}

	function dealScript($string,$innnerScript=false) {
		if(!$innnerScript) {
			$string	= "<script language='JavaScript'>".$string."</script>";
		}
		return $string;
	}
}//类定义结束
?>