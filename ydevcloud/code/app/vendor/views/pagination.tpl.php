<?php

/**
 * total: 总条数
 * pagesize: 每页显示条数 默认为20
 * currpage: 当前页
 *
 * @author leeboo
 */
$total    = $this->get_Data("total");
$pagesize = $this->get_Data("pagesize");
$currpage = $this->get_Data("currpage");
//var_dump($currpage);
$page_name = $this->get_Data("name");

if( ! $pagesize){
    $pagesize = 20;
}

if( ! $currpage){
    $currpage = 1;
}

if( ! $page_name ){
	$page_name = "page";
}

$pages = 10;//显示10页

$page_nums= ceil($total / $pagesize);

if( ! function_exists("_page_num") ){
	function _page_num($page_nums, $pages, $currpage){
		$pageArr = array();
		if($page_nums < $pages){
			for($i = 0; $i < $page_nums; $i++){
				$pageArr[$i] = $i + 1;
			}
		}else{
			for($i = 0; $i < $pages; $i++){
				$pageArr[$i] = $i;
			}
			if($currpage <= 3){
				for($i = 0; $i < count($pageArr); $i++){
					$pageArr[$i] = $i + 1;
				}
			}elseif($currpage <= $page_nums && $currpage > ($page_nums - $pages + 1)){
				for($i = 0; $i < count($pageArr); $i++){
					$pageArr[$i] = intval($page_nums - $pages + 1 + $i);
				}
			}else{
				for($i = 0; $i < count($pageArr); $i++){
					$pageArr[$i] = intval($currpage - 2 + $i);
				}
			}
		}
		return $pageArr;
	}
}
?>
<nav>
    <ul class="pagination">

        <?php
        if($currpage > 1){
            $firstUrl  = \yangzie\yze_merge_query_string("", array($page_name => 1));
            echo " <li class='page-item'><a class='page-link' href='{$firstUrl}'>".\yangzie\__('First Page')."</a></li> ";
        }else{
            echo " <li class='page-item disabled'><a class='page-link' href='javascript:void(0)'>".\yangzie\__('First Page')."</a></li> ";
        }
        ?>
        <?php
        $pageArr = _page_num($page_nums, $pages, $currpage);
        for($i = 0; $i < count($pageArr); $i++){
            $page = $pageArr[$i];
            if($page == $currpage){
            	echo  " <li class='page-item active'><a class='page-link' href='#'>$page</a></li> ";
            }else{
                $url =  \yangzie\yze_merge_query_string("", array($page_name => $page));
                echo "<li class='page-item'><a class='page-link' href='{$url}'>{$page}</a></li>";
            }
        }
        if($currpage < $page_nums){
            $lastUrl   = \yangzie\yze_merge_query_string("", array($page_name => $page_nums));
            echo " <li class='page-item'><a class='page-link' href='{$lastUrl}'>".\yangzie\__('Last Page')."</a></li> ";
        }else{
            echo "<li class='page-item disabled'><a class='page-link' href='javascript:void(0)'>".\yangzie\__('Last Page')."</a></li> ";
        }
        echo  "<li class='page-item disabled'><a class='page-link' href='javascript:void(0)'>".\yangzie\__('Total: ')."{$total}</a></li>";
        ?>
    </ul>
</nav>
