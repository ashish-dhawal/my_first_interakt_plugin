<?php // add the admin options page
/*
  Plugin Name: Interakt for WordPress
  Plugin URI: http://interakt.co
  Description: Integrate the <a href="http://interakt.co">Interakt</a> all in one customer engagement platform with your WordPress web app.
  Author: Ashish Dhawal
  Author URI: https://www.facebook.com/dhawalashish
  Version: 2.3
*/
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

class PS_Interakt{
    /**
     * Holds the values to be used in the fields callbacks
     */

    public $options;

    /**
     * Start up
     */
    public function __construct()
    {
      $this->options = get_option( 'interakt_plugin_options_name' );
      add_action( 'admin_menu', array( $this, 'interakt_plugin_admin_add_page' ) );
      add_action( 'admin_init', array( $this, 'interakt_plugin_admin_init' ) );      
    }

    /**
     * Add options page
     */
    public function interakt_plugin_admin_add_page()
    {
      // This page will be under "Settings"
      add_options_page(
        'Interakt Settings',
        'Interakt Settings',
        'manage_options',
        '__FILE__',
        array( $this, 'interakt_plugin_options_page' )
      );
    }

    /**
     * Options page callback
     */
    public function interakt_plugin_options_page()
    {
      // Set class property
      $this->options = get_option( 'interakt_plugin_options_name' );
      ?>
      <div class="wrap">
        <h2>Configure Interakt Ap Id</h2>
        <form method="post" action="options.php">
          <?php
            // This prints out all hidden setting fields
            settings_fields( 'interakt_plugin_options_group' );
            do_settings_sections( '__FILE__' );
            submit_button();
          ?>
        </form>
      </div>
      <?php
    }

    /**
     * Register and add settings
     */
    public function interakt_plugin_admin_init()
    {
      register_setting(
        'interakt_plugin_options_group', // Option group
        'interakt_plugin_options_name', // Option name
        array( $this, 'interakt_plugin_options_validate' ) // Sanitize
      );

      add_settings_section(
        'interakt_main_section_id', // ID
        'App Key Setting', // interakt_app_key
        array( $this, 'interakt_main_section_cb' ), // Callback
        '__FILE__' // Page
      );
      add_settings_field(
        'interakt_app_do_not_reload',
        '',
        array( $this, 'interakt_app_do_not_reload_message' ),
        '__FILE__',
        'interakt_main_section_id'
      );

      add_settings_field(
        'interakt_app_id',
        'Interakt App Id',
        array( $this, 'interakt_app_id_setting' ),
        '__FILE__',
        'interakt_main_section_id'
      );
      add_settings_field(
        'interakt_app_key',
        'Interakt App Key',
        array( $this, 'interakt_app_key_setting' ),
        '__FILE__',
        'interakt_main_section_id'
      );
      add_settings_field(
        'interakt_sink_data',
        'Sync User Data',
        array( $this, 'interakt_sync_data_button' ),
        '__FILE__',
        'interakt_main_section_id'
      );
      add_settings_field(
        'interakt_no_of_users_synced',
        '',
        array( $this, 'interakt_no_of_users_synced_message' ),
        '__FILE__',
        'interakt_main_section_id'
      );
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */

    public function interakt_plugin_options_validate( $input )
    {
      return $input;
    }

    /**
     * Print the Section text
     */
    public function interakt_main_section_cb()
    {
      echo '<h3>Confused! where to get these keys? </h3>';
      echo '<h3><a href="http://docs.interakt.co/integrations/wordpress" target="_blank">Click Here</a> or <a href="mailto:support@interakt.co?Subject=Need help for Interakt Integration with WordPress site" target="_top">Drop us an Email</a><h3>';
    }


    /**
     * Get the settings option array and print one of its values
     */
    public function interakt_app_id_setting()
    {
      printf(
        '<input type="text" id="interakt_app_id" name="interakt_plugin_options_name[interakt_app_id]" size="30" value="%s" />',
        isset( $this->options['interakt_app_id'] ) ? esc_attr( $this->options['interakt_app_id']) : ''
      );
    }
    /**
    * Field for interakt app key
    */
    public function interakt_app_key_setting()
    {
      printf(
        '<input type="text" id="interakt_app_key" name="interakt_plugin_options_name[interakt_app_key]" size="30" value="%s" />',
        isset( $this->options['interakt_app_key'] ) ? esc_attr( $this->options['interakt_app_key']) : ''
      );
    }
    public function interakt_sync_data_button()
    {
      printf('<span id="sink_btn" class="button " style="cursor:pointer;">Sync Users</span>');
    }
    public function interakt_no_of_users_synced_message()
    {
      printf('<div id="msg"></div>');
    }
    public function interakt_app_do_not_reload_message()
    {
        printf('<p id="reload_msg" style="font-size:20px;"></p>'); 
    }
    public function add_sync_user_script()
    {
      wp_enqueue_script('sync-user-script', plugin_dir_url(__FILE__) . '/js/syncing_user_data.js');
      wp_localize_script('sync-user-script', 'syncUserScript', array('pluginsUrl' => plugins_url(),'interakt_app_id'=>$this->options['interakt_app_id'],'interakt_app_key'=>$this->options['interakt_app_key']));
    }
}


//Calling constructor method if user is in admin panel
  if( is_admin() )
  {
    $my_settings_page = new PS_Interakt();
    $my_settings_page->add_sync_user_script();
  }

  add_action('wp_footer', "add_interakt_script" );


  function add_interakt_script(){
    $interakt_object = new PS_Interakt();
    $interakt_app_id = ($interakt_object->options['interakt_app_id']);
    if (!empty($interakt_app_id)) {
      echo "<script>
        (function() {
        var interakt = document.createElement('script');
        interakt.type = 'text/javascript'; interakt.async = true;
        interakt.src = 'http://cdn.interakt.co/interakt/$interakt_app_id.js'
        var scrpt = document.getElementsByTagName('script')[0];
        scrpt.parentNode.insertBefore(interakt, scrpt);
        })()
      </script>";
      if ( is_user_logged_in() ) {
        global $current_user;
        get_currentuserinfo();
        $user_name = $current_user->user_login;
        $email = $current_user->user_email;
        $created_at = $current_user->user_registered;
        echo "<script>
          window.mySettings = {
          email: '$email',
          name: '$user_name',
          created_at: '$created_at',
          app_id: '$interakt_app_id'
          };
        </script>";
      }
    }
  };

