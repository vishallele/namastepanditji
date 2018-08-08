<?php 

	include 'lib/np_appointment_list_table.php';

	$appointment_list = new NP_Appointments_List_Table();

	
	$appointment_list->prepare_items();
	
	$appointment_list->search_box('','');

	$appointment_list->display();
	
?>
