<?php
//check for security
if (!defined('WPINC')) {
    die('Cant access file directly');
}
//get contact body for $post_id
$contact_body = get_post_meta($post_id, 'biggidroid_form_content', true);
?>
<div class="biggidroid-contact-form-frontend">
    <div class="biggidroid-contact-header">
        <h3>
            <?php echo esc_html($title) ?>
        </h3>
    </div>
    <div class="biggidroid-contact-body">
        <form action="" id="biggidroid-contact-form-submit">
            <input type="hidden" name="action" value="biggidroid_send_message">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('biggidroid-con tact-message'); ?>">
            <?php
            //get the shortcode regex
            $shortcode_regex = get_shortcode_regex();
            //get the shortcodes regex
            if (preg_match_all('/' . $shortcode_regex . '/s', $contact_body, $matches, PREG_SET_ORDER)) {
                //loop through the matches
                foreach ($matches as $match) {
                    //do the shortcode
                    echo do_shortcode($match[0]);
                }
            }
            ?>
            <div class="biggidroid-form-group">
                <button type="submit">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>