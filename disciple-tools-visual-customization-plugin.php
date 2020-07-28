<?php

/**
 * Plugin Name: Disciple Tools - Visual Customization
 * Plugin URI: 
 * Description: Disciple Tools - Visual Customization is intended to manage Disciple Tools Theme's styles settings like (Colors, Fonts, Icons and Images).
 * Version:  0.1.0
 * Author URI: 
 * GitHub Plugin URI: 
 * Requires at least: 
 * Tested up to: 
 *
 * @package Disciple_Tools
 * @link    https://github.com/DiscipleTools
 * @license GPL-2.0 or later
 *          https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
$dt_visual_cutomization_required_dt_theme_version = '0.28.0';

/**
 * Gets the instance of the `DT_Visual_Customization_Plugin` class.
 *
 * @since  0.1
 * @access public
 * @return object|bool
 */
function dt_visual_customization_plugin()
{
    global $dt_visual_cutomization_required_dt_theme_version;
    $wp_theme = wp_get_theme();
    $version = $wp_theme->version;

    /*
     * Check if the Disciple.Tools theme is loaded and is the latest required version
     */
    $is_theme_dt = strpos($wp_theme->get_template(), "disciple-tools-theme") !== false || $wp_theme->name === "Disciple Tools";
    if ($is_theme_dt && version_compare($version, $dt_visual_cutomization_required_dt_theme_version, "<")) {
        add_action('admin_notices', 'dt_visual_cutomization_plugin_hook_admin_notice');
        add_action('wp_ajax_dismissed_notice_handler', 'dt_hook_ajax_notice_handler');
        return false;
    }
    if (!$is_theme_dt) {
        return false;
    }
    /**
     * Load useful function from the theme
     */
    if (!defined('DT_FUNCTIONS_READY')) {
        require_once get_template_directory() . '/dt-core/global-functions.php';
    }
    /*
     * Don't load the plugin on every rest request. Only those with the 'sample' namespace
     */
    $is_rest = dt_is_rest();
    //@todo change 'sample' if you want the plugin to be set up when using rest api calls other than ones with the 'sample' namespace
    if (!$is_rest) {
        return DT_Visual_Customization_Plugin::get_instance();
    }
    // @todo remove this "else if", if not using rest-api.php
    //else if ( strpos( dt_get_url_path(), 'dt_visual_customization_plugin' ) !== false ) {
    //    return DT_Visual_Customization_Plugin::get_instance();
    //}
    // @todo remove if not using a post type
    else if (strpos(dt_get_url_path(), 'visual_customization_post_type') !== false) {
        return DT_Visual_Customization_Plugin::get_instance();
    }
}
add_action('after_setup_theme', 'dt_visual_customization_plugin');


// Add filters to hook theme 'apply_filters' methods
add_filter('dt_default_logo', array('dt_visual_customization_plugin', 'set_logo_uri'));
add_action('wp_enqueue_scripts', array('dt_visual_customization_plugin', 'vc_styles'));

/**
 * Singleton class for setting up the plugin.
 *
 * @since  0.1
 * @access public
 */
class DT_Visual_Customization_Plugin
{

    /**
     * Declares public variables
     *
     * @since  0.1
     * @access public
     * @return object
     */
    public $token;
    public $version;
    public $dir_path = '';
    public $dir_uri = '';
    public $img_uri = '';
    public $includes_path;

    /**
     * Returns the instance.
     *
     * @since  0.1
     * @access public
     * @return object
     */
    public static function get_instance()
    {

        static $instance = null;

        if (is_null($instance)) {
            $instance = new dt_visual_customization_plugin();
            $instance->setup();
            $instance->includes();
            $instance->setup_actions();
        }
        return $instance;
    }

    /**
     * Constructor method.
     *
     * @since  0.1
     * @access private
     * @return void
     */
    private function __construct()
    {
    }

