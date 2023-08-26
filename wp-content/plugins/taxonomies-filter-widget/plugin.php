<?php
/*
Plugin Name: Taxonomies Filter Widget
Plugin URI: http://webcodesigner.com
Description: Creates a widget for filtering posts and pages by Categories, Tags, Custom Taxonomies and Custom Fields. You can even set your own order and specify how you want each option to be displayed.
Version: 2.0
Author: Cristian Ionel
Author URI: http://webcodesigner.com
Author Email: cristian.ionel@gmail.com
Text Domain: taxonomies-filter-widget
Domain Path: /lang/

*/
require_once( plugin_dir_path(__FILE__) . '/inc/walkers.php' );
require_once( plugin_dir_path(__FILE__) . '/inc/helpers.php' );

class Taxonomies_Filter_Widget extends WP_Widget {

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/   

	/**
	 * Specifies the classname and description, instantiates the widget, 
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	 
	public $tfw_options;
	public $filters;

	public function __construct() {  
	
		// load plugin text domain
		add_action( 'init', array( $this, 'widget_textdomain' ) );
		
		// Hooks fired when the Widget is activated and deactivated
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );
			
		parent::__construct(
			'taxonomies-filter-widget',
			__( 'Taxonomies Filter', 'taxonomies-filter-widget' ),
			array(
				'classname'		=>	'taxonomies-filter-widget',
				'description'	=>	__( 'Filter posts by category and/or taxonomy.', 'taxonomies-filter-widget' )
			)
		);

		// Load plugin options and/or set the default values 
		$this->tfw_options = wp_parse_args( (array) get_option('tfw_options'), array( 
			'auto_submit' => 0, 
			'hide_empty' => 0, 
			'display_search_box' => 0,
			'display_reset_button' => 0,
			'multiple_relation' => ',',
			'results_template' => 'search',
			'custom_template' => 'search.php',
			'post_count' => 'dynamic',
			'search_box' => 'Keywords',
			'search_button' => 'Search',
			'reset_button' => 'Reset all'
		));
		add_option('tfw_options', $this->tfw_options);
		$this->filters = get_option('widget_taxonomies-filter-widget');


		// Register admin styles and scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts_styles' ) );
	
		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
		
		// Register the function that will handle the ajax request 
		add_action('wp_ajax_get_term_childrens', array($this,'ajax_drilldown'));
		add_action('wp_ajax_nopriv_get_term_childrens', array($this,'ajax_drilldown')); 
		
		
		// if is admin, create the options page, otherwise, set the search results template
		if (is_admin()){
			add_action('admin_menu', array( $this, 'tfw_options_page' ));
		    add_action('admin_init', array( $this, 'tfw_page_init' ));
			add_filter('plugin_action_links', array( $this, 'add_settings_link'), 10, 2 );			
		} elseif(is_main_query()) {
			add_filter('get_meta_sql', array( $this, 'cast_decimal_precision' ));
			add_action('pre_get_posts', array( $this, 'filter_by_custom_field' ), 2);
			add_action('template_redirect', array( $this, 'results_template' ), 2);

		}
		
	} // end constructor     

	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/
	
	/**
	 * Outputs the content of the widget.
	 *
	 * @param	array	args		The array of form elements
	 * @param	array	instance	The current instance of the widget
	 */
	public function widget( $args, $instance ) {

		extract( $args );
		extract( $this->tfw_options );

		$title 				 = apply_filters('widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base);
		$selected_filters 	 = isset( $instance['selected_filters'] ) ? $instance['selected_filters'] : array();
		$tfw_post_type 		 = isset( $instance['tfw_post_type'] ) ? $instance['tfw_post_type'] : 'post';
		// labels
		$submit 			 = empty( $instance['submit'] ) ? '' : $instance['submit'];
		$select_all  		 = empty( $instance['select_all'] ) ? '' : $instance['select_all'];
       	$search_box_label 	 = empty( $instance['search_box_label'] ) ? '' : $instance['search_box_label'];
       	$reset_button_label  = empty( $instance['reset_button_label'] ) ? '' : $instance['reset_button_label'];

		
		$this->selected_filters = $selected_filters;

		echo $before_widget;
		include( plugin_dir_path( __FILE__ ) . '/views/widget.php' );
		echo $after_widget;
	} // end widget
	
	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param	array	new_instance	The previous instance of values before the update.
	 * @param	array	old_instance	The new instance of values to be generated via the update.
	 */
	public function update( $new_instance, $old_instance ) {
	
		$instance = $old_instance;
		
		$instance['title'] 				= strip_tags($new_instance['title']);
		$instance['filters'] 			=!empty( $new_instance['filters']) ? $new_instance['filters'] : array();
		$instance['tfw_post_type'] 		= isset( $new_instance['tfw_post_type'] ) ? $new_instance['tfw_post_type'] : 'post';

		//labels
		$instance['submit'] 			= strip_tags($new_instance['submit']);
		$instance['select_all'] 		= strip_tags($new_instance['select_all']);
		$instance['search_box_label'] 	= strip_tags($new_instance['search_box_label']);
		$instance['reset_button_label'] = strip_tags($new_instance['reset_button_label']);

		$selected_filters = array();
		// Filter out the ones not selected
		foreach ($instance['filters'] as $single_filter) {
			if (array_key_exists( 'name', $single_filter )) {
				$selected_filters[] = $single_filter;
			}
		}
		$instance['selected_filters'] = $selected_filters;

		return $instance;
	} // end update
	
	/**
	 * Generates the administration form for the widget.
	 *
	 * @param	array	instance	The array of keys and values for the widget.
	 */
	public function form( $instance ) {
	
    	// Extracting definded values and defining default values for variables

	    $instance = wp_parse_args( (array) $instance, array( 
			'title' => '', 
			'filters' => array(), 
			'tfw_post_type' => 'post',
			'submit' => 'Search',
			'search_box_label' => 'Search For',
			'reset_button_label' => 'Reset All',
			'select_all' => 'Select All'
		));

		$title 				= esc_attr( $instance['title'] );
		$selected_filters	= isset( $instance['selected_filters'] ) ? $instance['selected_filters'] : array();
		$tfw_post_type 		= isset( $instance['tfw_post_type'] ) ? $instance['tfw_post_type'] : 'post';

		//labels
		$submit 			= esc_attr( $instance['submit'] );
		$select_all 		= esc_attr( $instance['select_all'] );
		$search_box_label 	= esc_attr( $instance['search_box_label'] );
		$reset_button_label = esc_attr( $instance['reset_button_label'] );


		// Get already selected filters and put them at the top
		$all_filters = $selected_filters;

		// Append all other taxonomies
		foreach ( $this->get_post_type_taxonomies($tfw_post_type) as $taxonomy ) {
			if ( !$this->in_array_r( $taxonomy, $selected_filters ) )
				$all_filters[] = array('name' => $taxonomy,'mode'=>'dropdown');
		}

		// Append all other custom fields
		foreach ( $this->get_post_type_custom_fields($tfw_post_type) as $cf ) {
			if ( !$this->in_array_r( $cf['name'], $selected_filters ) )
				$all_filters[] = array(
					'name' => $cf['name'],
					'mode'=>'input',
					'min' => $cf['min'],
					'max' => $cf['max']
				);
		}

		// Display the admin form
		include( plugin_dir_path(__FILE__) . '/views/admin.php' );	
		
	} // end form


	/*--------------------------------------------------*/
	/* Widget Specific Functions
	/*--------------------------------------------------*/
	
	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function widget_textdomain() {
	
		load_plugin_textdomain( 'taxonomies-filter-widget', false, plugin_dir_path( __FILE__ ) . '/lang/' );
		
	} // end widget_textdomain
	
	/**
	 * Fired when the plugin is activated.
	 *
	 * @param		boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function activate( $network_wide ) {
		
	} // end activate
	
	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog 
	 */
	public function deactivate( $network_wide ) {

		delete_option( 'widget_taxonomies-filter-widget' );
		delete_option( 'tfw_options' );
	} // end deactivate
	

	/**
	 * Registers and enqueues admin-specific JavaScript and CSS only on widgets page.
	 */	
	public function register_admin_scripts_styles($hook) {

		if( 'widgets.php' != $hook )
        	return;
		wp_enqueue_script( 'taxonomies-filter-widget-admin-script', plugins_url( 'taxonomies-filter-widget/js/admin.js' ), array('jquery'), false, true );
		wp_enqueue_style( 'taxonomies-filter-widget-admin-styles', plugins_url( 'taxonomies-filter-widget/css/admin.css' ) );
		
	} // end register_admin_scripts
	
	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {

		wp_enqueue_script('jquery');
		wp_enqueue_script( 'taxonomies-filter-widget-slider-script', plugins_url( 'taxonomies-filter-widget/js/jquery.nouislider.min.js' ), array('jquery'), false, true );
		wp_enqueue_script( 'taxonomies-filter-widget-script', plugins_url( 'taxonomies-filter-widget/js/widget.js' ), array('jquery'), false, true );
		wp_localize_script( 'taxonomies-filter-widget-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'relation' => $this->tfw_options['multiple_relation'] ));

	} // end register_widget_scripts

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {
		wp_register_style( 'taxonomies-filter-widget-styles', plugins_url( 'taxonomies-filter-widget/css/widget.css' ) );
		wp_register_style( 'taxonomies-filter-widget-slider-styles', plugins_url( 'taxonomies-filter-widget/css/nouislider.fox.css' ) );
		wp_enqueue_style( 'taxonomies-filter-widget-styles' );
		wp_enqueue_style( 'taxonomies-filter-widget-slider-styles' );
	} // end register_widget_styles





	/*--------------------------------------------------*/
	/* Settings page Functions
	/*--------------------------------------------------*/		

	public function tfw_options_page(){
        // This page will be under "Settings"
		add_options_page('Settings Admin', 'Taxonomies Filter', 'manage_options', 'tfw_options_page', array($this, 'tfw_create_admin_page'));
    }
	
	public function tfw_create_admin_page(){
        include( plugin_dir_path(__FILE__) . '/views/options.php' );	
    }
    
    /**
	* Add Settings link to plugins - code from GD Star Ratings
	*/
	public function add_settings_link($links, $file) {
		static $this_plugin;
		if (!$this_plugin) $this_plugin = plugin_basename(__FILE__);
		if ($file == $this_plugin){
			$settings_link = '<a href="options-general.php?page=tfw_options_page">'.__("Settings", "taxonomies-filter-widget").'</a>';
	 		array_unshift($links, $settings_link);
		}
		return $links;
	}
    
	public function tfw_page_init(){	
		include( plugin_dir_path( __FILE__ ) . '/inc/register_settings.php' );
    }
    

	/* Options page & form inputs */
	
	
	/* General Options */
    public function section_general(){
		_e('<h4>Use this section to enable/disable filter options:</h4>', 'taxonomies-filter-widget');
    }
	
    	function field_auto_submit(){
        	?><input type="checkbox" name="tfw_options[auto_submit]" 			<?php checked( !empty($this->tfw_options['auto_submit']) ); ?> 			/><?php
    	}
    	function field_hide_empty(){
        	?><input type="checkbox" name="tfw_options[hide_empty]" 			<?php checked( !empty($this->tfw_options['hide_empty']) ); ?> 			/><?php
    	}
    	function field_display_search_box(){
        	?><input type="checkbox" name="tfw_options[display_search_box]" 	<?php checked( !empty($this->tfw_options['display_search_box']) ); ?> 	/><?php
    	}
    	function field_display_reset_button(){
        	?><input type="checkbox" name="tfw_options[display_reset_button]" 	<?php checked( !empty($this->tfw_options['display_reset_button']) ); ?> /><?php
    	}
    	function field_post_count(){
        	?><label><input type="radio" name="tfw_options[post_count]" value="total"	<?php checked( "total" 	== $this->tfw_options['post_count'] ); ?> /> Total</label>
         <br/><label><input type="radio" name="tfw_options[post_count]" value="dynamic" <?php checked( "dynamic"== $this->tfw_options['post_count'] ); ?> /> Dynamic</label>
         <br/><label><input type="radio" name="tfw_options[post_count]" value="none"	<?php checked( "none" 	== $this->tfw_options['post_count'] ); ?> /> None</label><?php
    	}
    	function field_multiple_relation(){
        	?><select name="tfw_options[multiple_relation]">
					<option value="+" <?php selected( "+" == $this->tfw_options['multiple_relation'] ); ?> > AND </option>
					<option value="," <?php selected( "," == $this->tfw_options['multiple_relation'] ); ?> > OR </option>
			  </select><?php
    	}
    	function field_results_template(){
        	?><select name="tfw_options[results_template]">

					<option value="search"		<?php selected(   "search" == $this->tfw_options['results_template'] ); ?> > Search Results </option>
					<option value="archive" 	<?php selected(  "archive" == $this->tfw_options['results_template'] ); ?> > Archive </option>
					<option value="category" 	<?php selected( "category" == $this->tfw_options['results_template'] ); ?> > Category </option>
					<option value="tag" 		<?php selected( 	 "tag" == $this->tfw_options['results_template'] ); ?> > Tag Archive </option>
					<option value="taxonomy" 	<?php selected( "taxonomy" == $this->tfw_options['results_template'] ); ?> > Taxonomy Archive </option>
					<option value="posttype" 	<?php selected( "posttype" == $this->tfw_options['results_template'] ); ?> > Post Type Archive </option>
					<option value="home" 		<?php selected( 	"home" == $this->tfw_options['results_template'] ); ?> > Blog Home Page </option>
					<option value="custom"		<?php selected(   "custom" == $this->tfw_options['results_template'] ); ?> > Custom Template </option>

			  </select><?php
    	}
    	function field_custom_template(){
        	?><input type="text" name="tfw_options[custom_template]" value="<?php echo $this->tfw_options['custom_template']; ?>"/><?php
    	}
    
    
    
    /* Labels 
    public function section_labels(){
		_e('<h4>From here you can customize the labels displayed on the front-end: </h4>', 'taxonomies-filter-widget');
    }
    
    	function field_search_button(){
        	?><input type="text" name="tfw_options[search_button]" 	value="<?php echo $this->tfw_options['search_button']; ?>"				/><?php
    	}
    	function field_reset_button(){
        	?><input type="text" name="tfw_options[reset_button]" 	value="<?php echo $this->tfw_options['reset_button']; ?>"				/><?php
    	}
    	function field_search_box(){
        	?><input type="text" name="tfw_options[search_box]"		value="<?php echo $this->tfw_options['search_box']; ?>"					/><?php
    	}
    */
    	
    /* Cleaning user input */
	public function callback_check($input){
		$clean_options = array();
		foreach($input as $option => $value)
			$clean_options[$option] = esc_attr ( strip_tags($value) );			
		return $clean_options;
    }    
    
	// End options page    
    
    



    /*--------------------------------------------------*/
	/* Other Functions
	/*--------------------------------------------------*/


	/**
	 * Get all registered post types
	 */
	public function get_all_post_types(){

		$args = array(
		  'public'   => true
		); 
		$output = 'objects'; 
		$operator = 'and'; 
		return get_post_types( $args, $output, $operator ); 
	}

	/**
	 * Get all the taxonomies defined in the blog
	 */
	public function get_all_taxonomies(){

		$args = array(
		  'public'   => true
		); 
		$output = 'names'; 
		$operator = 'and'; 
		return get_taxonomies( $args, $output, $operator ); 
	}


	/**
	 * Get all taxonomies registered for a post type
	 */
	public function get_post_type_taxonomies($post_type){

		return get_object_taxonomies($post_type, 'names');
	}

	/**
	 * Get all custom fields associated with a post type
	 */
	public function get_post_type_custom_fields($post_type){

		$args = array(
		   'numberposts' => -1,
		   'post_type' => $post_type,
		   'post_status' => 'publish'
		);
		$all_post_type_cf = array();
		$temp = array();
		$all_posts = get_posts ( $args );

		foreach ($all_posts as $post) {
			$post_cf = get_post_custom($post->ID);
			$wp_cf = array('_edit_last','_edit_lock');
			foreach ($post_cf as $cf_key => $cf_value) {

				if(!in_array($cf_key, $wp_cf) ){

					$temp[$cf_key]['name']  = $cf_key;
					foreach ($cf_value as $value) {
						(isset($temp[$cf_key]['min'])) ? $temp[$cf_key]['min'] = min($temp[$cf_key]['min'], $value) : $temp[$cf_key]['min'] = $value;
						(isset($temp[$cf_key]['max'])) ? $temp[$cf_key]['max'] = max($temp[$cf_key]['max'], $value) : $temp[$cf_key]['max'] = $value; 
					}
				} //endif
				
			} //endforeach
			
		} //endforeach
		foreach ($temp as $cf) {
			if(is_numeric($cf['min']) && is_numeric($cf['max'])){
				$all_post_type_cf[] = $cf;
			}
		}
		return $all_post_type_cf;
	}

	/**
	 * Check if the taxonmy is valid and is public
	 */
	public function valid_public_taxonomy( $tax_name ) {

		$taxonomy = get_taxonomy( $tax_name );
		if ( $taxonomy && $taxonomy->public && $taxonomy->query_var ){
		    return $taxonomy;
		}
		return false;
	}

	/**
	 * Prints out a taxonomy in the widget's config panel
	 */
	public function admin_print_taxonomy( $taxonomy, $selected_filters, $i, $output ) {
	?>

		<li class="tfw_taxonomy">
			<label>
				<input type="checkbox" name="<?php echo $this->get_field_name('filters'); ?>[<?php echo $i; ?>][name]" value="<?php echo $taxonomy->name; ?>" <?php checked($this->in_array_r($taxonomy->name, $selected_filters)); ?> > 
				<?php echo $taxonomy->label; ?>
			</label>

			<select name="<?php echo $this->get_field_name('filters'); ?>[<?php echo $i; ?>][mode]">
				<?php 
				$output_modes = array('dropdown','multiselect','checkbox','radio');
				foreach ($output_modes as $output_mode) { ?>
					<option value="<?php echo $output_mode; ?>" <?php selected($output_mode == $output); ?> ><?php echo $output_mode; ?></option>
				<?php }	?>
			</select>
		</li>
	
	<?php
	} // end admin_print_taxonomy

	/**
	 * Prints out a custom field in widget's config panel
	 */
	public function admin_print_custom_field( $cf, $selected_filters, $i, $output ) {
	?>

	<li class="tfw_cf_li"> 
		<label>
			<input type="checkbox" name="<?php echo $this->get_field_name('filters'); ?>[<?php echo $i; ?>][name]" value="<?php echo $cf['name']; ?>" <?php checked($this->in_array_r($cf['name'], $selected_filters)); ?> > 
			Name: <?php echo $cf['name']; ?>
		</label> <br/>
		<label>Label:<input class="tfw_cf_label" type="text" name="<?php echo $this->get_field_name('filters'); ?>[<?php echo $i; ?>][label]" value="<?php  if(isset($cf['label'])) echo $cf['label']; ?>"/></label><br/>
		<label>Min: <input type="text" class="tfw_cf_min_max" name="<?php echo $this->get_field_name('filters'); ?>[<?php echo $i; ?>][min]" value="<?php echo $cf['min']; ?>" /> </label>
		<label>Max: <input type="text" class="tfw_cf_min_max" name="<?php echo $this->get_field_name('filters'); ?>[<?php echo $i; ?>][max]" value="<?php echo $cf['max']; ?>" /></label>
		<label>Step: <input type="text" class="tfw_cf_step" name="<?php echo $this->get_field_name('filters'); ?>[<?php echo $i; ?>][step]" value="<?php if(isset($cf['step'])) echo $cf['step'];  ?>" /></label><br/>

		<label>Mode:</label>
			<select class="tfw_cf_mode" name="<?php echo $this->get_field_name('filters'); ?>[<?php echo $i; ?>][mode]">
				<?php 
				$output_modes = array('input', 'range');
				foreach ($output_modes as $output_mode) { ?>
					<option value="<?php echo $output_mode; ?>" <?php selected($output_mode == $output); ?> ><?php echo $output_mode; ?></option>
				<?php }	?>
		</select>
		<label>Unit:<input class="tfw_cf_unit" type="text" name="<?php echo $this->get_field_name('filters'); ?>[<?php echo $i; ?>][unit]" value="<?php  if(isset($cf['unit'])) echo $cf['unit']; ?>"/></label><br/>

	</li>
	
	<?php
	} // end admin_print_cf

	/**
	 * Handles Ajax processing and returns the HTML element if selected term has childrens
	 */
	public function ajax_drilldown() {

		$taxonomy = esc_attr($_REQUEST['taxonomy']);
		$term = esc_attr($_REQUEST['term']);
		$name = $taxonomy;
		if ($taxonomy == 'category_name') {
			$taxonomy = 'category';
		}
		$current_term = get_term_by( 'slug', $term, $taxonomy );
		$childrens = array();		    
	    if (isset($current_term->term_id)) {
		    $childrens = get_terms ($taxonomy, array(
				'child_of'	=> $current_term->term_id,
				'hide_empty'    => $this->tfw_options['hide_empty']
			));
		}
		if ($childrens){
		    $this->print_dropdown_taxonomy($taxonomy,$current_term->term_id,'',1,$name);
	    }		    
	    die();
	} //ajax_drilldown

	/**
	 * Returns the current term's parent
	 * @param	integer 	term		The id of the current term
	 * @param	object		taxonomy	The taxonomy to which the term belongs
	 * @return 	object      parent_term	The term's parent
	 */

	public function get_term_parent ($term, $taxonomy){
		$parent_term = get_term_by( 'id', intval($term->parent) , $taxonomy);
		return $parent_term;
	}

	/**
	 * Walks through given taxonomy parents and call the print function for each one;
	 * Also checks if the form has been already submitted and if so, call the print function
	 * for each child previously selected; 	 	 
	 * @param	string	taxonomy	Name of the taxonomy
	 */	 	

	public function taxonomy_dropdown_walker($taxonomy){   

		if ($taxonomy) { 
				
				$name = fix_taxonomy_name($taxonomy);
				$current_term = get_term_by( 'slug', get_query_var($name) , $taxonomy);
				$selected = get_query_var($name);
				$tax = get_taxonomy($taxonomy);
				echo '<li class="'.$taxonomy.'-section"><label class="taxlabel">'.$tax->label.'</label>'; 
				
				if ($selected && is_taxonomy_hierarchical( $taxonomy ) ){ // if form has been submitted and this taxonomy has been selected							  	  	
					$new_term = $current_term;
				    $has_parents = 1;
				    $output_array = array();
				    $childrens = array();		    
				    if (isset($current_term->term_id)) {
					    $childrens = get_terms ($taxonomy, array(
							'child_of'	=> $current_term->term_id,
							'hide_empty'    => $this->tfw_options['hide_empty']
						));
					}
					if ($childrens){
					    $output_array[] = $this->print_dropdown_taxonomy($taxonomy,$current_term->term_id,'',0,$name);
				    }	
					while ($has_parents != 0){
		    		    if ($current_term->parent == 0){
		    		    	$output_array[] = $this->print_dropdown_taxonomy($taxonomy, $current_term->parent, $current_term->slug, 0, $name); //store the dropdown
							$has_parents = 0; 
						} else {
							$output_array[] = $this->print_dropdown_taxonomy($taxonomy, $current_term->parent, $current_term->slug, 0, $name); //store the dropdown
							$parent_term = $this->get_term_parent( $current_term , $taxonomy); // get the closest parent
							$current_term = get_term_by( 'id', $parent_term->term_id , $taxonomy);
							$has_parents = $parent_term->term_id;
						} //end else		
					}
					$output_array = array_reverse($output_array);  //reverse the array with dropdowns since we started from the last child
					foreach ($output_array as $output){
						echo $output;
					} //end foreach
				} else { 
					$this->print_dropdown_taxonomy($taxonomy,0,$selected,1,$name);  // display the dropdown for the top level term without anything selected
				}
				echo '</li>';
		} // end if
	}// end taxonomy_dropdown_walker
	

	/**
	* Prints out the taxonomy or return a string with the dropdown code using the wp_dropdown_categories function
	* @param 	string	taxonomy	Taxonomy name
	* @param	integer	parent		Id of the parent term
	* @param	string	selected	Name of the term that needs to be selected
	* @param	bool	echo		echo html when true; returns string when false				
	* @return	mixed	html|string
	**/
			
	public function print_dropdown_taxonomy($taxonomy, $parent = 0, $selected = '', $echo = 1, $name){      
 	
		if ( taxonomy_exists($taxonomy) ) { 
			$tax = get_taxonomy($taxonomy);
			$select_all = isset($this->filters[$this->number]['select_all']) ? $this->filters[$this->number]['select_all'] : '';
			$args = array(
				'show_option_all'    => $select_all,
				'show_option_none'   => '',
				'orderby'            => 'name', 
				'order'              => 'ASC',
				'show_count'         => $this->tfw_options['post_count'],
				'hide_empty'         => $this->tfw_options['hide_empty'], 
				'child_of'           => $parent,
				'exclude'            => '',
				'echo'               => $echo,
				'selected'           => $selected,
				'hierarchical'       => 1, 
				'name'               => $name,
				'id'                 => $parent ? 'sub_cat_'.$name : $name,
				'class'              => $tax->hierarchical ? 'taxonomies-filter-widget-input tax-with-childrens' :'taxonomies-filter-widget-input',
				'depth'              => 1,
				'tab_index'          => 0,
				'taxonomy'           => $taxonomy,
				'hide_if_empty'      => $this->tfw_options['hide_empty'],
				'walker'			 => new Walker_TaxonomiesDropdown()
			); 

			if ($echo) {
			    wp_dropdown_categories( $args );
			} else {
				return wp_dropdown_categories( $args );
			}
		} // end if
	} //end print_dropdown_taxonomy



	/**
	* Prints out the taxonomy using multiselect form 
	* @param 	string 	taxonomy 	Name of the taxonomy to be printed out
	**/
	public function print_multiselect_taxonomy($taxonomy){      
 		
 		if ( taxonomy_exists($taxonomy) ) {
 			$tax = get_taxonomy($taxonomy); 
 			$tax_name = fix_taxonomy_name($taxonomy);
 			$output = '
 				<li class="'.$tax_name.'_li">
 					<label class="taxlabel">'.$tax->label.'</label>
 					<select class="taxonomies-filter-widget-multiselect" multiple size="8">';
			$output_list = wp_list_categories(array(
				'walker'   => new Walker_TaxonomiesMultiselect(),
				'name'     => $tax_name,       
				'orderby'  => 'name',
				'order'    => 'ASC',
				'title_li' => '',
				'style'    => 'list',
				'echo'	   => 0,
				'show_count' => $this->tfw_options['post_count'],
				'hide_empty' => $this->tfw_options['hide_empty'],
				'taxonomy' => $taxonomy    
			));
					
			if (trim( $output_list )) {
				global $wp_query;
				$selected = isset($wp_query->query[$tax_name]) ? $wp_query->query[$tax_name] : get_query_var($tax_name);  
				$output .= $output_list.'</select>'.'<input type="hidden" name="'.$tax_name.'" value="'.$selected.'">'.'</li>';
				echo $output;
			}

		} //end if

	} //end print_checkbox_taxonomy




	/**
	* Prints out the taxonomy using checkboxes 
	* @param 	string 	taxonomy 	Name of the taxonomy to be printed out
	**/
	public function print_checkbox_taxonomy($taxonomy){      
 		if ( taxonomy_exists($taxonomy) ) {
 			$tax = get_taxonomy($taxonomy); 
 			$tax_name = fix_taxonomy_name($taxonomy);
 			$output = '
 				<li class="'.$tax_name.'_li">
 					<label class="taxlabel">'.$tax->label.'</label>
 					<ul class="checkboxes_list">';
			$output_list = wp_list_categories(array(
				'walker'   	=> new Walker_TaxonomiesChecklist(),
				'orderby'  	=> 'name',
				'order'    	=> 'ASC',
				'name'		=> $tax_name, 
				'title_li' 	=> '',
				'style'    	=> 'list',
				'echo'	   	=> 0,
				'show_count' => $this->tfw_options['post_count'],
				'hide_empty' => $this->tfw_options['hide_empty'],
				'taxonomy' 	=> $taxonomy    
			));

			if (trim( $output_list )) {  
				global $wp_query;
				$selected = isset($wp_query->query[$tax_name]) ? $wp_query->query[$tax_name] : get_query_var($tax_name);    
				$output .= $output_list.'<input type="hidden" name="'.$tax_name.'" value="'.$selected.'">'.'</ul></li>';
				echo $output;
			}
		} //end if
	} //end print_checkbox_taxonomy

	/**
	* Prints out the taxonomy using radio buttons 
	* @param 	string 	taxonomy 	Name of the taxonomy to be printed out
	**/
	public function print_radio_taxonomy($taxonomy){      
 		
 		if ( taxonomy_exists($taxonomy) ) {
 			$tax = get_taxonomy($taxonomy); 
 			$name = fix_taxonomy_name($taxonomy);  
			$output = '
				<li class="'.$taxonomy.'_li">
					<label class="taxlabel">'.$tax->label.'</label>
					<ul class="radio_list">';
			$output_list = wp_list_categories(array(
				'walker'   => new Walker_TaxonomiesRadio(),
				'name'     => $name,       // name of the input
				'orderby'  => 'name',
				'order'    => 'ASC',
				'show_option_none' => '',
				'title_li' => 0,
				'style'    => 'list',
				'echo'     => 0,
				'show_count' => $this->tfw_options['post_count'],
				'hide_empty' => $this->tfw_options['hide_empty'],
				'taxonomy' => $taxonomy    
			));
			
			if (trim( $output_list )) {
				$output .= $output_list.'</ul></li>';
				echo $output;
			}

		} //end if
	} //end print_radio_taxonomy

	/**
	* Prints out the custom field filter using the input mode
	* @param 	array 	cf 	Array containing the Custom Field parameters (name, label, mode, min and max)
	**/
	public function print_input_custom_field($cf){      
 		
 		$min_name = $cf['name'].'_min';
 		$max_name = $cf['name'].'_max';

 		$min_val = (isset($_GET[$min_name])) ? esc_attr($_GET[$min_name]) : $cf['min'];
 		$max_val = (isset($_GET[$max_name])) ? esc_attr($_GET[$max_name]) : $cf['max'];
 		
		echo '<li class="'.$cf['name'].'_li"><label class="taxlabel">'.$cf['label'].'</label><br/>';
			
			echo "<label>".$cf['unit']."<input type='text' name='".$min_name."' value='".$min_val."' class='input_cf' /></label>";
			echo "<label> - ".$cf['unit']."<input type='text' name='".$max_name."' value='".$max_val."' class='input_cf' /></label>";

		echo '</li>';

	} //end print_input_custom_field


	/**
	* Prints out the custom field filter using the range mode
	* @param 	array 	cf 	Array containing the Custom Field parameters (name, label, mode, min and max)
	**/
	public function print_range_custom_field($cf){      
 		
 		$min_name = $cf['name'].'_min';
 		$max_name = $cf['name'].'_max';

 		$min_val = (isset($_GET[$min_name])) ? esc_attr($_GET[$min_name]) : $cf['min'];
 		$max_val = (isset($_GET[$max_name])) ? esc_attr($_GET[$max_name]) : $cf['max'];
 		
		echo '<li class="'.$cf['name'].'_li"><label class="taxlabel">'.$cf['label'].'</label>';
			echo "<span>".$cf['unit'].$min_val." - ".$cf['unit'].$max_val."</span><br/>";
			echo "<div class='noUiSlider ".$cf['name']."_slider'></div>";
		echo '</li>';
		?>
		<script type="text/javascript">
		(function ($) {
			"use strict";
			$(function () {
				$(".<?php echo $cf['name']; ?>_slider").noUiSlider({
						    range: [<?php echo $cf['min']; ?>, <?php echo $cf['max']; ?>]
						   ,start: [<?php echo $min_val; ?>, <?php echo $max_val; ?>]
						   ,handles: 2
						   <?php if($cf['step'] != ''){ echo ",step: ".$cf['step'];} ?>
						   ,slide: function(){
						      var values = $(this).val();
						      $(this).parent().find("span").text(
						         "<?php echo $cf['unit']; ?>" + values[0] + " - <?php echo $cf['unit']; ?>" + values[1]
						      );
						   }
						   ,serialization: {
						       to: ["<?php echo $min_name; ?>", "<?php echo $max_name; ?>"]
						      ,resolution: 0.01
						   }
				});
			});	
		}(jQuery));		
		</script>
		<?php		

	} //end print_range_custom_field

	/**
	* Load specific template when form is submitted
	**/

	public function results_template(){
		
		global $wp_query;
	    if ( isset($_GET['post_type']) && is_main_query() && 'custom' == $this->tfw_options['results_template'] ){
	    	// if the option is set for a custom template, load it
	    	if ( $this->filters[$this->number]['tfw_post_type'] == $_GET['post_type'] && !empty($this->tfw_options['custom_template']) ){
	    		locate_template( $this->tfw_options['custom_template'], true, true );
	    		exit;
			}
	    } // end if
	} // end results_template

	/**
	*
	* Filter by custom field
	*
	**/
	public function filter_by_custom_field($wp_query){
		//global $wp_query;
		if ($wp_query->is_main_query() && $this->number > 1 && isset($_GET['post_type']) ) {
			
			$selected_filters = $this->filters[$this->number]['selected_filters'];
			$meta_query = array();
			foreach ($selected_filters as $filter) {
				if(isset($filter['max']) && isset($_GET[$filter['name'].'_max'])){
					$meta_query[] = array(
						'key' => $filter['name'],
						'value' => array( esc_attr($_GET[$filter['name'].'_min']), esc_attr($_GET[$filter['name'].'_max']) ),
						'type' => 'DECIMAL',
						'compare' => 'BETWEEN'
					);
					
				}
			}
			
			$wp_query->set( 'meta_query', $meta_query );
			
			// handle template tags
			$wp_query->is_category = false;
			$wp_query->is_tag 	= false;
			$wp_query->is_tax = false;
			$wp_query->is_post_type_archive = false;
			switch ($this->tfw_options['results_template']) {
		    		case 'search'	:$wp_query->is_search 	= true; break;
		    		case 'archive'	:$wp_query->is_archive 	= true; break;
		    		case 'category'	:$wp_query->is_category = true; break;
		    		case 'tag'		:$wp_query->is_tag 		= true; break;
		    		case 'taxonomy'	:$wp_query->is_tax 		= true; break;
		    		case 'home'		:$wp_query->is_home 	= true; break;
		    		case 'posttype'	:$wp_query->is_post_type_archive = true; break;
		    		case 'custom'	:$wp_query->is_post_type_archive = true; break;
		    		default: /* do nothing */	break;
		    } // end switch
			
		}
		return $wp_query;
		
	} // end filter_by_custom_field	



	/**
	* Recursive search to check if a values exists in an array of arrays 
	**/
	public function in_array_r($needle, $haystack, $strict = false) {
	    foreach ($haystack as $item) {
	        if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && $this->in_array_r($needle, $item, $strict))) {
	            return true;
	        }
	    }
	    return false;
	} // end in_array_r

	/**
	* Cast decimal precision to meta_query type: DECIMAL
	**/
	public function cast_decimal_precision( $array ) {
		$array['where'] = str_replace('DECIMAL','DECIMAL(10,3)',$array['where']);
		return $array;
	}

} // end class


add_action('widgets_init', create_function('', 'return register_widget("Taxonomies_Filter_Widget");'));
