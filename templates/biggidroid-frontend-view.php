<?php
//check for security
if (!defined('WPINC')) {
    die('Cant access file directly');
}
?>
<p>
    Am working <?php echo esc_html($id) . ' ' . esc_html($title); ?>
</p>