<?php

namespace app;

/**
 * 用户分组的形式是：
 * /------组根
 * 大组名/
 * 小组名/
 * 用户id
 *
 * /module/controller/action
 */
function yze_get_aco_desc($aconame) {
    foreach ( ( array ) yze_get_acos_aros () as $aco => $desc ) {
        if (preg_match ( "{^" . $aco . "}", $aconame )) {
            return @$desc ['desc'];
        }
    }
    return '';
}
function yze_get_ignore_acos() {
    return array();
}
function yze_get_acos_aros() {
    $array = array (
            "/" => array (//module/controller/action
                    "deny" => "",
                    "allow" => array (
                            "*" //aro
                    ),
                    "desc" => ""//功能说明 
            )
		);
    
	return $array;
}
?>