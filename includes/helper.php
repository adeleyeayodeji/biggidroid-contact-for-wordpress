<?php
//check for security
if (!defined('WPINC')) {
    die('Cant access file directly');
}

//check if get_biggidroid_form_fields function is already defined
if (!function_exists('get_biggidroid_form_fields')) {
    /**
     * Get BiggiDroid Form Fields
     * @param int $post_id
     * 
     * @return array
     */
    function get_biggidroid_form_fields($post_id)
    {
        //get biggidroid contact form
        $biggidroid_contact_form = BiggiDroidContactForm::getInstance();
        //get biggidroid form fields
        return $biggidroid_contact_form->getBiggiDroidFormFields($post_id);
    }
}
