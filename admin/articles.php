<?php
require_once 'header.php';

require_once '../include/pager.php';

require_once 'chkMenuId.php';

$admin_article_page_config = array
(
	"menu"=>array
	(
		"add"=>"添加","list"=>"列表"
	)
	,
	"config"=>array
	(
		"dbTable"=>"h_article","keyName"=>"文章","listNum"=>15,"recordAddBack"=>"addinfo","recordEditBack"=>"","defaultFun"=>"main();"
	)
	,
	"list"=>array
	(
		"h_title"=>array
		(
			"title"=>"标题","type"=>"string","width"=>"30%","align"=>"center"
		)
		,
		"h_pageKey"=>array
		(
			"title"=>"页面名称","type"=>"string","width"=>"","align"=>"center"
		)
		,
		"order"=>array
		(
			"title"=>"排序","width"=>"15%","align"=>"center"
		)
		,
		"action"=>array
		(
			"title"=>"相关操作","width"=>"15%","align"=>"center",
			"action"=>array
			(
				"edit"=>"修改","delete"=>"删除"
			)
		)
	)
	,
	"record"=>array
	(
		"hidden"=>array
		(
			"h_addTime"=>"{now}"
		)
		,
		"h_title"=>array
		(
			"title"=>"标题","control"=>"text","required"=>1,
			"attr"=>array
			(
				"class"=>"inputclass2","maxlength"=>"250"
			)
		)
		,
		"h_pageKey"=>array
		(
			"title"=>"页面名称","control"=>"text","required"=>1,
			"attr"=>array
			(
				"class"=>"inputclass2","maxlength"=>"250"
			)
		)
		,
		"h_keyword"=>array
		(
			"title"=>"页面关键字","control"=>"textarea","required"=>0,
			"attr"=>array
			(
				"class"=>"textareaclass5"
			)
		)
		,
		"h_description"=>array
		(
			"title"=>"页面介绍","control"=>"textarea","required"=>0,
			"attr"=>array
			(
				"class"=>"textareaclass5"
			)
		)
		,
		"h_info"=>array
		(
			"title"=>"内容","control"=>"fckeditor","required"=>0,
			"attr"=>array
			(
				"Height"=>400,"ToolbarSet"=>"Default"
			)
		)
	)
);

require_once 'inc_db_field_fun.php';

require_once 'footer.php';
?>