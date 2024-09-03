<?php
//check for security
if (!defined('WPINC')) {
    die('Cant access file directly');
}


/**
 * BiggiDroid Contact Form
 * @author Adeleye Ayodeji
 * @link https://adeleyeayodeji.com
 * @since 1.0.0
 * 
 */
class BiggiDroidContactForm
{
    /**
     * BiggiDroidContactForm instance
     * @var BiggiDroidContactForm
     */
    private static $instance;

    /**
     * Get BiggiDroidContactForm instance
     * @return BiggiDroidContactForm
     */
    public static function getInstance()
    {
        //check if instance is null
        if (self::$instance == null) {
            //create new instance
            self::$instance = new self();
        }
        //return instance
        return self::$instance;
    }

    /**
     * Constructor
     * 
     */
    public function __construct()
    {
        //init the plugin
        add_action('init', array($this, 'registerPostType'));
        //add action edit_form_after_title
        add_action('edit_form_after_title', array($this, 'editFormAfterTitle'), 10);
        //add action edit_form_after_title for body
        add_action('edit_form_after_title', array($this, 'editFormAfterTitleBody'), 11);
        //add admin script
        add_action('admin_enqueue_scripts', array($this, 'adminEnqueueScripts'));
        //init
        add_action('init', array($this, 'init'));
        //save post action
        add_action('save_post', array($this, 'savePostGeneratedId'), 10, 3);
        //save post
        add_action('save_post', array($this, 'savePostOthersData'), 11, 3);
        //create shortcode
        add_shortcode('biggidroid-contact-form', array($this, 'biggidroidContactFormShortcode'));
        //column table
        add_filter('manage_biggidroid_contact_posts_columns', array($this, 'addCustomColumns'));
        //column table content
        add_action('manage_biggidroid_contact_posts_custom_column', array($this, 'addCustomColumnsContent'), 10, 2);
        //add frontend assets
        add_action('wp_enqueue_scripts', array($this, 'frontendAssetsScripts'));
        //add multiple shortcode
        $this->addMultipleShortcode();
        //add ajax biggidroid_send_message
        add_action('wp_ajax_biggidroid_send_message', array($this, 'biggidroidSendMessage')); //send with user login
        add_action('wp_ajax_nopriv_biggidroid_send_message', array($this, 'biggidroidSendMessage')); //send without user login
    }

