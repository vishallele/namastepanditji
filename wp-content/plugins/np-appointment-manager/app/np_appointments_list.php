<?php 

	include 'lib/np_appointment_list_table.php';

	$appointment_list = new NP_Leads_List_Table();
?>
	<div class="wrap">
<?php
	$appointment_list->prepare_items();
	
	$appointment_list->search_box('Search Appointments','Search Appointments');

	$appointment_list->display();
	
?>
</div>