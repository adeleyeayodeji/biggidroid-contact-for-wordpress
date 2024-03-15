<?php
//check for security
if (!defined('WPINC')) {
    die('Cant access file directly');
}
?>
<div class="biggidroid-contact-form-description">
    <p>
        Copy this shortcode and paste it into your post, page, or text widget content:
    </p>
    <p class="biggidroid-contact-highlight">
        [biggidroid-contact-form id="<?php echo esc_html($checkforid->generated_id); ?>" title="<?php echo esc_html(get_post_field('post_title', $checkforid->post_id)); ?>"]
    </p>
</div>