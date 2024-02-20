<?php
$exception = $this->Get_data("exception");
echo $exception ? $exception->getMessage() : "Page not found";
?>
