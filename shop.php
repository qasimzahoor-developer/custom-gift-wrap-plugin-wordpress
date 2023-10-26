<?php
//Get All Bundels
$categories = get_terms( array(
    'taxonomy' => 'bundles',
    'hide_empty' => true,
) );

//Add Template
require_once(ABSPATH . 'wp-content/plugins/giftwrap/templates/template-shop.php');
