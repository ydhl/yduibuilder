<?php
include 'config.php';

if(@$argv[1]){
	system("php run-tests.php {$argv[1]}");
}else{
	system("php run-tests.php ./");
}

?>
