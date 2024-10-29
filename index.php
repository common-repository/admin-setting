<?php
/**
* @package Easy Setting Admin
* @version 1.0
*/
/*
Plugin Name: Easy Setting Admin
Plugin URI: 
Description: Create plugin Easy Setting Admin help you hide menu custom post type and page login.
Version: 1.0
Author URI: https://nenweb.vn/
Author: Nền Web Team
*/

class Easy_Setting_Admin{

	public function __construct(){
        add_action('admin_menu', array( $this, 'register_easy_setting_admin_submenu_page' ));
        add_action('admin_init', array( $this,'setting_easy_setting_admin_remove_menus' ));

        add_action( 'login_enqueue_scripts', array( $this, 'custom_login_logo') );
        add_action( 'login_head', array( $this, 'custom_login_head_background') );
        add_filter( 'login_headerurl', array( $this, 'custom_login_headerurl') );
        add_filter( 'login_headertitle', array( $this, 'custom_login_headertitle') );

		add_action('admin_enqueue_scripts', array( $this, 'style_init' ));
		register_activation_hook( __FILE__, array( $this, 'wpa_install' ) );
		register_deactivation_hook( __FILE__, array( $this, 'wpa_uninstall') );
    }
    
    public function register_easy_setting_admin_submenu_page() {
        add_menu_page( 'Easy Setting Admin', 'Easy Setting Admin', 'manage_options', 'setting-wp-admin', array( $this,'setting_easy_setting_admin_page_callback') ); 
    }

    public function setting_easy_setting_admin_remove_menus() {
        $list_menu_remove = unserialize(get_option('list_menu_remove'));
        if($list_menu_remove) {
            foreach($list_menu_remove as $menu) {
                remove_menu_page($menu); 
            }
        }
    }

