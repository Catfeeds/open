<?php
namespace app\admin\validate;

use think\Validate;

/**
* 
*/
class ArticlelistValidate extends Validate
{
	
	protected $rule = [
			'al_title' => 'require',
			'ac_id' => 'require',
			'seo' => 'max:50',
			'al_link' => 'url',
			'al_time' => 'date|after:2010-10-01',
			'editorValue' => 'require',
	];

	protected $message = [
			'al_title.require' => '标题必须填',
			'ac_id.require' => '分类必须填',
			'seo.max' => '关键词最大字符为50',
			'al_link.url' => '链接格式不对',
			'al_time.date' => '日期格式不对',
			'al_time.after' => '日期必须大于 2010-10-01',
			'editorValue.require' => '文章内容必须填',
	];
}