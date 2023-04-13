<?php
/**
 * トップページの条件式付きでwp_enqueue_styleを返す
 * */ 
function insertFrontStyle($insertName, $insertUrl, $selectPage) {
    $text = '';
    switch ($selectPage) {
        case 'top':
            $text = "if(is_front_page() || is_home()) {
                wp_enqueue_style({$insertName}, {$insertUrl})
            };";
            break;
        case 'page':
            $text = "if(is_page()) {
                wp_enqueue_style({$insertName}, {$insertUrl})
            };";
            break;
        case 'post':
            $text = "if(is_single()) {
                wp_enqueue_style({$insertName}, {$insertUrl})
            };";
            break;
        
        default:
            # code...
            break;
    }
        
        return $text;


}

/**
 * 固定ページの条件式付きでwp_enqueue_styleを返す
 * */ 
function insertPageStyle($insertName, $insertUrl) {
    $text = "if(is_page()) {
        wp_enqueue_style({$insertName}, {$insertUrl})
    };";
    return $text;
}


?>