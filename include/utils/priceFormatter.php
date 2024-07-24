<?php
function formatPrice($price) {
    if(count(explode('.',$price))===1){
        $price.='.00';
    }
    elseif(strlen(explode('.',$price)[1])===1){
            $price.='0';
    }
    return str_replace('.',',',$price);
}