    /**
     * biggidroidSendMessage
     * 
     */
    public function biggidroidSendMessage()
    {
        try {
            //get the nonce data
            if (!wp_verify_nonce($_POST['nonce'], 'biggidroid-contact-message')) {
                wp_send_json_error([
                    'message' => 'Invalid nonce, please reload the page'
                ]);
            }
            //get the form field
            $biggidroid_subject = sanitize_text_field($_POST['biggidroid-subject']);
            $biggidroid_name = sanitize_text_field($_POST['biggidroid-name']);
            $biggidroid_email = sanitize_email($_POST['biggidroid-email']);
            $biggidroid_phone = sanitize_text_field($_POST['biggidroid-phone']);
            $biggidroid_message = sanitize_textarea_field($_POST['biggidroid-message']);
            //get the post id
            $post_id = sanitize_text_field($_POST['biggidroid-post-id']);

            //format the message
            $message = $this->biggidroidMessageFormat([
                'name' => $biggidroid_name,
                'email' => $biggidroid_email,
                'phone' => $biggidroid_phone,
                'message' => $biggidroid_message,
                'subject' => $biggidroid_subject,
                'post_id' => $post_id
            ]);
        } catch (\Exception $e) {
            //log to debug
            error_log("Biggidroid contact error: " . $e->getMessage());
            wp_send_json_error([
                'message' => 'Something went wrong: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * BiggiDroid Message Format
     * @param $args array
     * 
     * @return string
     */
    public function biggidroidMessageFormat($args): string
    {
        //extract
        extract($args); //create variables from the array
        //get saved form fields
        $form_fields = $this->getBiggiDroidFormFields($post_id);
        return "";
    }

    /**
     * Get BiggiDroid Form Fields
     * @param int $post_id
     * 
     * @return array
     */
    public function getBiggiDroidFormFields($post_id): array
    {
        //create default array
        $biggidroid_default_array = [
            'biggidroid-form-content' => '[biggidroid_contact_subject] 

[biggidroid_contact_name] 

[biggidroid_contact_phone]

[biggidroid_contact_email] 

[biggidroid_contact_message]',
            'biggidroid-mail-to' => '[_site_admin_email]',
            'biggidroid-mail-from' => '[_site_title]',
            'biggidroid-mail-subject' => '[_site_title] - [your-subject]',
            'biggidroid-mail-additional-headers' => 'Reply-To: [your-email]',
            'biggidroid-mail-body' => 'From: [your-name] [your-email]
Subject: [your-subject]

Message Body:
[your-message]

-- 
This is a notification that a contact form was submitted on your website ([_site_title] [_site_url]).',
            'biggidroid-message-success' => 'Message sent!',
            'biggidroid-message-error' => 'Something went wrong.',
        ];

        try {
            //get saved form fields
            $form_fields = get_post_meta($post_id, 'biggidroid_form_fields', true);
            //check if form fields is empty, if empty return default form fields
            if (empty($form_fields)) {
                //return default form fields
                return $biggidroid_default_array;
            }
            //return form fields
            return $form_fields;
        } catch (\Exception $th) {
            //log to debug
            error_log("Biggidroid contact error: " . $th->getMessage());
            //return default form fields
            return $biggidroid_default_array;
        }
    }

    /**
     * addMultipleShortcode
     * 
     */
    function addMultipleShortcode(): void
    {
        //shortcodes
        $shortcodes = [
            'name' => 'nameShortCode',
            'phone' => 'phoneShortCode',
            'email' => 'emailShortCode',
            'message' => 'messageShortCode',
            'subject' => 'subjectShortCode'
        ];
        //loop through shortcodes
        foreach ($shortcodes as $shortcode => $callback) {
            //add shortcode
            add_shortcode(BIGGIDROID_CONTACT_SHORT_CODE_PREFIX . $shortcode, array($this, $callback));
        }
    }

    function nameShortCode($attr): string
    {
        ob_start();
?>
        <div class="biggidroid-form-group">
            <label for="name">Name</label>
            <input type="text" name="biggidroid-name" id="biggidroid_name" placeholder="Enter your name">
        </div>
    <?php
        return ob_get_clean();
    }

    function phoneShortCode(): string
    {
        ob_start();
    ?>
        <div class="biggidroid-form-group">
            <label for="phone">Phone Number</label>
            <input type="text" name="biggidroid-phone" id="biggidroid_phone" placeholder="Enter your phone">
        </div>
    <?php
        return ob_get_clean();
    }

    function emailShortCode(): string
    {
        ob_start();
    ?>
        <div class="biggidroid-form-group">
            <label for="email">Email</label>
            <input type="email" name="biggidroid-email" id="biggidroid_email" placeholder="Enter your email">
        </div>
    <?php
        return ob_get_clean();
    }

    function messageShortCode(): string
    {
        ob_start();
    ?>
        <div class="biggidroid-form-group">
            <label for="message">Message</label>
            <textarea name="biggidroid-message" id="" cols="30" rows="10" placeholder="Enter your message"></textarea>
        </div>
    <?php
        return ob_get_clean();
    }

    function subjectShortCode(): string
    {
        ob_start();
    ?>
        <div class="biggidroid-form-group">
            <label for="subject">Subject</label>
            <input type="text" name="biggidroid-subject" id="biggidroid_subject" placeholder="Enter your subject">
        </div>
<?php
        return ob_get_clean();
    }

    /**
     * frontendAssetsScripts
     * 
     */
    function frontendAssetsScripts(): void
    {
        //add styles
        wp_enqueue_style('biggidroid-frontend-assets', BIGGIDROID_CONTACT_ASSETS_URL . 'css/frontend-styles.css', [], BIGGIDROID_CONTACT_PLUGIN_VERSION);
    }

    /**
     * editFormAfterTitleBody
     * 
     */
    public function editFormAfterTitleBody($post)
    {
        //check if post type is biggidroid_contact
        if ($post->post_type == 'biggidroid_contact') {
            $post_id = $post->ID;
            //ob start
            ob_start();
            //include the file
            include BIGGIDROID_CONTACT_PLUGIN_DIR . 'templates/edit-form-after-title-body.php';
            //echo the output
            echo ob_get_clean();
        }
    }

    /**
     * addCustomColumns
     */
    public function addCustomColumns($columns)
    {
        //unset date
        unset($columns['date']);
        //add shortcode
        $columns['shortcode'] = 'Shortcode';
        //add author
        $columns['author'] = 'Author';
        //add date
        $columns['date'] = 'Date';
        //return columns
        return $columns;
    }

    /**
     * addCustomColumnsContent
     */
    public function addCustomColumnsContent($column, $post_id)
    {
        //check for shortcode
        switch ($column) {
            case 'shortcode':
                printf(
                    '[biggidroid-contact-form id="%s" title="%s"]',
                    $post_id,
                    get_the_title($post_id)
                );
                break;
            case 'author':
                echo esc_html(get_the_author_meta('display_name', get_post_field('post_author', $post_id)));
                break;
        }
    }

    /**
     * biggidroidContactFormShortcode
     */
    public function biggidroidContactFormShortcode($atts)
    {
        //extract
        extract(
            shortcode_atts(
                [
                    'id' => '',
                    'title' => ''
                ],
                $atts
            )
        );
        //check for id
        if (!$id) {
            //do nothing
            return;
        }
        //check for title
        if (!$title) {
            $title = 'BiggiDroid Contact Form';
        }
        //get the post id
        $postData = $this->getPostDataByGeneratedId($id);
        //post id
        $post_id = $postData->post_id;
        //get the template
        ob_start();
        include_once BIGGIDROID_CONTACT_PLUGIN_DIR . 'templates/biggidroid-frontend-view.php';
        $htmlview = ob_get_clean();
        return $htmlview;
    }

    /**
     * savePostGeneratedId
     */
    public function savePostGeneratedId($post_id, $post, $update)
    {
        //check if post type is biggidroid_contact
        if ($post->post_type == 'biggidroid_contact') {
            //check for checkGeneratedId
            if ($this->checkGeneratedId($post_id)) {
                return;
            }
            //generate id
            $generated_id = substr(md5($post_id . time()), 0, 7);
            //table
            global $wpdb;
            $table = $wpdb->prefix . 'biggidroid_contacts';
            //insert
            $wpdb->insert(
                $table,
                [
                    'post_id' => $post_id,
                    'generated_id' => $generated_id
                ]
            );
        }
    }

    /**
     * savePostOthersData
     * 
     */
    function savePostOthersData($post_id, $post, $update): void
    {
        //check if post type is biggidroid_contact
        if ($post->post_type == 'biggidroid_contact') {
            //collect all form fields
            $form_fields = []; //initialize form fields array
            //loop through $_POST
            foreach ($_POST as $key => $value) {
                //if key is matching biggidroid-
                if (strpos($key, 'biggidroid-') !== false) {
                    //check if key is matching biggidroid-form-content
                    switch ($key) {
                        case 'biggidroid-form-content':
                            //pass the value to the form_fields array
                            $form_fields[$key] = sanitize_textarea_field($value);
                            break;
                            //biggidroid-mail-additional-headers
                        case 'biggidroid-mail-additional-headers':
                            //pass the value to the form_fields array
                            $form_fields[$key] = sanitize_textarea_field($value);
                            break;
                            //biggidroid-mail-body
                        case 'biggidroid-mail-body':
                            //pass the value to the form_fields array
                            $form_fields[$key] = sanitize_textarea_field($value);
                            break;
                        default:
                            //add to form fields
                            $form_fields[$key] = sanitize_text_field($value);
                            break;
                    }
                }
            }
            //update post meta
            update_post_meta($post_id, 'biggidroid_form_fields', $form_fields);
        }
    }

    /**
     * init
     */
    public function init()
    {
        try {
            global $wpdb;
            //table
            $table = $wpdb->prefix . 'biggidroid_contacts';
            //check if table exists
            if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
                //sql
                $sql = "CREATE TABLE $table (
                    id INT(11) NOT NULL AUTO_INCREMENT,
                    post_id INT(11) NOT NULL,
                    generated_id VARCHAR(100) NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id)
                )";
                //create table
                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
            }
        } catch (\Exception $e) {
            error_log("Biggidroid contact error: " . $e->getMessage());
        }
    }

    /**
     * adminEnqueueScripts
     */
    public function adminEnqueueScripts($hook)
    {
        //check if post type is biggidroid_contact
        if ($hook == 'post-new.php' || $hook == 'post.php') {
            global $post;
            if ($post->post_type == 'biggidroid_contact') {
                //enqueue style
                wp_enqueue_style(
                    'biggidroid-contact-admin-style',
                    BIGGIDROID_CONTACT_ASSETS_URL . 'css/biggidroid-style.css',
                    [],
                    BIGGIDROID_CONTACT_PLUGIN_VERSION
                );
                //jquery ui biggidroid css
                wp_enqueue_style('jquery-ui-biggidroid', BIGGIDROID_CONTACT_ASSETS_URL . 'css/jquery-ui.css');
                //jquery ui biggidroid js
                wp_enqueue_script(
                    'jquery-ui-biggidroid',
                    BIGGIDROID_CONTACT_ASSETS_URL . 'js/jquery-ui.js',
                    ['jquery'],
                    BIGGIDROID_CONTACT_PLUGIN_VERSION,
                    true
                );
            }
        }
    }

    /**
     * editFormAfterTitle
     */
    public function editFormAfterTitle($post)
    {
        //check if post type is biggidroid_contact
        if ($post->post_type == 'biggidroid_contact') {
            //check for checkGeneratedId
            $checkforid = $this->checkGeneratedId($post->ID);
            if (!$checkforid) {
                //do nothing
                return;
            }
            //ob start
            ob_start();
            //include the file
            include BIGGIDROID_CONTACT_PLUGIN_DIR . 'templates/edit-form-after-title.php';
            //echo the output
            echo ob_get_clean();
        }
    }

    /**
     * Check for generated_id
     * @param int $post_id
     * 
     * @return mixed|bool
     */
    public function checkGeneratedId($post_id)
    {
        global $wpdb;
        //table
        $table = $wpdb->prefix . 'biggidroid_contacts';
        //sql
        $sql = $wpdb->prepare(
            "SELECT * FROM $table WHERE post_id = %d",
            $post_id
        );
        //get results
        $results = $wpdb->get_results($sql);
        //check if results
        if ($results) {
            return $results[0];
        }
        return false;
    }

    /**
     * Get post data by generated id
     * @param int $generated_id
     * 
     * @return mixed|bool
     */
    public function getPostDataByGeneratedId($generated_id)
    {
        global $wpdb;
        //table
        $table = $wpdb->prefix . 'biggidroid_contacts';
        //sql
        $sql = $wpdb->prepare(
            "SELECT * FROM $table WHERE generated_id = %d",
            $generated_id
        );
        //get results
        $results = $wpdb->get_results($sql);
        //check if results
        if ($results) {
            return $results[0];
        }
        return false;
    }

    /**
     * Register post type
     */
    public function registerPostType()
    {

        //$labels
        $labels = [
            'name' => "Contact",
            'singular_name' => "Contact",
            'menu_name' => "BiggiDroid Contact",
            'name_admin_bar' => "BiggiDroid Contact",
            'add_new' => "Add New Contact",
            'add_new_item' => "Add New Contact",
            'new_item' => "New Contact",
            'edit_item' => "Edit Contact",
            'view_item' => "View Contact",
            'all_items' => "All Contact",
            'search_items' => "Search Contact",
            'parent_item_colon' => "Parent Contact:",
            'not_found' => "No Contact found.",
            'not_found_in_trash' => "No Contact found in Trash.",
        ];

        //args
        $args = [
            'label' => "BiggiDroid Contact",
            'labels' => $labels,
            'description' => "BiggiDroid Contact for WordPress",
            'show_ui' => true,
            'supports' => ['title'],
            //icon
            'menu_icon' => 'dashicons-email-alt',
        ];

        //register
        register_post_type('biggidroid_contact', $args);
    }
}
