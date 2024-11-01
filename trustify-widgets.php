<?php

/**
 * Plugin Name: Trustify Widgets
 * Description: Embed widgets for your Trustify review profile
 * Version: 1.0.0
 * Author: Navest GmbH
 * Author URI: https://trustify.ch
 */

class WPTrustifyWidget
{
   /**
    * Holds the values to be used in the fields callbacks
    */
   private $options;

   /**
    * Start up
    */
   public function __construct()
   {
      $this->options = get_option('trustify_options');
      add_filter('plugin_action_links_' . plugin_basename(__FILE__), [$this, 'plugin_settings_link']);
      add_action('admin_menu', array($this, 'add_plugin_page'));
      add_action('admin_init', array($this, 'page_init'));
      add_action('wp_enqueue_scripts', [$this, 'loadAssets']);
      add_action('wp_footer', [$this, 'displayBar']);
   }

   /**
    * Add links to plugin overview
    */
   public function plugin_settings_link($actions)
   {
      $actions[] = '<a href="' . admin_url('options-general.php?page=trustify-widgets') . '">' . __('Settings') . '</a>';
      $actions[] = '<a href="https://app.trustify.ch" target="_blank">' . __('Trustify Dashboard') . '</a>';
      return $actions;
   }

   /**
    * Load requried styles and scripts if a certian widget is enabled
    */
   public function loadAssets()
   {
      if (isset($this->options['trustify_bar_enabled'])) {
         wp_enqueue_script('trustify-bar', 'https://public.trustify.ch/widgets/js/bar.js');
         wp_enqueue_style('trustify-bar', 'https://public.trustify.ch/widgets/css/bar.css');
      }
   }

   /**
    * Inject the bar element into the footer
    */
   public function displayBar()
   {
      if (isset($this->options['trustify_bar_enabled']) && isset($this->options['trustify_slug'])) {
         printf('<div class="trustify-bar" data-profile="%s"></div>', $this->options['trustify_slug']);
      }
   }

   /**
    * Add options page
    */
   public function add_plugin_page()
   {
      // This page will be under "Settings"
      add_options_page(
         'Settings Admin',
         'Trustify Settings',
         'manage_options',
         'trustify-widgets',
         array($this, 'create_admin_page')
      );
   }

   /**
    * Options page callback
    */
   public function create_admin_page()
   {
?>
      <div class="wrap">
         <h1>Trustify Widgets</h1>
         <form method="post" action="options.php">
            <?php
            // This prints out all hidden setting fields
            settings_fields('turstify_option_group');
            do_settings_sections('trustify-widgets');
            submit_button();
            ?>
         </form>
      </div>
<?php
   }

   /**
    * Register and add settings
    */
   public function page_init()
   {
      register_setting(
         'turstify_option_group', // Option group
         'trustify_options', // Option name
         array($this, 'sanitize') // Sanitize
      );

      add_settings_section(
         'trustify_option_general', // ID
         'General', // Title
         array($this, 'print_general_info'), // Callback
         'trustify-widgets' // Page
      );

      add_settings_field(
         'trustify_slug', // ID
         'Profile Slug', // Title 
         array($this, 'general_slug_callback'), // Callback
         'trustify-widgets', // Page
         'trustify_option_general' // Section           
      );

      add_settings_section(
         'trustify_option_bar', // ID
         'Bar Widget', // Title
         array($this, 'print_bar_info'), // Callback
         'trustify-widgets' // Page
      );

      add_settings_field(
         'trustify_bar_enabled',
         'Bar Enabled',
         array($this, 'bar_enabled_callback'),
         'trustify-widgets',
         'trustify_option_bar'
      );
   }

   /**
    * Sanitize each setting field as needed
    *
    * @param array $input Contains all settings fields as array keys
    */
   public function sanitize($input)
   {
      $new_input = array();
      if (isset($input['trustify_slug']))
         $new_input['trustify_slug'] = sanitize_text_field($input['trustify_slug']);

      if (isset($input['trustify_bar_enabled']))
         $new_input['trustify_bar_enabled'] = filter_var($input['trustify_bar_enabled'], FILTER_SANITIZE_NUMBER_INT);

      return $new_input;
   }

   /** 
    * Print the General Section text
    */
   public function print_general_info()
   {
   }

   /** 
    * Print the Bar Section text
    */
   public function print_bar_info()
   {
   }

   /** 
    * Get the settings option array and print one of its values
    */
   public function general_slug_callback()
   {
      printf(
         '<input type="text" id="trustify_slug" name="trustify_options[trustify_slug]" value="%s" placeholder="my-profile"/>
          <p class="description">The slug is provided during the profile creation. If the url to your profile sth. like
          <code>in.trustify.ch/my-profile</code> the slug is <code>my-profile</code></p>
         ',
         isset($this->options['trustify_slug']) ? esc_attr($this->options['trustify_slug']) : ''
      );
   }

   /** 
    * Get the settings option array and print one of its values
    */
   public function bar_enabled_callback()
   {
      printf(
         '<fieldset>
            <lable for="trustify_bar_enabled"><input type="checkbox" id="trustify_bar_enabled" name="trustify_options[trustify_bar_enabled]" %s />
               Enable Trustify Bar
            </label>
         </fieldset>
         <p class="description">If this option is enabled the trustify bar widget will be displayed on each page.</p>',
         isset($this->options['trustify_bar_enabled']) ? 'checked' : ''
      );
   }
}

$widget = new WPTrustifyWidget();
