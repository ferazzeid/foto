<?php echo $before_title . $title . $after_title; ?>
	<form action="<?php echo esc_url( home_url( '/' ) ); ?>" role="search" method="get" class="taxonomies-filter-widget-form <?php if( $auto_submit ) echo 'tfw_auto'; ?>"><div>
		<input type="hidden" name="post_type" value="<?php echo $tfw_post_type ?>" />
		<ul>
<?php
	foreach ($selected_filters as $filter) {
		switch($filter['mode']) {
	        case 'dropdown':	$this->taxonomy_dropdown_walker($filter['name']);	break;
	        case 'multiselect':	$this->print_multiselect_taxonomy($filter['name']);	break;
	        case 'checkbox':	$this->print_checkbox_taxonomy($filter['name']);	break;
	        case 'radio':		$this->print_radio_taxonomy($filter['name']);		break;
	        case 'input':		$this->print_input_custom_field($filter);			break;
	        case 'range':		$this->print_range_custom_field($filter);			break;
	    	} //end switch
	} //end foreach

	if( $display_search_box && !empty($search_box_label) ){
	echo '<li class="search_box"><label class="taxlabel">'.$search_box_label.'</label><input type="text" name="s" class="input_search" value="'.get_search_query().'" /></li>';
	} 
	echo '<li>';
	if( $display_reset_button && !empty($reset_button_label) ){
		echo '<a class="reset_button" href="'. esc_url( home_url( "/?post_type=$tfw_post_type") ).'" >'.$reset_button_label.'</a>';
	} // end if

	if ($auto_submit) {   echo '<noscript>';  } 
		echo '<input type="submit" value="'.$submit.'" />';
	if ($auto_submit) {   echo '</noscript>';  } 
	
	echo '</li></ul></div></form>';