    public function setting_easy_setting_admin_page_callback() {
        ?>
        <div class="wrap">
            <div class="admin-main">
                <h2>Setting Admin Menu</h2>
                <div class="admin-content">
                    <?php 
                    if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' && $_POST['save'] == 'Update') {
                        $list_menu = array();
                        if($_POST['menu']){
                            foreach ($_POST['menu'] as $menu_item) {
                                $list_menu[] = $menu_item;
                                remove_menu_page($menu_item); 
                            }
                            update_option('list_menu_remove', serialize($list_menu));
                            wp_redirect(menu_page_url('setting-wp-admin').'&status=ok');
                            exit;
                        } else {
                            wp_redirect(menu_page_url('setting-wp-admin').'&status=no');
                            exit;
                        }
                    } 
                    if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' && $_POST['default'] == 'Set Default Setting') {
                        update_option('list_menu_remove', '');
                        wp_redirect(menu_page_url('setting-wp-admin').'&status=default');
                        exit;
                    } 
                    ?>
                    <?php if(isset($_GET['status']) && !empty($_GET['status'])) {
                        if($_GET['status'] == 'ok') {
                            echo "<p class='status'>Update successful</p>";
                        } else if($_GET['status'] == 'default') {
                            echo "<p class='status'>The updated menu returns to the default.</p>";
                        } else if($_GET['status'] == 'no') {
                            echo "<p class='status'>There was an error during the update process, please try again.</p>";
                        }
                    } ?>
                    <form method="post">
                        <?php //wp_nonce_field( 'menu-remove' ); ?>
                        <div class="control-button">
                            <input name="default" type="submit" class="button button-info button-large" value="Set Default Setting" >
                            <input name="save" type="submit" class="button button-primary button-large" id="publish" value="Update">
                        </div>
                        <table class="wp-list-table widefat fixed striped posts table-second-td">
                            <tr>
                                <th><strong>Menu Of Side Bar</strong></th>
                                <th><strong>Status</strong></th> 
                            </tr>
                            <?php 
                            $list_menu_all = unserialize(get_option('list_menu'));
                            $list_menu_remove = unserialize(get_option('list_menu_remove'));
                            if($list_menu_all) {
                                foreach($list_menu_all as $item) {  
                                    if(($item[2]) != 'setting-wp-admin' && !empty($item[0])) { ?>
                                        <tr>
                                            <th>
                                                <?php 
                                                if(strpos( $item[6], 'http://' ) === 0 || strpos( $item[6], 'https://' ) === 0) { ?>
                                                    <span class="wp-menu-image dashicons-before">
                                                        <img src="<?php echo $item[6]; ?>" alt="icon image">
                                                    </span>
                                                <?php } else { ?>
                                                    <span class="dashicons-before <?php echo $item[6]; ?>"></span>
                                                <?php } ?>
                                                <span><?php echo preg_replace('/[0-9]+/', '', $item[0]); ?></span>
                                            </th>
                                            <th>
                                            <?php if(in_array($item[2], $list_menu_remove)) { ?>
                                                <input type="checkbox" name="menu[<?php echo $item[1]; ?>]" id="<?php echo $item[1]; ?>" value="<?php echo $item[2]; ?>" checked>
                                            <?php } else { ?>
                                                <input type="checkbox" name="menu[<?php echo $item[1]; ?>]" id="<?php echo $item[1]; ?>" value="<?php echo $item[2]; ?>">
                                            <?php } ?>
                                            </th> 
                                        </tr>
                                    <?php }
                                }
                            }
                            ?>
                        </table>
                    </form>
                </div>
            </div>
            <div class="admin-login">
            <h2>Setting Admin Login</h2>
                <div class="login-content">
                    <?php 
                    if(strtoupper($_SERVER['REQUEST_METHOD']) === 'POST' && $_POST['save-login'] == 'Update') {
                        if(check_admin_referer('login-update')){
                            
                            $logo_login = esc_url_raw($_POST['logo-login']);
                            if($logo_login) {
                                update_option('logo_login', $logo_login);
                            }
                            $background_login = sanitize_hex_color($_POST['background-login']);
                            if($background_login) {
                                update_option('background_login', $background_login);
                            }
                            $logo_login_url = esc_url_raw($_POST['logo-login-url']);
                            if($logo_login_url) {
                                update_option('logo_login_url', $logo_login_url);
                            }
                            $logo_login_title = sanitize_text_field($_POST['logo-login-title']);
                            if($logo_login_title) {
                                update_option('logo_login_title', $logo_login_title);
                            }
                            wp_redirect(menu_page_url('setting-wp-admin').'&status-login=ok');
                            exit;
                        } else {
                            wp_redirect(menu_page_url('setting-wp-admin').'&status-login=no');
                            exit;
                        }
                    } ?>
                    <?php if(isset($_GET['status-login']) && !empty($_GET['status-login'])) {
                        if($_GET['status-login'] == 'ok') {
                            echo "<p class='status'>Update successful</p>";
                        } else if($_GET['status-login'] == 'no') {
                            echo "<p class='status'>There was an error during the update process, please try again.</p>";
                        }
                    } ?>
                    <form method="post">
                        <?php wp_nonce_field( 'login-update' ); ?>
                        <p><label for="logo-login">Logo Login:</label><input type="text" name="logo-login" id="logo-login" placeholder="Size images: 84px - 84px and https link" value="<?php echo get_option('logo_login'); ?>"></p>
                        <p><label for="background-login">Background Login:</label><input type="color" name="background-login" id="background-login" value="<?php echo get_option('background_login'); ?>"></p>
                        <p><label for="logo-login-url">Logo Login Url:</label><input type="text" name="logo-login-url" id="logo-login-url" value="<?php echo get_option('logo_login_url'); ?>"></p>
                        <p><label for="logo-login-title">Logo Login Title:</label><input type="text" name="logo-login-title" id="logo-login-title" value="<?php echo get_option('logo_login_title'); ?>"></p>
                        <input name="save-login" type="submit" class="button button-primary button-large" id="publish" value="Update">
                    </form>
                </div>
            </div>
        </div>
        <?php
    }

    public function custom_login_logo() {
        $logo_login = get_option('logo_login');
        if (!empty($logo_login)) { ?>
            <style type="text/css">
                #login h1 a, .login h1 a {
                    background-image: url(<?php echo $logo_login; ?>);
                    background-size: cover;
                    background-repeat: no-repeat;
                    margin-bottom: 0px;
                }
            </style>
        <?php }
    }

    public function custom_login_head_background() {
        $background_login = get_option('background_login');
        if (!empty($background_login)) {
            echo '<style>
            body.login {
                background: '.$background_login.';
            }
            </style>';
        }
    }

    public function custom_login_headerurl() {
        $logo_login_url = get_option('logo_login_url');
        if (!empty($logo_login_url)) {
            return $logo_login_url;
        } else {
            return get_bloginfo( 'url' );
        }
    }

    public function custom_login_headertitle() {
        $logo_login_title = get_option('logo_login_title');
        if (!empty($logo_login_title)) {
            return $logo_login_title;
        } else {
            return get_bloginfo( 'name' );
        }
    }

	public function style_init() {
        wp_enqueue_style( 'wpa-styles', plugin_dir_url( __FILE__ ) .'assets/css/wpa-styles.css');
		wp_enqueue_script( 'script-wpa', plugin_dir_url( __FILE__ ) . 'assets/js/wpa.js', array(), '1.0.0', true);
    }
    
	public function wpa_install() {
        global $menu;
        update_option( 'list_menu', serialize($menu));
    }

	public function wpa_uninstall() {
        update_option( 'list_menu', '');
    }

}
new Easy_Setting_Admin;
?>