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

$page_name = $this->get_Data("name");

if( ! $pagesize){
    $pagesize = PAGE_SIZE;
}



if( ! $page_name ){
	$page_name = "page";
}

$currpage = @$_GET[$page_name];

if( ! $currpage){
	$currpage = 1;
}
$pages = 3;//显示10页

$page_nums= ceil($total / $pagesize);

if( ! function_exists("_page_num") ){
	function _page_num($page_nums, $pages, $currpage){
		$pageArr = array();
                //leeboo @20180320 能显示全或者当前页就在$pages的中间范围内

		if($page_nums < $pages){
			for($i = 0; $i < $page_nums; $i++){
				$pageArr[] = $i + 1;
			}
		}elseif( ! $currpage || $currpage==1){
			for($i = 0; $i < $pages; $i++){
				$pageArr[] = $i + 1;
			}
		}else{
                    $midpage = floor($pages / 2);
                    for($i=0; $i<$midpage; $i++){
                        $pageArr[] = $i+$currpage-$midpage;
                    }
                    for($i=$midpage; $i<$pages; $i++){
                        $_ = $currpage++;
                        if($_>$page_nums)return $pageArr;
                        $pageArr[] = $_;
                    }
		}
		return $pageArr;
	}
}
?>
<style>
    table.log-pagination span{
        margin: 0px 5px;
    }
</style>
<nav>
    <?php if(!$total){?>
        <div>
            <p style="text-align: center;padding: 15px;">暂无数据</p>
        </div>
    <?php }?>

    <table class="log-pagination">
        <thead>
        <tr>
            <td>
        <?php
        if($currpage > 1){
            $firstUrl  = \yangzie\yze_merge_query_string("", array($page_name => 1));
            $prevUrl   = \yangzie\yze_merge_query_string("", array($page_name => $currpage-1));
            echo " <span><a href='{$firstUrl}'>首页</a></span> ";
            echo " <span><a href='{$prevUrl}' aria-label='Previous'> <span aria-hidden='true'>&laquo;</span> ";
        }else{
            echo " <span><a href='javascript:void(0)'>首页</a></span> ";
            echo " <span><a href='javascript:void(0)'>&laquo;</a></span> ";
        }
        ?>
        <?php
        $pageArr = _page_num($page_nums, $pages, $currpage);
        for($i = 0; $i < count($pageArr); $i++){
            $page = $pageArr[$i];
            if($page == $currpage){
            	echo  " <span><a href='javascript:void(0)'>[{$page}]</a></span> ";
            }else{
                $url =  \yangzie\yze_merge_query_string("", array($page_name => $page));
                echo "<span ><a href='{$url}'>{$page}</a></span>";
            }
        }
        if($currpage < $page_nums){
            $lastUrl   = \yangzie\yze_merge_query_string("", array($page_name => $page_nums));
            $nextUrl   = \yangzie\yze_merge_query_string("", array($page_name => $currpage+1));
            echo " <span ><a href='{$nextUrl}' aria-label='Next'> <span aria-hidden='true'>&raquo;</span></a></span> ";
            echo " <span ><a href='{$lastUrl}'>尾页</a></span> ";
        }else{
            echo "<span ><a href='javascript:void(0)' aria-label='Next'> <span aria-hidden='true'>&raquo;</span></a></span>";
            echo "<span ><a href='javascript:void(0)'>尾页</a></span> ";
        }
        echo  "<span ><a href='javascript:void(0)'>{$total}项/{$page_nums}页</a></span>";
        ?>
            </td>
        </tr>
        </thead>
    </table>
</nav>
