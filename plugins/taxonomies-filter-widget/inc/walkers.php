<?php


/**
 *  Walker class for dropdown taxonomies
 */

class Walker_TaxonomiesDropdown extends Walker_CategoryDropdown{


	function start_el(&$output, $category, $depth, $args) {
		$pad = str_repeat('&nbsp;', $depth * 3);
		$cat_name = apply_filters('list_cats', $category->name, $category);
		$counter = tfw_dynamic_counter($args['show_count'],$args['name'],$category);
		
		// Do not output it if the counter is empty and hide_empty is TRUE
		if (!(!$counter && $args['hide_empty'])){ 
			$output .= "\t<option class=\"level-$depth\" value=\"".$category->slug."\"";
			if ( $category->slug === (string) $args['selected'] ){
				$output .= ' selected="selected"';
			}
			$output .= '>';
			$output .= $pad.$cat_name;
			$output .= ($args['show_count'] == "none") ? '' : '&nbsp;('.  $counter .')';
			$output .= "</option>\n";
		} //end if counter
	}
 
} 


/**
 *  Walker class for Select Multiple
 */
class Walker_TaxonomiesMultiselect extends Walker_Category{

	function start_lvl( &$output, $depth = 0, $args = array() ) { $output .= ""; }	

  	public function start_el(&$output, $term, $depth, $args){
  		$pad = str_repeat('&nbsp;', $depth * 3);
	    $args = wp_parse_args($args); extract($args);
	    $tfw_options = get_option('tfw_options');
	    global $wp_query;
	  	$selected  = get_query_var($name);
	    $selected .= $tfw_options['multiple_relation'];
	    if(isset($wp_query->query[$name]))
	  	$selected .= $wp_query->query[$name];
	    $counter = tfw_dynamic_counter($show_count,$name,$term);
		// Do not output it if the counter is empty and hide_empty is TRUE
	    if (!(!$counter && $hide_empty)){
		   	ob_start();
		    $output .= "\t<option value=\"".$term->slug."\"";
		    if( in_array($term->slug, explode($tfw_options['multiple_relation'],$selected)) )
				$output .= 'selected';
			$output .= '>';
			$output .= $pad.$term->name;
			$output .= ($show_count == "none") ? '' : '&nbsp;('.  $counter .')';
			$output .= "</option>\n";
			$output .= ob_get_clean();
	    } //end if counter
	}

  	function end_el( &$output, $page, $depth = 0, $args = array() ) { return; }
	function end_lvl( &$output, $depth = 0, $args = array() ) {	$output .= ""; }
}


/**
 *  Walker class for checklists
 */
class Walker_TaxonomiesChecklist extends Walker_Category{

	function start_lvl( &$output, $depth = 0, $args = array() ) { $output .= "<li><ul class='children'>"; }	

  	public function start_el(&$output, $term, $depth, $args){

	    $args = wp_parse_args($args);  extract($args);
	    $counter = tfw_dynamic_counter($show_count,$name,$term);
	    $tfw_options = get_option('tfw_options');
	    global $wp_query;
	    $selected  = get_query_var($name);
	    $selected .= $tfw_options['multiple_relation'];
	    if(isset($wp_query->query[$name]))
	  	$selected .= $wp_query->query[$name];
		// Do not output it if the counter is empty and hide_empty is TRUE
	    if (!(!$counter && $hide_empty)){
		    ob_start(); ?>   
		    <li>
		    	<label for="<?php echo $term->slug; ?>">
		      		<input type="checkbox" <?php checked(in_array($term->slug, explode($tfw_options['multiple_relation'],$selected))); ?> id="<?php echo $term->slug; ?>"  value="<?php echo $term->slug; ?>" /> <?php echo $term->name; 
		      		echo ($show_count == "none") ? '' : '&nbsp;<span>('.  $counter .')</span>'; 
			?>	</label>  
			</li>     
			<?php 
		    $output .= ob_get_clean();
	    } //end if counter
	}

  	function end_el( &$output, $page, $depth = 0, $args = array() ) { return; }
	function end_lvl( &$output, $depth = 0, $args = array() ) {	$output .= "</ul></li>"; }
}

/**
 *  Walker class for radio buttons
 */
class Walker_TaxonomiesRadio extends Walker_Category{

	function start_lvl( &$output, $depth = 0, $args = array() ) { $output .= "<li><ul class='children'>"; }	

	public function start_el(&$output, $term, $depth = 0, $args = array()){

	    $args = wp_parse_args($args);  extract($args);
	    $counter = tfw_dynamic_counter($show_count,$name,$term);
	    $checked = get_query_var($name);
	
		// Do not output it if the counter is empty and hide_empty is TRUE
	    if (!(!$counter && $hide_empty)){
		    ob_start(); ?>   
		    <li>
		    	<label for="<?php echo $term->slug; ?>">
		      		<input type="radio" <?php checked($term->slug == $checked); ?> id="<?php echo $term->slug; ?>" name="<?php echo $name; ?>" value="<?php echo $term->slug; ?>" /> <?php echo esc_attr($term->name); 
					echo ($show_count == "none") ? '' : '&nbsp;<span>('.  $counter .')</span>'; 
			?>  </label>  
			</li>     
			<?php 
		    $output .= ob_get_clean();
		  } //end if counter
	} 

  	function end_el( &$output, $page, $depth = 0, $args = array() ) { return; }
	function end_lvl( &$output, $depth = 0, $args = array() ) { $output .= "</ul></li>"; }
}

