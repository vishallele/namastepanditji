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



?>
