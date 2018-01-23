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

define('CUL_ALLOWED_EXTENSIONS_KEY', 'cul_allowed_extensions');

function cul_allowed_upload_types($mimes) {

  if(get_option(CUL_ALLOWED_EXTENSIONS_KEY) == false || get_option(CUL_ALLOWED_EXTENSIONS_KEY) == '{}') {
    $mimes = array(
    	// Image formats
    	'jpg|jpeg'                     => 'image/jpeg',
    	'gif'                          => 'image/gif',
    	'png'                          => 'image/png',
    	'bmp'                          => 'image/bmp',
    	'tif|tiff'                     => 'image/tiff',

    	// Video formats
    	'mp4|m4v'                      => 'video/mp4',

    	// Text formats
    	'txt'                          => 'text/plain',
    	'csv'                          => 'text/csv',

    	// Audio formats
    	'mp3|m4a|m4b'                  => 'audio/mpeg',
    	'wav'                          => 'audio/wav',
    	'ogg|oga'                      => 'audio/ogg',
    	'mid|midi'                     => 'audio/midi',

    	// Misc application formats
    	'rtf'                          => 'application/rtf',
    	'pdf'                          => 'application/pdf',

    	// MS Office formats
    	'doc'                          => 'application/msword',
    	'ppt'                          => 'application/vnd.ms-powerpoint',
    	'xls'                          => 'application/vnd.ms-excel',
    	'docx'                         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    	'xlsx'                         => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
    	'pptx'                         => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',

    	// OpenOffice formats
    	'odt'                          => 'application/vnd.oasis.opendocument.text',
    	'odp'                          => 'application/vnd.oasis.opendocument.presentation',
    	'ods'                          => 'application/vnd.oasis.opendocument.spreadsheet'
    );

    update_option(CUL_ALLOWED_EXTENSIONS_KEY, json_encode($mimes));
  }

  $mimes = json_decode(get_option(CUL_ALLOWED_EXTENSIONS_KEY), true);

  return $mimes;
}

add_action('upload_mimes', 'cul_allowed_upload_types');

function cul_allowed_upload_file_extensions() {
  return array_keys(cul_allowed_upload_types(array()));
}

/** Set up settings page **/
if ( is_admin() ){
  cul_allowed_upload_types(array()); // Call allowed types method to initialize default values if blank
  
  add_action( 'admin_menu', 'cul_allowed_upload_types_plugin_menu' );
  add_action( 'admin_init', 'register_cul_allowed_upload_types_plugin_settings' );
}

function register_cul_allowed_upload_types_plugin_settings() {
  register_setting( 'cul-allowed-upload-types-group', CUL_ALLOWED_EXTENSIONS_KEY );
}

function cul_allowed_upload_types_plugin_menu() {
  add_options_page( 'CUL Allowed Upload Types', 'CUL Upload Types', 'manage_options', 'cul-allowed-upload-types', 'cul_allowed_upload_types_plugin_options' );
}

function cul_allowed_upload_types_plugin_options() {
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }
  ?>

  <script>
  var $ = jQuery;
  $(document).ready(function(){

    //Bind delete row action
    $('#cul-allowed-upload-types-form').on('click', '.delete-row', function(){
      $(this).closest('tr').remove();
    })

    //Bind submit action
    $('#cul-allowed-upload-types-form').on('submit', function(e){
      var newUploadTypes = {};

      $('#upload-type-values tbody').find('tr').each(function(){
        var extension = $(this).find('td.extension input').val();
        var mimeType = $(this).find('td.mime-type input').val();

        if(extension !== '' && mimeType !== '') {
          newUploadTypes[extension] = mimeType;
        }
      });

      $('#hidden-data-field').val(JSON.stringify(newUploadTypes));

      return true;
    });
  });
  </script>

  <div class="wrap">
    <h2>CUL Allowed Upload Types</h2>
  </div>
  <form id="cul-allowed-upload-types-form" method="post" action="options.php">
    <?php settings_fields( 'cul-allowed-upload-types-group' ); ?>
    <?php do_settings_sections( 'cul-allowed-upload-types-group' ); ?>
    <table class="form-table">
      <input id="hidden-data-field" type="hidden" name="<?php echo CUL_ALLOWED_EXTENSIONS_KEY; ?>" value="">
      <tr>
        <td>
          <table id="upload-type-values">
            <thead>
              <tr>
                <th>Extension</th>
                <th>Mime Type</th>
                <th></th>
              </tr>
            </thead>
            <tbody>
              <?php
                if(get_option(CUL_ALLOWED_EXTENSIONS_KEY) == false) {
                  $allowed_extensions = array();
                } else {
                  $allowed_extensions = json_decode(get_option(CUL_ALLOWED_EXTENSIONS_KEY), true);
                }
                $allowed_extensions[''] = ''; //add empty item at the end so we have a new, blank row at the end of the table
                foreach($allowed_extensions as $extension => $mimeType):
              ?>
              <tr>
                <td class="extension"><input type="text" value="<?php echo esc_html($extension); ?>"></td>
                <td class="mime-type"><input type="text" value="<?php echo esc_html($mimeType); ?>"></td>
                <?php if($extension != ''): ?>
                  <td><button class="delete-row" type="button">x</button></td>
                <?php endif; ?>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </td>
      </tr>
    </table>
    <?php submit_button(); ?>
  </form>
  <?php
}

?>
