<?php

/**
 * @param int $rating
 * @return string
 */
function html_rating($rating) {
    $output ='';
    for($i=1;$i<=5;$i++) {
        if($i <= $rating) {
            $output .= '<i class="fa fa-star"></i>';
        } else {
            $output .= '<i class="fa fa-star-o"></i>';
        }
    }
    return $output;
}
