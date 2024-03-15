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
        //create shortcode
        add_shortcode('biggidroid-contact-form', array($this, 'biggidroidContactFormShortcode'));
        //column table
        add_filter('manage_biggidroid_contact_posts_columns', array($this, 'addCustomColumns'));
        //column table content
        add_action('manage_biggidroid_contact_posts_custom_column', array($this, 'addCustomColumnsContent'), 10, 2);
    }

    /**
     * editFormAfterTitleBody
     * 
     */
    public function editFormAfterTitleBody($post)
    {
        //check if post type is biggidroid_contact
        if ($post->post_type == 'biggidroid_contact') {
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
