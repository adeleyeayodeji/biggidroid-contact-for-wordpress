<?php
//check for security
if (!defined('WPINC')) {
    die('Cant access file directly');
}

//get biggidroid_form_fields
$biggidroid_form_fields = $this->getBiggiDroidFormFields($post_id);
?>
<style>
    .ui-state-active,
    .ui-widget-content .ui-state-active,
    .ui-widget-header .ui-state-active,
    a.ui-button:active,
    .ui-button:active,
    .ui-button.ui-state-active:hover {
        border: 1px solid #333;
        background: #333;
        font-weight: normal;
        color: #000;
    }

    #biggidroid-form-content {
        width: 100%;
        height: 300px;
    }

    #biggidroid-mail-body {
        height: 300px;
        width: 100%;
    }

    #biggidroid-mail-additional-headers {
        height: 70px;
        width: 100%;
    }

    .biggidroid-form input {
        width: 100%;
    }

    .biggidroid-contact-messages {
        display: flex;
        flex-direction: column;
        gap: 2px;
        margin-bottom: 10px;
    }

    .biggidroid-contact-messages label {
        font-weight: normal;
    }

    .biggidroid-contact-messages input {
        width: 100%;
        padding: 8px;
        outline: none;
    }
</style>
<div class="biggidroid-contact-tabs">
    <div id="biggidroid-contact-tabs-content">
        <ul>
            <li><a href="#tabs-form">Form</a></li>
            <li><a href="#tabs-mail">Mail</a></li>
            <li><a href="#tabs-messages">Messages</a></li>
        </ul>
        <div id="tabs-form">
            <h3>Form</h3>
            <code>
                [biggidroid_contact_subject]
                [biggidroid_contact_name]
                [biggidroid_contact_phone]
                [biggidroid_contact_email]
                [biggidroid_contact_message]
            </code>
            <p>
                You can edit the form template here.
            </p>
            <div class="biggidroid-contact-edit-area">
                <textarea name="biggidroid-form-content" id="biggidroid-form-content" placeholder="Enter form content"><?php echo esc_html($biggidroid_form_fields['biggidroid-form-content']); ?></textarea>
            </div>
        </div>
        <div id="tabs-mail">
            <h3>Mail</h3>
            <p>
                You can edit the mail template here.
            </p>
            <p>
                In the following fields, you can use these mail-tags: <br>
                <code>[your-name][your-email][your-subject][your-message]</code>
            </p>
            <div class="biggidroid-mail-settings">
                <table class="form-table biggidroid-form">
                    <tr>
                        <th>
                            <label for="biggidroid-mail-to">To:</label>
                        </th>
                        <td>
                            <input type="text" class="regular-text" name="biggidroid-mail-to" id="biggidroid-mail-to" value="<?php echo esc_html($biggidroid_form_fields['biggidroid-mail-to']); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="biggidroid-mail-from">From:</label>
                        </th>
                        <td>
                            <input type="text" class="regular-text" name="biggidroid-mail-from" id="biggidroid-mail-from" value="<?php echo esc_html($biggidroid_form_fields['biggidroid-mail-from']); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="biggidroid-mail-subject">Subject:</label>
                        </th>
                        <td>
                            <input type="text" class="regular-text" name="biggidroid-mail-subject" id="biggidroid-mail-subject" value="<?php echo esc_html($biggidroid_form_fields['biggidroid-mail-subject']); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="biggidroid-mail-additional-headers">Additional Headers:</label>
                        </th>
                        <td>
                            <textarea name="biggidroid-mail-additional-headers" id="biggidroid-mail-additional-headers" placeholder="Enter additional headers"><?php echo esc_html($biggidroid_form_fields['biggidroid-mail-additional-headers']); ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <th>
                            <label for="biggidroid-mail-body">Message body:</label>
                        </th>
                        <td>
                            <textarea name="biggidroid-mail-body" id="biggidroid-mail-body" placeholder="Enter mail body"><?php echo esc_html($biggidroid_form_fields['biggidroid-mail-body']); ?></textarea>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        <div id="tabs-messages">
            <h3>Messages</h3>
            <p>
                You can edit messages used in various situations here
            </p>
            <div class="biggidroid-contact-messages">
                <label for="biggidroid-message-success">Success message:</label>
                <input name="biggidroid-message-success" id="biggidroid-message-success" placeholder="Enter success message" value="<?php echo esc_html($biggidroid_form_fields['biggidroid-message-success']); ?>" />
            </div>
            <div class="biggidroid-contact-messages">
                <label for="biggidroid-message-error">Error message:</label>
                <input name="biggidroid-message-error" id="biggidroid-message-error" placeholder="Enter error message" value="<?php echo esc_html($biggidroid_form_fields['biggidroid-message-error']); ?>" />
            </div>
        </div>
    </div>
</div>
<script>
    jQuery(document).ready(function($) {
        //init biggidroid-contact-tabs-content
        $("#biggidroid-contact-tabs-content").tabs();
    });
</script>