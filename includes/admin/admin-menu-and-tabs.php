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

        //INITIAL VARIABLES
        $fontStyles = array('Comic Sans MS', 'Calibri', 'Arial');
        $wpUploadDir = wp_upload_dir();

        // FORM SUBMIT -> UPDATE OPTIONS IN DATA BASE (BEFORE GET HIM IN VIEW)
        if (isset($_POST['submit'])) {

            //global $formColorTopbar, $fontStyleValue;

            $formColorTopbar = sanitize_text_field($_POST['color-topbar']);
            $fontStyleValue = sanitize_text_field($_POST['font-style']);
            $formColorPrimary = sanitize_text_field($_POST['color-primary']);
            $formColorSecondary = sanitize_text_field($_POST['color-secondary']);
            $formColorSuccess = sanitize_text_field($_POST['color-success']);
            $formColorDanger = sanitize_text_field($_POST['color-danger']);
            $formColorWarning = sanitize_text_field($_POST['color-warning']);
            $formColorInfo = sanitize_text_field($_POST['color-info']);
            $formColorSwitch = sanitize_text_field($_POST['color-switch']);
            $formColorLink = sanitize_text_field($_POST['color-link']);
            $formColorTitles = sanitize_text_field($_POST['color-titles']);
            $formColorBackground = sanitize_text_field($_POST['color-background']);
            $formColorTiles = sanitize_text_field($_POST['color-tiles']);

            update_option('vc_color_topbar', $formColorTopbar);
            update_option('vc_font_style', $fontStyleValue);
            update_option('vc_color_primary', $formColorPrimary);
            update_option('vc_color_secondary', $formColorSecondary);
            update_option('vc_color_success', $formColorSuccess);
            update_option('vc_color_danger', $formColorDanger);
            update_option('vc_color_warning', $formColorWarning);
            update_option('vc_color_info', $formColorInfo);
            update_option('vc_color_switch', $formColorSwitch);
            update_option('vc_color_link', $formColorLink);
            update_option('vc_color_titles', $formColorTitles);
            update_option('vc_color_background', $formColorBackground);
            update_option('vc_color_tiles', $formColorTiles);
        }

        if (isset($_FILES['logo'])) {
            $file = $_FILES['logo'];
            $imageSize = getimagesize($file['tmp_name']);
            if ($imageSize !== false) {
                $upload_overrides = array('test_form' => false);
                // Upload File
                $uploadFileResponse = wp_handle_upload($file, $upload_overrides);
                if ($uploadFileResponse && !isset($uploadFileResponse['error'])) {
                    update_option('vc_logo', $wpUploadDir["subdir"] . "/" . $file["name"]);
                    //$logoPath = $wpUploadDir["baseurl"] . get_option('vc_logo');
                } else {
                    $error = $uploadFileResponse['error'];
                    echo "Error message: " . $error . "</br>";
                }
            }
        }

        // GET OPTIONS SAVED IN DATABASE

        $wpOptionVcColorTopbar = get_option('vc_color_topbar');
        $wpOptionVcFontStyle = get_option('vc_font_style');
        $wpOptionVcColorPrimary = get_option('vc_color_primary');
        $wpOptionVcColorSecondary = get_option('vc_color_secondary');
        $wpOptionVcColorSuccess = get_option('vc_color_success');
        $wpOptionVcColorDanger = get_option('vc_color_danger');
        $wpOptionVcColorWarning = get_option('vc_color_warning');
        $wpOptionVcColorInfo = get_option('vc_color_info');
        $wpOptionVcColorSwitch = get_option('vc_color_switch');
        $wpOptionVcColorLink = get_option('vc_color_link');
        $wpOptionVcColorTitles = get_option('vc_color_titles');
        $wpOptionVcColorBackground = get_option('vc_color_background');
        $wpOptionVcColorTiles = get_option('vc_color_tiles');

        $logoPath = empty(get_option('vc_logo')) ? 'data:,' : $wpUploadDir["baseurl"] . get_option('vc_logo');