    /**
     * Loads files needed by the plugin.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function includes()
    {
        require_once('includes/admin/admin-menu-and-tabs.php');
    }

    /**
     * Sets up globals.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function setup()
    {

        // Main plugin directory path and URI.
        $this->dir_path     = trailingslashit(plugin_dir_path(__FILE__));
        $this->dir_uri      = trailingslashit(plugin_dir_url(__FILE__));

        // Plugin directory paths.
        $this->includes_path      = trailingslashit($this->dir_path . 'includes');

        // Plugin directory URIs.
        $this->img_uri      = trailingslashit($this->dir_uri . 'img');

        // Admin and settings variables
        $this->token             = 'dt_visual_customization_plugin';
        $this->version             = '0.1';



        // sample rest api class
        require_once('includes/rest-api.php');

        // sample post type class
        //require_once( 'includes/post-type.php' );

        // custom site to site links
        require_once('includes/custom-site-to-site-links.php');
    }

    /**
     * Sets up main plugin actions and filters.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    private function setup_actions()
    {

        if (is_admin()) {
            // Check for plugin updates
            if (!class_exists('Puc_v4_Factory')) {
                require(get_template_directory() . '/dt-core/libraries/plugin-update-checker/plugin-update-checker.php');
            }
            /**
             * Below is the publicly hosted .json file that carries the version information. This file can be hosted
             * anywhere as long as it is publicly accessible. You can download the version file listed below and use it as
             * a template.
             * Also, see the instructions for version updating to understand the steps involved.
             * @see https://github.com/DiscipleTools/disciple-tools-version-control/wiki/How-to-Update-the-Starter-Plugin
             */
            //            @todo enable this section with your own hosted file
            //            $hosted_json = "https://raw.githubusercontent.com/DiscipleTools/disciple-tools-version-control/master/disciple-tools-visual-customization-plugin-version-control.json";
            //            Puc_v4_Factory::buildUpdateChecker(
            //                $hosted_json,
            //                __FILE__,
            //                'disciple-tools-visual-customization-plugin'
            //            );
        }

        // Internationalize the text strings used.
        add_action('init', array($this, 'i18n'), 2);

        if (is_admin()) {
            // adds links to the plugin description area in the plugin admin list.
            add_filter('plugin_row_meta', [$this, 'plugin_description_links'], 10, 4);
        }
    }

    /**
     * Filters the array of row meta for each/specific plugin in the Plugins list table.
     * Appends additional links below each/specific plugin on the plugins page.
     *
     * @access  public
     * @param   array       $links_array            An array of the plugin's metadata
     * @param   string      $plugin_file_name       Path to the plugin file
     * @param   array       $plugin_data            An array of plugin data
     * @param   string      $status                 Status of the plugin
     * @return  array       $links_array
     */
    public function plugin_description_links($links_array, $plugin_file_name, $plugin_data, $status)
    {
        if (strpos($plugin_file_name, basename(__FILE__))) {
            // You can still use `array_unshift()` to add links at the beginning.

            $links_array[] = '<a href="https://disciple.tools">Disciple.Tools Community</a>'; // @todo replace with your links.

            // add other links here
        }

        return $links_array;
    }

    function set_logo_uri($logoUri)
    {
        $logoPath = empty(get_option('vc_logo')) ? $logoUri : wp_upload_dir()["baseurl"] . get_option('vc_logo');
        return $logoPath;
    }

    function vc_styles()
    {
        wp_enqueue_style('vc-styles', get_template_directory_uri() . '/dt-assets/scss/style.scss');
        wp_add_inline_style('vc-styles', "

            body {
                font-family: " . get_option('vc_font_style') . " !important;
                background-color: ". get_option('vc_color_background'). " !important;
            }

            .top-bar, 
            .top-bar ul,
            #top-bar-menu .dropdown.menu a,
            #list-filter-tabs .is-active a, #list-filter-tabs .is-active a:focus {
                background-color: ".get_option('vc_color_topbar')." !important;
                color: #ffffff !important;
            }

            .top-bar .active a {
                background-color: ".get_option('vc_color_topbar')." !important;
                filter: brightness(0.85);
            }

            .list-name-link, .accordion-title {
                color: ".get_option('vc_color_topbar')." !important;
            }

            .button, .button.disabled, 
            .button.disabled:focus, 
            .button.disabled:hover, 
            .button[disabled], 
            .button[disabled]:focus, 
            .button[disabled]:hover {
                background-color: ".get_option('vc_color_primary')." !important;
            }

            .button.clear, 
            .button.clear.disabled, 
            .button.clear.disabled:focus, 
            .button.clear.disabled:hover, 
            .button.clear[disabled], 
            .button.clear[disabled]:focus, 
            .button.clear[disabled]:hover {
                background-color: ".get_option('vc_color_secondary')." !important;
            }

            .dt-green, a.dt-green:hover {
                background-color: ".get_option('vc_color_success')." !important;
            }

            input.dt-switch:checked+label,
            input:checked~.switch-paddle {
                background: ".get_option('vc_color_switch')." !important;
            }

            a, a:focus, a:hover {
                color: ".get_option('vc_color_link')." !important;
            }

            .title, .section-header {
                color: ".get_option('vc_color_titles')." !important;
            }

            .detail-notification-box {
                background-color: ".get_option('vc_color_danger')." !important;
            }

        ");
    }

    /**
     * Method that runs only when the plugin is activated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function activation()
    {

        // Confirm 'Administrator' has 'manage_dt' privilege. This is key in 'remote' configuration when
        // Disciple Tools theme is not installed, otherwise this will already have been installed by the Disciple Tools Theme
        $role = get_role('administrator');
        if (!empty($role)) {
            $role->add_cap('manage_dt'); // gives access to dt plugin options
        }

        add_option('vc_color_topbar', '#3f729b');
        add_option('vc_font_style', 'Arial');
        add_option('vc_logo', '');
        add_option('vc_color_primary', '#007bff');
        add_option('vc_color_secondary', '#6c757d');
        add_option('vc_color_success', '#28a745');
        add_option('vc_color_danger', '#dc3545');
        add_option('vc_color_warning', '#ffc107');
        add_option('vc_color_info', '#17a2b8');
        add_option('vc_color_switch', '#007bff');
        add_option('vc_color_link', '#007bff');
        add_option('vc_color_titles', '#007bff');
        add_option('vc_color_background', '#fefefe');
        add_option('vc_color_tiles', '#fefefe');
    }

    /**
     * Method that runs only when the plugin is deactivated.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public static function deactivation()
    {
        delete_option('dismissed-dt-visual-customization');
        delete_option('vc_color_topbar');
        delete_option('vc_font_style');
        delete_option('vc_logo');
        delete_option('vc_color_primary');
        delete_option('vc_color_secondary');
        delete_option('vc_color_success');
        delete_option('vc_color_danger');
        delete_option('vc_color_warning');
        delete_option('vc_color_info');
        delete_option('vc_color_switch');
        delete_option('vc_color_link');
        delete_option('vc_color_titles');
        delete_option('vc_color_background');
        delete_option('vc_color_tiles');

        remove_filter('dt_default_logo', 'set_logo_uri');
    }

    /**
     * Loads the translation files.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function i18n()
    {
        load_plugin_textdomain('dt_visual_customization_plugin', false, trailingslashit(dirname(plugin_basename(__FILE__))) . 'languages');
    }

    /**
     * Magic method to output a string if trying to use the object as a string.
     *
     * @since  0.1
     * @access public
     * @return string
     */
    public function __toString()
    {
        return 'dt_visual_customization_plugin';
    }

    /**
     * Magic method to keep the object from being cloned.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, 'Whoah, partner!', '0.1');
    }

    /**
     * Magic method to keep the object from being unserialized.
     *
     * @since  0.1
     * @access public
     * @return void
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, 'Whoah, partner!', '0.1');
    }

    /**
     * Magic method to prevent a fatal error when calling a method that doesn't exist.
     *
     * @param string $method
     * @param array $args
     * @return null
     * @since  0.1
     * @access public
     */
    public function __call($method = '', $args = array())
    {
        _doing_it_wrong("dt_visual_customization_plugin::" . esc_html($method), 'Method does not exist.', '0.1');
        unset($method, $args);
        return null;
    }
}
// end main plugin class

