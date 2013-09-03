<?php
define(	'PSK_SGW_VERSION'  			, '0.3');


/*
 *	Global constants
 */
define(	'PSK_SGW_NAME'     			, 'Stronger Github Widget');
define(	'PSK_SGW_ID'       			, 'psk_sgw');


define( 'PSK_SGW_GITHUB_URL'        , 'https://www.github.com/' );
define( 'PSK_SGW_GITHUB_API_URL'    , 'https://api.github.com/users/' );
$PSK_SGW_GITHUB_API_OPT = array( 'sslverify' => false);


/*
 *	File paths
 */
define( 'PSK_SGW_PLUGIN_FOLDER'		, dirname(PSK_SGW_PLUGIN_FILE) . DIRECTORY_SEPARATOR);
define( 'PSK_SGW_CLASSES_FOLDER'	, PSK_SGW_PLUGIN_FOLDER . DIRECTORY_SEPARATOR . 'class' . DIRECTORY_SEPARATOR );
define( 'PSK_SGW_INCLUDES_FOLDER'	, PSK_SGW_PLUGIN_FOLDER . DIRECTORY_SEPARATOR . 'inc' . DIRECTORY_SEPARATOR );


/*
 *	Url paths
 */
define( 'PSK_SGW_PLUGIN_URL'		, plugin_dir_url(PSK_SGW_PLUGIN_FILE));
define( 'PSK_SGW_CSS_URL'			, PSK_SGW_PLUGIN_URL . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR );
define( 'PSK_SGW_JS_URL'			, PSK_SGW_PLUGIN_URL . DIRECTORY_SEPARATOR . 'js' . DIRECTORY_SEPARATOR );
define( 'PSK_SGW_IMG_URL'			, PSK_SGW_PLUGIN_URL . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR );


