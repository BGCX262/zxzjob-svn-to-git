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
 * Ajax标签库解析类
 +------------------------------------------------------------------------------
 * @category   Think
 * @package  Think
 * @subpackage  Template
 * @author    liu21st <liu21st@gmail.com>
 * @version   $Id$
 +------------------------------------------------------------------------------
 */
import('Think.Template.TagLib');
class TagLibDao extends TagLib
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
		parent::__construct('dao');
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
    function _find($attr,$content) 
    {
        $tag        =	$this->parseXmlAttr($attr,'find');
		$name	=	$tag['name'];
        $condition	    =	empty($tag['condition'])?"":$tag['condition'] ;       
		$result   	=	$tag['result'];   
        $cache         	=	empty($tag['cache'])?'1':$tag['cache'];   
        $relation	=	empty($tag['relation'])?'0':$tag['relation'] ;    
        $fields     =	empty($tag['fields']) ? '*':$tag['fields'] ;    

        $parseStr  =  '<?php $dao = D("'.$name.'");$'.$result.' = $dao->find("'.$condition.'","'.$fields.'",'.$cache.','.$relation.');?>';
        $parseStr .=  '<?php if($'.$result.'):?>'.$content;
        $parseStr .= "<?php endif;?>";
        return $parseStr;
    }

    function _findAll($attr,$content) 
    {
        $tag        =	$this->parseXmlAttr($attr,'find');
		$name	=	$tag['name'];
        $condition	    =	empty($tag['condition'])?"":$tag['condition'] ;       
		$result   	=	$tag['result'];   
 
        $fields     =	empty($tag['fields']) ? '*':$tag['fields'] ;    

        $parseStr  =  '<?php $dao = D("'.$name.'");$'.$result.' = $dao->findAll("'.$condition.'","'.$fields.'");?>';
        $parseStr .=  '<?php if($'.$result.'):?>'.$content;
        $parseStr .= "<?php endif;?>";
        return $parseStr;
    }

}//类定义结束
?>