// Register activation hook.
register_activation_hook(__FILE__, ['DT_Visual_Customization_Plugin', 'activation']);
register_deactivation_hook(__FILE__, ['DT_Visual_Customization_Plugin', 'deactivation']);

function dt_visual_cutomization_plugin_hook_admin_notice()
{
    global $dt_visual_cutomization_required_dt_theme_version;
    $wp_theme = wp_get_theme();
    $current_version = $wp_theme->version;
    $message = __("'Disciple Tools - Visual Customization' plugin requires 'Disciple Tools' theme to work. Please activate 'Disciple Tools' theme or make sure it is latest version.", "dt_visual_customization_plugin");
    if ($wp_theme->get_template() === "disciple-tools-theme") {
        $message .= sprintf(esc_html__('Current Disciple Tools version: %1$s, required version: %2$s', 'dt_visual_customization_plugin'), esc_html($current_version), esc_html($dt_visual_cutomization_required_dt_theme_version));
    }
    // Check if it's been dismissed...
    if (!get_option('dismissed-dt-visual-customization', false)) { ?>
        <div class="notice notice-error notice-dt-visual-customization is-dismissible" data-notice="dt-visual-customization">
            <p><?php echo esc_html($message); ?></p>
        </div>
        <script>
            jQuery(function($) {
                $(document).on('click', '.notice-dt-visual-customization .notice-dismiss', function() {
                    $.ajax(ajaxurl, {
                        type: 'POST',
                        data: {
                            action: 'dismissed_notice_handler',
                            type: 'dt-visual-customization',
                            security: '<?php echo esc_html(wp_create_nonce('wp_rest_dismiss')) ?>'
                        }
                    })
                });
            });
        </script>
<?php }
}


/**
 * AJAX handler to store the state of dismissible notices.
 */
if (!function_exists("dt_hook_ajax_notice_handler")) {
    function dt_hook_ajax_notice_handler()
    {
        check_ajax_referer('wp_rest_dismiss', 'security');
        if (isset($_POST["type"])) {
            $type = sanitize_text_field(wp_unslash($_POST["type"]));
            update_option('dismissed-' . $type, true);
        }
    }
}
