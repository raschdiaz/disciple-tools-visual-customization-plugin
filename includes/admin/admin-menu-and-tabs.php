<?php

/**
 * DT_Visual_Customization_Plugin_Menu class for the admin page
 *
 * @class       DT_Visual_Customization_Plugin_Menu
 * @version     0.1.0
 * @since       0.1.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Initialize menu class
 */
DT_Visual_Customization_Plugin_Menu::instance();

/**
 * Class DT_Visual_Customization_Plugin_Menu
 */
class DT_Visual_Customization_Plugin_Menu
{

    public $token = 'dt_visual_customization_plugin';

    private static $_instance = null;

    /**
     * DT_Visual_Customization_Plugin_Menu Instance
     *
     * Ensures only one instance of DT_Visual_Customization_Plugin_Menu is loaded or can be loaded.
     *
     * @since 0.1.0
     * @static
     * @return DT_Visual_Customization_Plugin_Menu instance
     */
    public static function instance()
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    } // End instance()


    /**
     * Constructor function.
     * @access  public
     * @since   0.1.0
     */
    public function __construct()
    {

        add_action("admin_menu", array($this, "register_menu"));
    } // End __construct()


    /**
     * Loads the subnav page
     * @since 0.1
     */
    public function register_menu()
    {
        add_menu_page(__('Extensions (DT)', 'disciple_tools'), __('Extensions (DT)', 'disciple_tools'), 'manage_dt', 'dt_extensions', [$this, 'extensions_menu'], 'dashicons-admin-generic', 59);
        add_submenu_page('dt_extensions', __('Visual Customization', 'dt_visual_customization_plugin'), __('Visual Customization', 'dt_visual_customization_plugin'), 'manage_dt', $this->token, [$this, 'content']);
    }

    /**
     * Menu stub. Replaced when Disciple Tools Theme fully loads.
     */
    public function extensions_menu()
    {
    }

    /**
     * Builds page contents
     * @since 0.1
     */
    public function content()
    {

        if (!current_user_can('manage_dt')) { // manage dt is a permission that is specific to Disciple Tools and allows admins, strategists and dispatchers into the wp-admin
            wp_die(esc_attr__('You do not have sufficient permissions to access this page.'));
        }

        $wpOptionVcPrimaryColor = get_option('vc_primary_color');
        $wpOptionVcFontStyle = get_option('vc_font_style');
        $fontStyles = array('Comic Sans MS', 'Calibri', 'Arial');

        if (isset($_POST['primary-color'])) {
            global $themePrimaryColorValue, $fontStyleValue;
            $themePrimaryColorValue = sanitize_text_field($_POST['primary-color']);
            $fontStyleValue = sanitize_text_field($_POST['font-style']);
            update_option('vc_primary_color', $themePrimaryColorValue);
            update_option('vc_font_style', $fontStyleValue);
            $wpOptionVcPrimaryColor = get_option('vc_primary_color');
            $wpOptionVcFontStyle = get_option('vc_font_style');
        }

?>
        <div class="wrap">
            <div id="poststuff">
                <div id="post-body" class="metabox-holder columns-2">
                    <form action="" method="POST" class="form-basic">

                        <div class="form-title-row">
                            <h1>Visual Customization Settings <?php (isset($_POST)) ? print_r($_POST) : '' ?></h1>
                        </div>

                        <div class="form-row">
                            <label>
                                Theme Primary Color
                            </label>
                            <input type="color" name="primary-color" value="<?php echo ($wpOptionVcPrimaryColor) ? $wpOptionVcPrimaryColor : '' ?>" required>
                        </div>

                        <div class="form-row">
                            <label>
                                Font Style
                            </label>
                            <select name="font-style" required>
                                <?php foreach ($fontStyles as $fontStyle) : ?>
                                    <option style="font-family: '<?php echo $fontStyle ?>';" value="<?php echo $fontStyle ?>" <?php if ($fontStyle == $wpOptionVcFontStyle) : ?> selected="selected" <?php endif; ?>><?php echo $fontStyle ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-row">
                            <button type="submit">Submit Form</button>
                        </div>

                    </form>
                </div>
            </div>

        </div>

<?php
    }
}
