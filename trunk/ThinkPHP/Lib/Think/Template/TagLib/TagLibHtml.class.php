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
 * HTML标签库解析类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Template
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
import('Think.Template.TagLib');
class TagLibHtml extends TagLib
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
    public function __construct( &$template)
    {
        $this->tpl = $template;
		parent::__construct('html');
    }

    /**
     +----------------------------------------------------------
     * editor标签解析 插入可视化编辑器
     * 格式： <html:editor id="editor" name="remark" type="FCKeditor" content="{$vo.remark}" />
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    public function _editor($attr) 
    {
        $tag        =	$this->parseXmlAttr($attr,'editor');
        $id			=	!empty($tag['id'])?$tag['id']: '_editor';       
		$name   	=	$tag['name'];   
        $style   	    =	$tag['style'];   
        $width		=	!empty($tag['width'])?$tag['width']: '100%';    
        $height     =	!empty($tag['height'])?$tag['height'] :'320px';    
        $content    =   $tag['content'];
        $type       =   $tag['type'] ;
		switch(strtoupper($type)) {
            case 'FCKEDITOR':
            	$parseStr   =	'<!-- 编辑器调用开始 --><script type="text/javascript" src="__ROOT__/Public/Js/FCKeditor/fckeditor.js"></script><textarea id="'.$id.'" name="'.$name.'">'.$content.'</textarea><script type="text/javascript"> var oFCKeditor = new FCKeditor( "'.$id.'","'.$width.'","'.$height.'" ) ; oFCKeditor.BasePath = "__ROOT__/Public/Js/FCKeditor/" ; oFCKeditor.ReplaceTextarea() ;function resetEditor(){setContents("'.$id.'",document.getElementById("'.$id.'").value)}; function saveEditor(){document.getElementById("'.$id.'").value = getContents("'.$id.'");} </script> <!-- 编辑器调用结束 -->';
            	break;
			case 'EWEBEDITOR':
				$parseStr	=	"<!-- 编辑器调用开始 --><script type='text/javascript' src='__ROOT__/Public/Js/eWebEditor/js/edit.js'></script><input type='hidden'  id='{$id}' name='{$name}'  value='{$conent}'><iframe src='__ROOT__/Public/Js/eWebEditor/ewebeditor.htm?id={$name}' frameborder=0 scrolling=no width='{$width}' HEIGHT='{$height}'></iframe><script type='text/javascript'>function saveEditor(){document.getElementById('{$id}').value = getHTML();} </SCRIPT><!-- 编辑器调用结束 -->";
				break;
			case 'NETEASE':
                $parseStr   =	'<!-- 编辑器调用开始 --><textarea id="'.$id.'" name="'.$name.'" style="display:none">'.$content.'</textarea><iframe ID="Editor" name="Editor" src="__ROOT__/Public/Js/HtmlEditor/index.html?ID='.$name.'" frameBorder="0" marginHeight="0" marginWidth="0" scrolling="No" style="height:'.$height.';width:'.$width.'"></iframe><!-- 编辑器调用结束 -->';
                break;
            case 'SMART':
            	$parseStr  =  '<div class="smartEditor" style="'.$style.'"><script type="text/javascript" src="__ROOT__/Public/Js/smartEditor/smartEditor.js"></script><div id="tools" ><SELECT NAME="fontname" style="width:65px" onchange="setFont(options[this.selectedIndex].value)"><option value="">字体</option><option value="Arial">Arial</option><option value="Verdana">Verdana</option><option value="Tahoma">Tahoma</option><option value="System">System</option><option value="黑体">黑体</option><option value="宋体">宋体</option></SELECT> <select onchange=setColor(options[this.selectedIndex].value) style="width:35px" name="color"><OPTION value="" selected>颜色</OPTION><OPTION style="background: skyblue;" value=skyblue></OPTION> <OPTION style="background: royalblue" value=royalblue></OPTION> <OPTION style="background: blue" value=blue></OPTION> <OPTION style="background: darkblue" value=darkblue></OPTION> <OPTION style="background: orange" value=orange></OPTION> <OPTION style="background: orangered" value=orangered></OPTION> <OPTION style="background: crimson" value=crimson></OPTION> <OPTION style="background: red" value=red></OPTION> <OPTION style="background: firebrick" value=firebrick></OPTION> <OPTION style="background: darkred" value=darkred></OPTION> <OPTION style="background: green" value=green></OPTION> <OPTION style="background: limegreen" value=limegreen></OPTION> <OPTION style="background: seagreen" value=seagreen></OPTION> <OPTION style="background: deeppink" value=deeppink></OPTION> <OPTION style="background: tomato" value=tomato></OPTION> <OPTION style="background: coral" value=coral></OPTION> <OPTION style="background: purple" value=purple></OPTION> <OPTION style="background: indigo" value=indigo></OPTION> <OPTION style="background: burlywood" value=burlywood></OPTION> <OPTION style="background: sandybrown" value=sandybrown></OPTION> <OPTION style="background: sienna" value=sienna></OPTION> <OPTION style="background: chocolate" value=chocolate></OPTION> <OPTION style="background: teal" value=teal></OPTION> <OPTION style="background: silver" value=silver></OPTION></select><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/bold.gif"  onclick="format(\'bold\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="斜体"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/italic.gif" onclick="format(\'italic\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="粗体"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/underline.gif"  onclick="format(\'underline\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="下划线">	<IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/strikethrough.gif"  onclick="format(\'strikethrough\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="下划线"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/separator.gif"   WIDTH="2" HEIGHT="20" BORDER="0" ALT=""><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/justifyleft.gif" onclick="format(\'justifyleft\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="左对齐"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/justifycenter.gif" onclick="format(\'justifycenter\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="中对齐"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/justifyright.gif" onclick="format(\'justifyright\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="右对齐"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/justifyfull.gif" onclick="format(\'justifyfull\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="两端对齐"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/separator.gif"   WIDTH="2" HEIGHT="20" BORDER="0" ALT=""><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/numlist.gif" onclick="format(\'Insertorderedlist\')"  WIDTH="20" HEIGHT="20" BORDER="0" ALT="数字编号"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/bullist.gif" onclick="format(\'Insertunorderedlist\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="项目编号"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/separator.gif"   WIDTH="2" HEIGHT="20" BORDER="0" ALT=""><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/undo.gif" onclick="format(\'Undo\')"  WIDTH="20" HEIGHT="20" BORDER="0" ALT="撤销"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/redo.gif" onclick="format(\'Redo\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="重做"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/separator.gif"   WIDTH="2" HEIGHT="20" BORDER="0" ALT=""><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/indent.gif" onclick="format(\'Indent\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="增加缩进"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/outdent.gif" onclick="format(\'Outdent\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="减少缩进"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/separator.gif"   WIDTH="2" HEIGHT="20" BORDER="0" ALT=""><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/link.gif" onclick="createLink()" WIDTH="20" HEIGHT="20" BORDER="0" ALT="添加链接"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/unlink.gif" onclick="format(\'Unlink\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="取消链接"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/image.gif" onclick="selectImage(\''.__APP__.'/Attach/select/module/'.MODULE_NAME.'\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="添加图片"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/separator.gif"   WIDTH="2" HEIGHT="20" BORDER="0" ALT=""><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/cut.gif" onclick="format(\'Cut\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="剪切"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/copy.gif" onclick="format(\'Copy\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="拷贝"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/paste.gif" onclick="format(\'Paste\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="粘贴"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/removeformat.gif" onclick="format(\'RemoveFormat\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="清除格式"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/print.gif" onclick="format(\'Print\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="打印"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/code.gif" onclick="setMode()" WIDTH="20" HEIGHT="20" BORDER="0" ALT="查看源码"></div><textarea name="'.$name.'" id="sourceEditor" style="border:none;display:none" >'.$content.'</textarea><div style="'.$style.'" contentEditable="true" id="'.$id.'" >'.$content.'</div></div><SCRIPT LANGUAGE="JavaScript">function saveEditor(){document.getElementById("'.$name.'").value = document.getElementById("'.$id.'").innerHTML;} document.getElementById("'.$id.'").onblur=saveEditor;</SCRIPT>';
            	break;
            case 'MINI':
            	$parseStr  =  '<div class="smartEditor" style="'.$style.'"><script type="text/javascript" src="__ROOT__/Public/Js/smartEditor/smartEditor.js"></script><div id="tools" ><select onchange=setColor(options[this.selectedIndex].value) style="width:35px" name="color"><OPTION value="" selected>颜色</OPTION><OPTION style="background: skyblue;" value=skyblue></OPTION> <OPTION style="background: royalblue" value=royalblue></OPTION> <OPTION style="background: blue" value=blue></OPTION> <OPTION style="background: darkblue" value=darkblue></OPTION> <OPTION style="background: orange" value=orange></OPTION> <OPTION style="background: orangered" value=orangered></OPTION> <OPTION style="background: crimson" value=crimson></OPTION> <OPTION style="background: red" value=red></OPTION> <OPTION style="background: firebrick" value=firebrick></OPTION> <OPTION style="background: darkred" value=darkred></OPTION> <OPTION style="background: green" value=green></OPTION> <OPTION style="background: limegreen" value=limegreen></OPTION> <OPTION style="background: seagreen" value=seagreen></OPTION> <OPTION style="background: deeppink" value=deeppink></OPTION> <OPTION style="background: tomato" value=tomato></OPTION> <OPTION style="background: coral" value=coral></OPTION> <OPTION style="background: purple" value=purple></OPTION> <OPTION style="background: indigo" value=indigo></OPTION> <OPTION style="background: burlywood" value=burlywood></OPTION> <OPTION style="background: sandybrown" value=sandybrown></OPTION> <OPTION style="background: sienna" value=sienna></OPTION> <OPTION style="background: chocolate" value=chocolate></OPTION> <OPTION style="background: teal" value=teal></OPTION> <OPTION style="background: silver" value=silver></OPTION></select><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/bold.gif"  onclick="format(\'bold\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="斜体"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/italic.gif" onclick="format(\'italic\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="粗体"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/underline.gif"  onclick="format(\'underline\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="下划线">	<IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/strikethrough.gif"  onclick="format(\'strikethrough\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="下划线"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/separator.gif"   WIDTH="2" HEIGHT="20" BORDER="0" ALT=""><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/numlist.gif" onclick="format(\'Insertorderedlist\')"  WIDTH="20" HEIGHT="20" BORDER="0" ALT="数字编号"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/bullist.gif" onclick="format(\'Insertunorderedlist\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="项目编号"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/separator.gif"   WIDTH="2" HEIGHT="20" BORDER="0" ALT=""><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/indent.gif" onclick="format(\'Indent\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="增加缩进"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/outdent.gif" onclick="format(\'Outdent\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="减少缩进"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/separator.gif"   WIDTH="2" HEIGHT="20" BORDER="0" ALT=""><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/link.gif" onclick="createLink()" WIDTH="20" HEIGHT="20" BORDER="0" ALT="添加链接"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/unlink.gif" onclick="format(\'Unlink\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="取消链接"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/image.gif" onclick="selectImage(\''.__APP__.'/Attach/select/module/'.MODULE_NAME.'\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="添加图片"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/separator.gif"   WIDTH="2" HEIGHT="20" BORDER="0" ALT=""><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/cut.gif" onclick="format(\'Cut\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="剪切"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/copy.gif" onclick="format(\'Copy\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="拷贝"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/paste.gif" onclick="format(\'Paste\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="粘贴"><IMG SRC="'.WEB_PUBLIC_URL.'/Js/smartEditor/images/removeformat.gif" onclick="format(\'RemoveFormat\')" WIDTH="20" HEIGHT="20" BORDER="0" ALT="清除格式"></div><textarea name="'.$name.'" id="sourceEditor" style="border:none;display:none" >'.$content.'</textarea><div style="'.$style.'" contentEditable="true" id="'.$id.'" >'.$content.'</div></div><SCRIPT LANGUAGE="JavaScript">function saveEditor(){document.getElementById("'.$name.'").value = document.getElementById("'.$id.'").innerHTML;} document.getElementById("'.$id.'").onblur=saveEditor;</SCRIPT>';
            	break;            
            case 'UBB':
				$parseStr	=	'<script type="text/javascript" src="__ROOT__/Public/Js/UbbEditor.js"></script><div style="padding:1px;width:'.$width.';border:1px solid silver;float:left;"><SCRIPT LANGUAGE="JavaScript"> showTool(); </SCRIPT></div><div><TEXTAREA id="UBBEditor" NAME="'.$name.'"  style="clear:both;float:none;width:'.$width.';height:'.$height.'" >'.$content.'</TEXTAREA></div><div style="padding:1px;width:'.$width.';border:1px solid silver;float:left;"><SCRIPT LANGUAGE="JavaScript">showEmot();  </SCRIPT></div>';
				break;
            default :
                $parseStr  =  '<textarea id="'.$id.'" style="'.$style.'" name="'.$name.'" >'.$content.'</textarea>';
		}

        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * form标签解析 是否需要引入表单验证脚本
     * 格式： <html:form validation="true" />
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    public function _form($attr,$content) 
    {
        $tag	= $this->parseXmlAttr($attr,'form');
        $text	= $tag['text'];
        $validation  = $tag['validation'];
        $js		= '<script language="JavaScript" src="'.WEB_URL.'/'.TMPL_DIR.'/'.TEMPLATE_NAME.'/public/'.'js/CheckForm.js"></script>';
        $parseStr = '';
        if($validation == 'true' || $validation == 1 ) {
            $attr       = preg_replace('/validation=(.+?)\s/is',' onsubmit="return Check(this);" ',$attr);
            $parseStr  .= $js.'<br/><form '.$attr.' >';

        }else {
            $attr       = preg_replace('/validation=(.+?)\s/is',' ',$attr);
        	$parseStr   = '<form '.$attr.' >';
        }
        $parseStr .= $this->tpl->parse($content);
        $parseStr .= '</form>';
        return $parseStr;
    }


    /**
     +----------------------------------------------------------
     * select标签解析
     * 格式： <html:select options="name" selected="value" />
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    public function _select($attr) 
    {
        $tag        = $this->parseXmlAttr($attr,'select');
        $name       = $tag['name'];
        $options    = $tag['options'];
        $values     = $tag['values'];
        $output     = $tag['output'];
        $multiple   = $tag['multiple'];
        $id         = $tag['id'];
        $size       = $tag['size'];
        $first      = $tag['first'];
        $selected   = $tag['selected'];
        $style      = $tag['style'];
        $ondblclick = $tag['dblclick'];
		$onchange	= $tag['change'];
        
        if(!empty($multiple)) {
            $parseStr = '<select id="'.$id.'" name="'.$name.'" ondblclick="'.$ondblclick.'" onchange="'.$onchange.'" multiple="multiple" class="'.$style.'" size="'.$size.'" >';
        }else {
        	$parseStr = '<select id="'.$id.'" name="'.$name.'" onchange="'.$onchange.'" ondblclick="'.$ondblclick.'" class="'.$style.'" >';
        }
        if(!empty($first)) {
            $parseStr .= '<option value="" >'.$first.'</option>';
        }
        if(!empty($options)) {
            $parseStr   .= '<?php  foreach($'.$options.' as $key=>$val) { ?>';
            if(!empty($selected)) {
                $parseStr   .= '<?php if(!empty($'.$selected.') && ($'.$selected.' == $key || in_array($key,$'.$selected.'))) { ?>';
                $parseStr   .= '<option selected="selected" value="<?php echo $key ?>"><?php echo $val ?></option>';
                $parseStr   .= '<?php }else { ?><option value="<?php echo $key ?>"><?php echo $val ?></option>';
                $parseStr   .= '<?php } ?>';
            }else {
                $parseStr   .= '<option value="<?php echo $key ?>"><?php echo $val ?></option>';
            }
            $parseStr   .= '<?php } ?>';
        }else if(!empty($values)) {
            $parseStr   .= '<?php  for($i=0;$i<count($'.$values.');$i++) { ?>';
            if(!empty($selected)) {
                $parseStr   .= '<?php if(!empty($'.$selected.') && ($'.$selected.' == $'.$values.'[$i] || in_array($'.$values.'[$i],$'.$selected.'))) { ?>';
                $parseStr   .= '<option selected="selected" value="<?php echo $'.$values.'[$i] ?>"><?php echo $'.$output.'[$i] ?></option>';
                $parseStr   .= '<?php }else { ?><option value="<?php echo $'.$values.'[$i] ?>"><?php echo $'.$output.'[$i] ?></option>';
                $parseStr   .= '<?php } ?>';
            }else {
                $parseStr   .= '<option value="<?php echo $'.$values.'[$i] ?>"><?php echo $'.$output.'[$i] ?></option>';
            }
            $parseStr   .= '<?php } ?>';
        }
        $parseStr   .= '</select>';
        return $parseStr;
    }


    /**
     +----------------------------------------------------------
     * checkbox标签解析
     * 格式： <html:checkbox checkboxs="" checked="" />
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    public function _checkbox($attr) 
    {
        $tag        = $this->parseXmlAttr($attr,'checkbox');
        $name       = $tag['name'];
        $checkboxes = $tag['checkboxes'];
        $checked    = $tag['checked'];
        $separator  = $tag['separator'];
        $checkboxes = $this->tpl->get($checkboxes);
        $checked    = $this->tpl->get($checked)?$this->tpl->get($checked):$checked;
        $parseStr   = '';
        foreach($checkboxes as $key=>$val) {
            if($checked == $key  || in_array($key,$checked) ) {
                $parseStr .= '<input type="checkbox" checked="checked" name="'.$name.'[]" value="'.$key.'">'.$val.$separator;
            }else {
                $parseStr .= '<input type="checkbox" name="'.$name.'[]" value="'.$key.'">'.$val.$separator;
            }
            
        }
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * mulitSelect标签解析
     * 格式： <html:list datasource="" show="" />
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _multiCheckBox($attr) 
    {
        $tag        = $this->parseXmlAttr($attr,'mulitCheckBox');
        $id         = $tag['id'];                   //表格ID
        $name       = $tag['name'];                 //返回表单值
        $source     = $tag['source'];               //原始数据源
        $size       = $tag['size'];                 //下拉列表size
        $style      = $tag['style'];                //表格样式

        $parseStr	= "<!-- Think 系统多选组件开始 -->\n<div align=\"center\"><TABLE class=\"".$style."\">";
        $parseStr	.= '<tr><td height="5" colspan="3" class="topTd" ></td></tr>';
        $parseStr	.= '<TR><Th width="44%" >'.$sourceTitle.'</Th><Th ></Th><Th width="44%">'.$targetTitle.'</Th></TR>';
        $parseStr	.= '<TR><TD ><div class="solid"><html:select id="sourceSelect" options="'.$source.'" dblclick="addItem()" multiple="true" style="multiSelect" size="'.$size.'" /></div></TD><TD valign="middle"><div style="margin-top:35px"><html:imageBtn value="添加" click="addItem()" style="impBtn vMargin fLeft " /><html:imageBtn type="button" value="全选" click="addAllItem()" style="impBtn vMargin fLeft " /><html:imageBtn value="移除" click="delItem()" style="impBtn vMargin fLeft " /><html:imageBtn  value="全删" click="delAllItem()" style="impBtn vMargin fLeft " /></div></TD>	<TD ><div class="solid"><html:select name="'.$name.'[]" id="targetSelect" options="'.$target.'" dblclick="delItem()" multiple="true" style="multiSelect" size="'.$size.'" /></div></TD></TR><tr><td height="5" colspan="3" class="bottomTd" ></td></tr></TABLE></div>';
        $parseStr	.= "\n<!-- Think 系统多选组件结束 -->\n";
        return $parseStr;
	}

    /**
     +----------------------------------------------------------
     * radio标签解析
     * 格式： <html:radio radios="name" checked="value" />
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    public function _radio($attr) 
    {
        $tag        = $this->parseXmlAttr($attr,'radio');
        $name       = $tag['name'];
        $radios     = $tag['radios'];
        $checked    = $tag['checked'];
        $separator  = $tag['separator'];
        $radios     = $this->tpl->get($radios);
        $checked    = $this->tpl->get($checked)?$this->tpl->get($checked):$checked;
        $parseStr   = '';
        foreach($radios as $key=>$val) {
            if($checked == $key ) {
                $parseStr .= '<input type="radio" checked="checked" name="'.$name.'[]" value="'.$key.'">'.$val.$separator;
            }else {
                $parseStr .= '<input type="radio" name="'.$name.'[]" value="'.$key.'">'.$val.$separator;
            }
            
        }
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * link标签解析
     * 格式： <html:link file="" type="" />
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    public function _link($attr) 
    {
        $tag        = $this->parseXmlAttr($attr,'link');
        $file       = $tag['href'];
        $type       = isset($tag['type'])?
                    strtolower($tag['type']):
                    strtolower(substr(strrchr($file, '.'),1));
        if($type=='js') {
            $parseStr = "<script language='JavaScript' src='".$file."'></script> ";
        }elseif($type=='css') {
            $parseStr = "<link rel='stylesheet' type='text/css' href='".$file."'>";
        }

        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * link标签解析
     * 格式： <html:link file="" type="" />
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    public function _import($attr) 
    {
        $tag        = $this->parseXmlAttr($attr,'import');
        $file       = $tag['file'];
        $basepath   = !empty($tag['basepath'])?$tag['basepath']:WEB_PUBLIC_URL;
        $type       = !empty($tag['type'])?  strtolower($tag['type']):'js';
        if($type=='js') {
            $parseStr = "<script language='JavaScript' src='".$basepath.'/'.str_replace('.','/',$file).'.js'."'></script> ";
        }elseif($type=='css') {
            $parseStr = "<link rel='stylesheet' type='text/css' href='".$basepath.'/'.str_replace('.','/',$file).'.css'."'>";
        }

        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * imageLink标签解析
     * 格式： <html:imageLink type="" value="" />
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    public function _imgLink($attr) 
    {
        $tag        = $this->parseXmlAttr($attr,'imgLink');
        $name       = $tag['name'];                //名称
        $alt        = $tag['alt'];                //文字
        $id         = $tag['id'];                //ID
        $style      = $tag['style'];                //样式名
        $click      = $tag['click'];                //点击
        $type       = $tag['type'];                //点击
        if(empty($type)) {
            $type = 'button';
        }
       	$parseStr   = '<span class="'.$style.'" ><INPUT title="'.$alt.'" TYPE="'.$type.'" id="'.$id.'"  name="'.$name.'" onmouseover="this.style.filter=\'alpha(opacity=100)\'" onmouseout="this.style.filter=\'alpha(opacity=80)\'" onclick="'.$click.'" align="absmiddle" class="'.$name.' imgLink"></span>';

        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * swf标签解析 插入flash文件
     * 格式： <html:swf type="" value="" />
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    public function _swf($attr,$content) 
    {
        $tag        =	$this->parseXmlAttr($attr,'swf');
        $id			=	$tag['id'];           
        $src		=	$tag['src'];           
        $width		=	$tag['width'];    
		$parm		=	$tag['parm'];
		$vars		=	$tag['vars'];
		$bgcolor	=	$tag['bgcolor'];
        $height     =	$tag['height'];      
        $version    =	$tag['version'];     
        $autoinstall=	$tag['autoinstall'];  
		
        $parseStr   = '<div id="flashcontent">'.$content.'</div><script type="text/javascript">';
		$parseStr	.='// <![CDATA['."\r\n";
		$parseStr	.= 'var so = new SWFObject("'.$src.'", "'.$id.'", "'.$width.'", "'.$height.'", "'.$version.'", "'.$bgcolor.'","'.$autoinstall.'");'."\r\n";
		$parseStr	.=	'so.addVariable("var", "value");'."\r\n";
		$parseStr	.=	'so.addParam("scale", "noscale");'."\r\n";
		$parseStr	.=	'so.write("flashcontent");'."\r\n";
		$parseStr	.= '// ]]></script>';

        return $parseStr;
    }


    /**
     +----------------------------------------------------------
     * imageBtn标签解析
     * 格式： <html:imageBtn type="" value="" />
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string|void
     +----------------------------------------------------------
     */
    public function _imageBtn($attr) 
    {
        $tag        = $this->parseXmlAttr($attr,'imageBtn');
        $name       = $tag['name'];                //名称
        $value      = $tag['value'];                //文字
        $id         = $tag['id'];                //ID
        $style      = $tag['style'];                //样式名
        $click      = $tag['click'];                //点击
        $type       = empty($tag['type'])?'button':$tag['type'];                //按钮类型

        if(!empty($name)) {
            $parseStr   = '<div class="'.$style.'" ><INPUT TYPE="'.$type.'" id="'.$id.'" name="'.$name.'" value="'.$value.'" onclick="'.$click.'" class="'.$name.' imgButton"></div>';
        }else {
        	$parseStr   = '<div class="'.$style.'" ><INPUT TYPE="'.$type.'" id="'.$id.'"  name="'.$name.'" value="'.$value.'" onclick="'.$click.'" class="button"></div>';
        }
        
        return $parseStr;
    }

    /**
     +----------------------------------------------------------
     * mulitSelect标签解析
     * 格式： <html:list datasource="" show="" />
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _multiSelect($attr) 
    {
        $tag        = $this->parseXmlAttr($attr,'mulitSelect');
        $id         = $tag['id'];                   //表格ID
        $name       = $tag['name'];                 //返回表单值
        $source     = $tag['source'];               //原始数据源
        $target     = $tag['target'];               //目标数据源
        $size       = $tag['size'];                 //下拉列表size
        $style      = $tag['style'];                //表格样式
        $multiple   = $tag['multiple'];             //是否多选
        $sourceTitle   = $tag['sourcetitle'];       //原始标题
        $targetTitle   = $tag['targettitle'];       //目标标题

        $parseStr	= "<!-- Think 系统多选组件开始 -->\n<div align=\"center\"><TABLE class=\"".$style."\">";
        $parseStr	.= '<tr><td height="5" colspan="3" class="topTd" ></td></tr>';
        $parseStr	.= '<TR><Th width="44%" >'.$sourceTitle.'</Th><Th ></Th><Th width="44%">'.$targetTitle.'</Th></TR>';
        $parseStr	.= '<TR><TD ><div class=""><html:select id="sourceSelect" options="'.$source.'" dblclick="addItem()" multiple="true" style="multiSelect" size="'.$size.'" /></div></TD><TD valign="middle"><div style="margin-top:35px"><html:imageBtn value="添 加" click="addItem()" style="button vMargin fLeft " /><html:imageBtn type="button" value="全 选" click="addAllItem()" style="button vMargin fLeft " /><html:imageBtn value="移 除" click="delItem()" style="button vMargin fLeft " /><html:imageBtn  value="全 删" click="delAllItem()" style="button vMargin fLeft " /></div></TD>	<TD ><div class=""><html:select name="'.$name.'[]" id="targetSelect" options="'.$target.'" dblclick="delItem()" multiple="true" style="multiSelect" size="'.$size.'" /></div></TD></TR><tr><td height="5" colspan="3" class="bottomTd" ></td></tr></TABLE></div>';
        $parseStr	.= "\n<!-- Think 系统多选组件结束 -->\n";
        return $parseStr;
	}

	public function _acl($attr) {
        $tag        = $this->parseXmlAttr($attr,'accessSelect');
        $id         = $tag['id'];                   //表单ID
        $name       = $tag['name'];                 //返回表单值
        $title     = $tag['title'];               //标题
		$module	=	$tag['module'];		// 授权模块名称
		$accessList	=	$tag['accesslist'];		// 权限列表
		$selectAccessList	 =	 $tag['selectaccesslist'];	// 已经授权的列表
		$submitMethod	=	$tag['submitmethod'];		// 提交响应方法
		$width	=	$tag['width']?$tag['width']:'260px';
		$size	=	$tag['size']?$tag['size']:15;
		$parseStr	.=	 '<!-- 授权组件开始 --><html:import file="Js.Form.MultiSelect" /><FORM METHOD=POST id="'.$id.'"><TABLE class="select" style="width:'.$width.'"><tr><td height="5" colspan="3" class="topTd" ></td></tr><TR><Th class="tCenter">'.$title.' <html:select name="groupId" style="" change="location.href = \'?groupId=\'+this.options[this.selectedIndex].value;" first="选择组" options="groupList" selected="selectGroupId" /></Th></TR><TR><TH ></TH></TR><TR><TD ><html:select id="groupActionId" name="'.$name.'[]" options="'.$accessList.'" selected="'.$selectAccessList.'"  multiple="true" style="multiSelect" size="'.$size.'" /></td></tr><tr><td  class="row tCenter" ><INPUT TYPE="button" onclick="allSelect()" value="全 选" class="submit  ">	<INPUT TYPE="button" onclick="InverSelect()" value="反 选" class="submit  "> <INPUT TYPE="button" onclick="allUnSelect()" value="全 否" class="submit "> <INPUT TYPE="button" onclick="'.$submitMethod.'()" value="保 存" class="submit  "><INPUT TYPE="hidden" NAME="module" value="'.$module.'"><INPUT TYPE="hidden" name="ajax" VALUE="1"></td></tr><tr><td height="5" class="bottomTd" ></td></tr></TABLE></FORM><!-- 授权组件结束 -->';
		return $parseStr;
	}

    /**
     +----------------------------------------------------------
     * list标签解析
     * 格式： <html:list datasource="" show="" />
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @param string $attr 标签属性
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     */
    public function _list($attr) 
    {
        $tag        = $this->parseXmlAttr($attr,'list');
        $id         = $tag['id'];                       //表格ID
        $datasource = $tag['datasource'];               //列表显示的数据源VoList名称
        $pk         = empty($tag['pk'])?'id':$tag['pk'];//主键名，默认为id
        $style      = $tag['style'];                    //样式名
        $name       = !empty($tag['name'])?$tag['name']:'vo';                 //Vo对象名
        $action     = $tag['action'];                   //是否显示功能操作
        $checkbox   = $tag['checkbox'];                 //是否显示Checkbox
        if(isset($tag['actionlist'])) {
            $actionlist = explode(',',trim($tag['actionlist']));    //指定功能列表
        }
        
        if(substr($tag['show'],0,1)=='$') {
            $show   = $this->tpl->get(substr($tag['show'],1));
        }else {
            $show   = $tag['show'];
        }
        $show       = explode(',',$show);                //列表显示字段列表

        //计算表格的列数
        $colNum     = count($show);           
        if(!empty($checkbox))   $colNum++;
        if(!empty($action))     $colNum++;

        //显示开始
		$parseStr	= "<!-- Think 系统列表组件开始 -->\n";
        $parseStr  .= '<TABLE id="'.$id.'" class="'.$style.'" cellpadding=0 cellspacing=0 >';
        $parseStr  .= '<tr><td height="5" colspan="'.$colNum.'" class="topTd" ></td></tr>';
        $parseStr  .= '<TR class="row" >';
        //列表需要显示的字段
        $fields = array();
        foreach($show as $key=>$val) {
        	$fields[] = explode(':',$val);
        }
        if(!empty($checkbox) && 'true'==strtolower($checkbox)) {//如果指定需要显示checkbox列
            $parseStr .='<th width="8"><input type="checkbox" id="check" onclick="CheckAll(\''.$id.'\')"></th>';            
        }
        foreach($fields as $field) {//显示指定的字段
            $property = explode('|',$field[0]);
            $showname = explode('|',$field[1]);
            if(isset($showname[1])) {
                $parseStr .= '<Th width="'.$showname[1].'">';
            }else {
                $parseStr .= '<Th>';
            }
            $showname[2] = isset($showname[2])?$showname[2]:$showname[0];
            $parseStr .= '<A HREF="javascript:sortBy(\''.$property[0].'\',\'{$sort}\',\''.ACTION_NAME.'\')" title="按照'.$showname[2].'{$sortType} ">'.$showname[0].'<equal name="order" value="'.$property[0].'" ><IMG SRC="../public/images/{$sortImg}.gif" WIDTH="12" HEIGHT="17" BORDER="0" align="absmiddle"></equal></A></Th>';
        }
        if(!empty($action)) {//如果指定显示操作功能列
            $parseStr .= '<th >操作</th>';
        }
        
        $parseStr .= '</TR>';
        $parseStr .= '<volist name="'.$datasource.'" id="'.$name.'" ><TR class="row" onmouseover="over()" onmouseout="out()" onclick="change()" >';	//支持鼠标移动单元行颜色变化 具体方法在js中定义

        if(!empty($checkbox)) {//如果需要显示checkbox 则在每行开头显示checkbox
            $parseStr .= '<td><input type="checkbox" name="key"	value="{$'.$name.'.'.$pk.'}"></td>';
        }
        foreach($fields as $field) {
            //显示定义的列表字段
            $parseStr   .=  '<TD>';
            if(!empty($field[2])) {
                // 支持列表字段链接功能 具体方法由JS函数实现
                $href = explode('|',$field[2]);
                if(count($href)>1) {
                    //指定链接传的字段值
                    $parseStr .= '<a href="javascript:'.$href[0].'(\'{$'.$name.'.'.$href[1].'}\')">';
                }else {
                    //如果没有指定默认传编号值
                    $parseStr .= '<a href="javascript:'.$field[2].'(\'{$'.$name.'.'.$pk.'}\')">';
                }
            }
            $property = explode('|',$field[0]);
            if(count($property)>1) {
                $parseStr .= '{$'.$name.'.'.$property[0].'|'.$property[1].'}';
            }else {
                $parseStr .= '{$'.$name.'.'.$field[0].'}';
            }
            if(!empty($field[2])) {
                $parseStr .= '</a>';
            }
            $parseStr .= '</TD>';
            
        }
        if(!empty($action)) {//显示功能操作
            if(!empty($actionlist[0])) {//显示指定的功能项
                $parseStr .= '<TD>';
                foreach($actionlist as $val) {
                    // edit:编辑 表示 脚本方法名:显示名称
                    $a = explode(':',$val);
                    $b = explode('|',$a[1]);
                    if(count($b)>1) {
                        $c = explode('|',$a[0]);
                        if(count($c)>1) {
                            $parseStr .= '<A HREF="javascript:'.$c[1].'({$'.$name.'.'.$pk.'})"><?php if(0== (is_array($'.$name.')?$'.$name.'["status"]:$'.$name.'->status)){ ?>'.$b[1].'<?php } ?></A><A HREF="javascript:'.$c[0].'({$'.$name.'.'.$pk.'})"><?php if(1== (is_array($'.$name.')?$'.$name.'["status"]:$'.$name.'->status)){ ?>'.$b[0].'<?php } ?></A> ';
                        }else {
                            $parseStr .= '<A HREF="javascript:'.$a[0].'({$'.$name.'.'.$pk.'})"><?php if(0== (is_array($'.$name.')?$'.$name.'["status"]:$'.$name.'->status)){ ?>'.$b[1].'<?php } ?><?php if(1== (is_array($'.$name.')?$'.$name.'["status"]:$'.$name.'->status)){ ?>'.$b[0].'<?php } ?></A> ';
                        }
                        
                    }else {
                        $parseStr .= '<A HREF="javascript:'.$a[0].'({$'.$name.'.'.$pk.'})">'.$a[1].'</A> ';
                    }
                    
                }
                $parseStr .= '</TD>';
            }else { //显示默认的功能项，包括编辑、删除
                $parseStr .= '<TD><A HREF="javascript:edit({$'.$name.'.'.$pk.'})">编辑</A> <A onfocus="javascript:getTableRowIndex(this)" HREF="javascript:del({$'.$name.'.'.$pk.'})">删除</A></TD>';
            }

        }
        $parseStr	.= '</TR></volist><tr><td height="5" colspan="'.$colNum.'" class="bottomTd"></td></tr></TABLE>';
        $parseStr	.= "\n<!-- Think 系统列表组件结束 -->\n";
        return $parseStr;
    }

	
}//类定义结束
?>