?>
        <div class="wrap">
            <h1>
                Visual Customization Settings
                <?php
                // (isset($_POST)) ? print_r($_POST) : '' 
                //echo apply_filters('dt_default_logo', get_template_directory_uri() . "/dt-assets/images/disciple-tools-logo-white.png")
                ?>
            </h1>

            <form action="" method="post" enctype="multipart/form-data" class="form-basic">


                <h2 class="title">Colors</h2>

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label>Topbar Color</label>
                            </th>
                            <td>
                                <input type="color" name="color-topbar" value="<?php echo ($wpOptionVcColorTopbar) ? $wpOptionVcColorTopbar : '' ?>" required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Primary Color</label>
                            </th>
                            <td>
                                <input type="color" name="color-primary" value="<?php echo ($wpOptionVcColorPrimary) ? $wpOptionVcColorPrimary : '' ?>" required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Secondary Color</label>
                            </th>
                            <td>
                                <input type="color" name="color-secondary" value="<?php echo ($wpOptionVcColorSecondary) ? $wpOptionVcColorSecondary : '' ?>" required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Success Color</label>
                            </th>
                            <td>
                                <input type="color" name="color-success" value="<?php echo ($wpOptionVcColorSuccess) ? $wpOptionVcColorSuccess : '' ?>" required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Danger Color</label>
                            </th>
                            <td>
                                <input type="color" name="color-danger" value="<?php echo ($wpOptionVcColorDanger) ? $wpOptionVcColorDanger : '' ?>" required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Warning Color</label>
                            </th>
                            <td>
                                <input type="color" name="color-warning" value="<?php echo ($wpOptionVcColorWarning) ? $wpOptionVcColorWarning : '' ?>" required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Info Color</label>
                            </th>
                            <td>
                                <input type="color" name="color-info" value="<?php echo ($wpOptionVcColorInfo) ? $wpOptionVcColorInfo : '' ?>" required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Switch Color</label>
                            </th>
                            <td>
                                <input type="color" name="color-switch" value="<?php echo ($wpOptionVcColorSwitch) ? $wpOptionVcColorSwitch : '' ?>" required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Link Color</label>
                            </th>
                            <td>
                                <input type="color" name="color-link" value="<?php echo ($wpOptionVcColorLink) ? $wpOptionVcColorLink : '' ?>" required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Titles Color</label>
                            </th>
                            <td>
                                <input type="color" name="color-titles" value="<?php echo ($wpOptionVcColorTitles) ? $wpOptionVcColorTitles : '' ?>" required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Background Color</label>
                            </th>
                            <td>
                                <input type="color" name="color-background" value="<?php echo ($wpOptionVcColorBackground) ? $wpOptionVcColorBackground : '' ?>" required>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label>Tiles Color</label>
                            </th>
                            <td>
                                <input type="color" name="color-tiles" value="<?php echo ($wpOptionVcColorTiles) ? $wpOptionVcColorTiles : '' ?>" required>
                            </td>
                        </tr>

                    </tbody>
                </table>

                <h2 class="title">Fonts</h2>

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label>Font Style</label>
                            </th>
                            <td>
                                <select name="font-style" required>
                                    <?php foreach ($fontStyles as $fontStyle) : ?>
                                        <option style="font-family: '<?php echo $fontStyle ?>';" value="<?php echo $fontStyle ?>" <?php if ($fontStyle == $wpOptionVcFontStyle) : ?> selected="selected" <?php endif; ?>><?php echo $fontStyle ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <h2 class="title">Images</h2>

                <table class="form-table">
                    <tbody>
                        <tr>
                            <th scope="row">
                                <label>Logo</label>
                            </th>
                            <td>
                                <img src="<?php echo $logoPath; ?>" style="width: 200px; height: 100px;" />
                                <br>
                                <input type="file" name="logo">
                            </td>
                        </tr>
                    </tbody>
                </table>

                <p class="submit">
                    <button type="submit" name="submit" class="button button-primary">Submit Form</button>
                </p>

            </form>

        </div>

<?php
    }
}
