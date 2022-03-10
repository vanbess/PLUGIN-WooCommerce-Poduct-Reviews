<?php

/**
 * Plugin Name: SBWC Product Reviews
 * Description: Improved product reviews for WooCommerce
 * Version: 1.0.2
 * Author: Werner C. Bessinger
 */

//  abspath
if (!defined('ABSPATH')) :
    exit();
endif;

// init
function sbwcpr_init() {

    // constants
    define('SBWCPR_PATH', plugin_dir_path(__FILE__));
    define('SBWCPR_URI', plugin_dir_url(__FILE__));

    // traits
    require_once(SBWCPR_PATH.'includes/traits/trait-sbwcpr-css.php');
    require_once(SBWCPR_PATH.'includes/traits/trait-sbwcpr-js.php');
    require_once(SBWCPR_PATH.'includes/traits/trait-sbwcpr-user-ip.php');

    // classes
    require_once SBWCPR_PATH . 'includes/classes/class-sbwc-pr-front.php';
}
add_action('plugins_loaded', 'sbwcpr_init');
