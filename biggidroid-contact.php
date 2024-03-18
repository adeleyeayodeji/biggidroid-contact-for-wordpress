<?php

/**
 * Plugin Name: BiggiDroid Contact
 * Plugin URI:  https://biggidroid.com
 * Author:      Adeleye Ayodeji
 * Author URI:  https://biggidroid.com
 * Description: A simple contact form plugin
 * Version:     0.1.0
 * License:     GPL-2.0+
 * License URL: http://www.gnu.org/licenses/gpl-2.0.txt
 * text-domain: biggidroid-contact
 */

//check for security
if (!defined('WPINC')) {
    die('Cant access file directly');
}

//define constants
define('BIGGIDROID_CONTACT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('BIGGIDROID_CONTACT_PLUGIN_URL', plugin_dir_url(__FILE__));
define('BIGGIDROID_CONTACT_PLUGIN_VERSION', time());
//assets url
define('BIGGIDROID_CONTACT_ASSETS_URL', BIGGIDROID_CONTACT_PLUGIN_URL . 'assets/');
//shortcode prefix
define('BIGGIDROID_CONTACT_SHORT_CODE_PREFIX', 'biggidroid_contact_');


//check if BiggiDroidContactForm is exists
if (!class_exists('BiggiDroidContactForm')) {
    //include the class file
    include_once BIGGIDROID_CONTACT_PLUGIN_DIR . '/includes/biggidroid-contact-class.php';
    //create an instance
    new BiggiDroidContactForm();
}
