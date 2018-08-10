<?php 
/*
  Plugin Name: Appointment Manager
  Description: Plugin for showing list of appointments.
  Author: Vishal lele
  Version: 1.0
*/

//set up menu for plugin
add_action('admin_menu', 'set_np_admin_menu_for_plugin');

function set_np_admin_menu_for_plugin() {
	
	add_menu_page('Appointments', 'Appointments', 'edit_posts', 'appointment_list', 'np_load_appointment_list', '', 2);
}

function np_load_appointment_list(){
	require 'app/np_appointments_list.php';
}


//create table on activation of plugin
register_activation_hook(__FILE__, 'np_create_table_for_plugin');

function np_create_table_for_plugin(){

  global $wpdb;

  $table_name = $wpdb->prefix.'np_appointments_request';

  $sql = "CREATE TABLE IF NOT EXISTS ".$table_name." (
          id int(11) auto_increment PRIMARY KEY,
          name varchar(150) not null,
          email varchar(150) not null,
          subject varchar(150),
          message text,
          created_at datetime,
          status tinyint(1)
  )"; 

  require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
  dbDelta($sql);

}

/*
  This function depend upon contact form 7 plugin.
  contact form 7 hook used to enter data into the plugin table
*/
add_action('wpcf7_mail_sent', function ($cf7) {

  global $wpdb;

  $submission = WPCF7_Submission::get_instance();

  if($submission){

    $posted_data = $submission->get_posted_data();

    $wpdb->insert(
      $wpdb->prefix."np_appointments_request",
      array(
        'name' => $posted_data['your-name'],
        'email' => $posted_data['your-email'],
        'subject' => $posted_data['your-subject'],
        'message' => $posted_data['your-message'],
        'status' => 0
      ),
      array(
        '%s',
        '%s',
        '%s',
        '%s',
        '%d'
      )
    );

  }
  
});


?>
