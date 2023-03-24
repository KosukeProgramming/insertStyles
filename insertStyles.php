<?php 
/*
 * Plugin Name: insertStyles
 */


function my_plugin_options() {
    echo '<p></p>';
}

function my_plugin_menu() {
    add_options_page('My Plugin Options', 'insertStyles','manage_options', 'my-unique-identifier', 'my_plugin_options' );
}

add_action('admin_menu', 'my_plugin_menu');
