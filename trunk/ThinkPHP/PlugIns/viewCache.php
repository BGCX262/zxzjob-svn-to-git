<?php 
/*
Plugin Name: viewCache
Plugin URI: http://thinkphp.cn/
Description: 视图缓存插件，支持全部视图和局部视图缓存
Author: 流年
Version: 1.0
Author URI: http://blog.liu21st.com/
*/ 

    /**
     +----------------------------------------------------------
     * 检查并读取视图缓存
     *
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return void
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    function checkViewCache() 
    {             
         $cacheInfo  =  S(MODULE_NAME.'_'.ACTION_NAME.'_CACHE');
         if($cacheType   = $cacheInfo['type']) {
             // 全局视图缓存时间
             $cacheTime   = $cacheInfo['time'];
             if($cacheType=='ALL') { 
                 //全部视图缓存
                $cacheFile =  TEMP_PATH.md5($_SERVER['REQUEST_URI']).'.html';
                if(file_exists($cacheFile) && time() <= filemtime($cacheFile)+$cacheTime) {
                    // 视图缓存有效 读取缓存Action
                    readfile($cacheFile);
                    exit();
                }
             }elseif($cacheType=='PART') { 
                 //局部视图缓存
                // 获取视图缓存数据
                $view_cache   =  S(MODULE_NAME.'_'.ACTION_NAME.'_DATA');
                if($view_cache) {
                    // 存在视图缓存数据
                    if($cacheInfo['default']=='NOCACHE') {
                        foreach($view_cache as $key=>$val) {
                            if(isset($val['expire'])) {
                                // 局部视图缓存时间
                                $cacheTime   = $val['expire'];
                            }
                            $cacheFile  =  TEMP_PATH.md5($_SERVER['REQUEST_URI']).'_'.$val['id'].'.html'; //缓存文件定位
                            if(file_exists($cacheFile) && time() <= filemtime($cacheFile)+$cacheTime) {
                                // 视图缓存有效 给Action调用
                                Session::set(MODULE_NAME.'_'.ACTION_NAME.'_'.$val['id'],true);
                            }else {
                                Session::set(MODULE_NAME.'_'.ACTION_NAME.'_'.$val['id'],null);
                            }                              
                         }
                    }
                    elseif($cacheInfo['default']=='CACHE') {
                        $cacheFile  =  TEMP_PATH.md5($_SERVER['REQUEST_URI']).'.html'; //缓存文件定位
                        if(file_exists($cacheFile) && time() <= filemtime($cacheFile)+$cacheTime) {
                            // 视图缓存有效 给Action调用
                            Session::set(MODULE_NAME.'_'.ACTION_NAME.'_CACHE',true);
                        }else {
                        	Session::set(MODULE_NAME.'_'.ACTION_NAME.'_CACHE',null);
                        }
                     }
                }
             }
         }
        return ;
    }

    // 替换模版缓存标签 每次模版编译的时候执行
    function replaceCacheTag($content) 
    {
        //搜索是否有ThinkCache标签 判断视图缓存类型 <thinkcache type="" time="" />
        $find = preg_match('/<thinkcache\s(.+?)\s\/>\W/is',$content,$matches);
        if($find) {
            //替换ThinkCache标签
            $content = str_replace($matches[0],'',$content);
            //解析ThinkCache标签
            $tagLibs = $matches[1];
            $xml =  '<tpl><tag '.$tagLibs.' /></tpl>';
			$xml = simplexml_load_string($xml);
			$xml = (array)($xml->tag->attributes());
			$array = array_change_key_case($xml['@attributes']);
            if(!isset($array['time'])) {
            	$array['time'] = C('HTML_CACHE_TIME');
            }
            if(!isset($array['default'])) {
            	$array['default'] =  'nocache';
            }
            $cacheInfo   = array_map('strtoupper',$array);
            S(MODULE_NAME.'_'.ACTION_NAME.'_CACHE',$cacheInfo,-1);
        }else 
			$cacheInfo = array('type'=>'', 'default'=>'');

        if($cacheInfo['type']=='PART') {
            // 部分视图缓存 分两种情况 
            $cacheData    = array();
            if($cacheInfo['default'] == 'NOCACHE') {
            	// 默认部分为不缓存 检查模版是否存在 缓存标签 <cache id="" expire=""></cache> 
                $find = preg_match_all('/<cache\s(.+?)>(.+?)<\/cache>/is',$content,$matches,PREG_SET_ORDER);
                if($find) {
                    // 获取视图缓存数据
                    //解析Cache标签
                    foreach($matches as $key=>$match) {
                        // 替换匹配的标签
                        $cacheAttr = $match[1]; 
                        $cacheContent   =  $match[2];
                        $xml =  '<think><cache '.$cacheAttr.' /></think>';
						$xml = simplexml_load_string($xml);
						$xml = (array)($xml->cache->attributes());
						$array = array_change_key_case($xml['@attributes']);
                        $cacheId =  $array['id'];
                        // 生成缓存替换标签
                        $parseStr  =  '<cache_'.MODULE_NAME.'_'.ACTION_NAME.'_'.$cacheId.'>';
                        $parseStr .= $cacheContent;
                        $parseStr .=  '</cache_'.MODULE_NAME.'_'.ACTION_NAME.'_'.$cacheId.'>';
                        $content = str_replace($match[0],$parseStr,$content);
                        // 记录视图缓存数据
                        $cacheData[] =   $array;
                    } 
                    // 记录所有的视图缓存数据
                    S(MODULE_NAME.'_'.ACTION_NAME.'_DATA',$cacheData,-1);
                }  
            }elseif($cacheInfo['default'] ==   'CACHE') {
            	//默认部分为缓存 检查是否存在<nocache id=""></nocache> 标签
                $find = preg_match_all('/<nocache\s(.+?)>(.+?)<\/nocache>/is',$content,$matches,PREG_SET_ORDER);
                if($find) {
                    // 获取视图缓存数据
                    //解析Cache标签
                    foreach($matches as $key=>$match) {
                        // 替换匹配的标签
                        $cacheAttr = $match[1]; 
                        $cacheContent   =  $match[2];
                        $xml =  '<think><cache '.$cacheAttr.' /></think>';
						$xml = simplexml_load_string($xml);
						$xml = (array)($xml->cache->attributes());
						$array = array_change_key_case($xml['@attributes']);
                        $cacheId =  $array['id'];
                        // 生成缓存替换标签
                        $parseStr  =  '<nocache_'.MODULE_NAME.'_'.ACTION_NAME.'_'.$cacheId.'>';
                        $parseStr .= $cacheContent;
                        $parseStr .=  '</nocache_'.MODULE_NAME.'_'.ACTION_NAME.'_'.$cacheId.'>';
                        $content = str_replace($match[0],$parseStr,$content);
                        // 记录视图缓存数据
                        $cacheData[] =   $array;
                    } 
                    // 记录所有的视图缓存数据
                    S(MODULE_NAME.'_'.ACTION_NAME.'_DATA',$cacheData,-1);
                }  
            }
        }        
        return $content;
    }

    /**
     +----------------------------------------------------------
     * 写入视图缓存
     * 
     +----------------------------------------------------------
     * @access public 
     +----------------------------------------------------------
     * @return string
     +----------------------------------------------------------
     * @throws ThinkExecption
     +----------------------------------------------------------
     */
    function writeViewCache($content) 
    {
        // 生成视图缓存数据
         $cacheInfo  =  S(MODULE_NAME.'_'.ACTION_NAME.'_CACHE');
         if($cacheType   =  $cacheInfo['type']) {
             // 全局视图缓存时间
            $cacheTime   = $cacheInfo['time'];
            if($cacheType=='ALL') {
                // 生成全局视图缓存文件
                $cacheFile =  TEMP_PATH.md5($_SERVER['REQUEST_URI']).'.html'; 
                if( !file_exists($cacheFile) || time()>filemtime($cacheFile)+$cacheTime) {
                    // 缓存不存在或者无效，重新生成
                    if( false === file_put_contents($cacheFile,trim($content))) {
                        throw_exception(L('缓存文件写入失败！'));
                    }
                }
            }else {
                if($cacheInfo['default']=='NOCACHE') {
                    // 生成局部视图缓存文件
                    $cacheData    = S(MODULE_NAME.'_'.ACTION_NAME.'_DATA');
                    foreach($cacheData as $key=>$val) {
                        $cacheTime   = isset($val['expire'])?$val['expire']:$cacheTime;   // 局部缓存时间
                        $cacheFile =  TEMP_PATH.md5($_SERVER['REQUEST_URI']).'_'.$val['id'].'.html'; 
                        // 读取局部视图缓存区域
                        $tag =  'cache_'.MODULE_NAME.'_'.ACTION_NAME.'_'.$val['id'];
                        $find = preg_match('/<'.$tag.'>(.+?)<\/'.$tag.'>/is',$content,$matches);
                        if($find) {
                            $cacheContent   =  $matches[1];
                        }
                        if( !file_exists($cacheFile) || time()>filemtime($cacheFile)+$cacheTime) {
                            // 缓存不存在或者无效，重新生成
                            if(trim($cacheContent)!='') {
                                if( false === file_put_contents($cacheFile,trim($cacheContent))) {
                                    throw_exception(L('缓存文件写入失败！'));
                                }                            	
                            }
                        }else {
                            $cacheContent   =  file_get_contents($cacheFile);
                            $content = str_replace($matches[0],$cacheContent,$content);
                        }                	
                    }                	
                }elseif($cacheInfo['default']=='CACHE') {
                    $cacheFile =  TEMP_PATH.md5($_SERVER['REQUEST_URI']).'.html';
                    if( !file_exists($cacheFile) || time()>filemtime($cacheFile)+$cacheTime) {
                        // 缓存不存在或者无效，重新生成
                        if( false === file_put_contents($cacheFile,trim($content))) {
                            throw_exception(L('缓存文件写入失败！'));
                        }
                    }else {
                        // 读取全局缓存文件
                    	$cacheContent   =  file_get_contents($cacheFile);
                        // 替换局部视图不缓存的区域
                        $cacheData    = S(MODULE_NAME.'_'.ACTION_NAME.'_DATA');
                        foreach($cacheData as $key=>$val) {
                            // 读取局部视图缓存区域
                            $tag =  'nocache_'.MODULE_NAME.'_'.ACTION_NAME.'_'.$val['id'];
                            $find = preg_match('/<'.$tag.'>(.+?)<\/'.$tag.'>/is',$content,$matches);
                            if($find) {
                            	$replaceContent = $matches[1];
                            }
                            $find = preg_match('/<'.$tag.'>(.+?)<\/'.$tag.'>/is',$cacheContent,$matches);
                            if($find) {
                                $nocacheContent   =  $matches[1];
                            }
                            // 用动态内容替换不缓存的区域
                            $content   =  str_replace($nocacheContent,$replaceContent,$cacheContent);
                        }  
                    }
                }
            }         
         }
        return $content;
    }

    add_filter('app_init','checkViewCache');
    add_filter('tmpl_replace','replaceCacheTag');
    add_filter('ob_content','writeViewCache');
?>