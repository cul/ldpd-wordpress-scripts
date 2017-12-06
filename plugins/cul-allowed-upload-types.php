<?php

/*
Plugin Name: CUL Allowed Upload Types
Plugin URI:  http://library.columbia.edu
Description: Limits the set of allowed upload file extensions for CUL WP sites.
Version:     1.0
Author:      Erix
Author URI:  http://library.columbia.edu
License:     MIT
License URI: https://opensource.org/licenses/MIT
*/

function cul_allowed_upload_types($mimes) {
  $mimes = array(); //clear any default mime types

  $mimes['csv'] = "text/csv";
  $mimes['txt'] = "text/plain";

  return $mimes;
}

add_action('upload_mimes', 'cul_allowed_upload_types');

function cul_allowed_upload_file_extensions() {
  return array_keys(cul_allowed_upload_types(array()));
}

/** Set up settings page **/
if ( is_admin() ){
  add_action( 'admin_menu', 'cul_allowed_upload_types_plugin_menu' );
  add_action( 'admin_init', 'register_cul_allowed_upload_types_plugin_settings' );
}

function register_cul_allowed_upload_types_plugin_settings() {
  register_setting( 'cul-allowed-upload-types-group', 'allowed_extensions' );
}

function cul_allowed_upload_types_plugin_menu() {
  add_options_page( 'CUL Allowed Upload Types', 'CUL Upload Types', 'manage_options', 'cul-allowed-upload-types', 'cul_allowed_upload_types_plugin_options' );
}

function cul_allowed_upload_types_plugin_options() {
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }
  ?>
  <div class="wrap">
    <h2>CUL Allowed Upload Types</h2>
    <div style="border:1px solid #aaa; padding: 0 1em; border-radius:3px; background-color:#fff;">
      <pre><?php echo implode(cul_allowed_upload_file_extensions(), ', '); ?></pre>
    </div>
  </div>

  <form method="post" action="options.php">
    <?php settings_fields( 'cul-allowed-upload-types-group' ); ?>
    <?php do_settings_sections( 'cul-allowed-upload-types-group' ); ?>
    <table class="form-table">
      <tr valign="top">
      <th scope="row">Allowed Extensions:</th>
       <td><textarea name="allowed_extensions"><?php echo esc_html(get_option('allowed_extensions')); ?></textarea></td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>
  <?php
}

?>
