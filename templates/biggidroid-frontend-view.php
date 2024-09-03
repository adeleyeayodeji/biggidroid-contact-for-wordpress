<?php
//check for security
if (!defined('WPINC')) {
    die('Cant access file directly');
}
//get biggidroid form fields
$biggidroid_form_fields = get_biggidroid_form_fields($post_id);
//get contact body
$contact_body = $biggidroid_form_fields['biggidroid-form-content'];
?>
<style>
    .biggidroid-form-message-error {
        color: red;
        border: 1px solid red;
        padding: 10px;
        border-radius: 5px;
    }

    .biggidroid-form-message-success {
        color: green;
        border: 1px solid green;
        padding: 10px;
        border-radius: 5px;
    }
</style>
<div class="biggidroid-contact-form-frontend">
    <div class="biggidroid-contact-header">
        <h3>
            <?php echo esc_html($title) ?>
        </h3>
    </div>
    <div class="biggidroid-contact-body">
        <form action="" id="biggidroid-contact-form-submit">
            <input type="hidden" name="action" value="biggidroid_send_message">
            <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('biggidroid-contact-message'); ?>">
            <input type="hidden" name="biggipost-id" value="<?php echo esc_attr($post_id); ?>">
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
            <div class="biggidroid-form-group">
                <div class="biggidroid-form-message">
                    <p id="biggidroid-form-message"></p>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    jQuery(document).ready(function($) {
        $('form#biggidroid-contact-form-submit').on('submit', function(e) {
            e.preventDefault();
            var form = $(this);
            var data = form.serialize();
            //send ajax
            $.ajax({
                type: "POST",
                url: "<?php echo admin_url('admin-ajax.php'); ?>",
                data,
                dataType: "json",
                beforeSend: function() {
                    form.find('button[type="submit"]').prop('disabled', true);
                    //change the button text
                    form.find('button[type="submit"]').text('Sending...');
                },
                success: function(response) {
                    console.log(response);
                    if (response.success) {
                        //show success message
                        form.find('#biggidroid-form-message').html(
                            `
                            <p class="biggidroid-form-message-success">${response.message}</p>
                            `
                        );
                        //reset the form
                        form[0].reset();
                    } else {
                        //show error message
                        form.find('#biggidroid-form-message').html(
                            `
                            <p class="biggidroid-form-message-error">${response.message}</p>
                            `
                        );
                    }
                    //restore the button
                    form.find('button[type="submit"]').prop('disabled', false);
                    form.find('button[type="submit"]').text('Submit');
                },
                error: function(response) {
                    console.log(response);
                    //show error message
                    form.find('#biggidroid-form-message').html(
                        `
                        <p class="biggidroid-form-message-error">Something went wrong</p>
                        `
                    );
                    //restore the button
                    form.find('button[type="submit"]').prop('disabled', false);
                    form.find('button[type="submit"]').text('Submit');
                }
            });
        });
    });
</script>