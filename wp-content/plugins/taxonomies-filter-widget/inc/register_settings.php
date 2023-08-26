<?php

		register_setting('tfw_options_group', 'tfw_options', array($this, 'callback_check'));
		
		
		/*================== General Options ===================*/
		
		add_settings_section(
		    'tfw_options_general',
		    'General Options',
		    array($this, 'section_general'),
		    'tfw_options_page'
		);	
			
			add_settings_field(
			    'auto_submit', 
			    'Auto submit form?', 
			    array($this, 'field_auto_submit'), 
			    'tfw_options_page',
			    'tfw_options_general'			
			);
			add_settings_field(
			    'hide_empty', 
			    'Hide empty terms?', 
			    array($this, 'field_hide_empty'), 
			    'tfw_options_page',
			    'tfw_options_general'			
			);
			add_settings_field(
			    'display_search_box', 
			    'Display the search box?', 
			    array($this, 'field_display_search_box'), 
			    'tfw_options_page',
			    'tfw_options_general'			
			);
			add_settings_field(
			    'display_reset_button', 
			    'Display the reset button?', 
			    array($this, 'field_display_reset_button'), 
			    'tfw_options_page',
			    'tfw_options_general'			
			);
			add_settings_field(
			    'post_count', 
			    'Display post count:', 
			    array($this, 'field_post_count'), 
			    'tfw_options_page',
			    'tfw_options_general'			
			);
			add_settings_field(
			    'multiple_relation', 
			    'Multiple selection relation:', 
			    array($this, 'field_multiple_relation'), 
			    'tfw_options_page',
			    'tfw_options_general'			
			);
			add_settings_field(
			    'results_template', 
			    'Search results template:', 
			    array($this, 'field_results_template'), 
			    'tfw_options_page',
			    'tfw_options_general'			
			);
			add_settings_field(
			    'custom_template', 
			    'Custom template file name:<br/><span style="font-size:0.9em;">Use this field if <strong>Search result template</strong> is set to <strong>Custom Template</strong></span> : <pre>Ex: my_custom_template.php</pre>', 
			    array($this, 'field_custom_template'), 
			    'tfw_options_page',
			    'tfw_options_general'			
			);

			
			

		/*================= Labels ====================*/	
		/*
		add_settings_section(
		    'tfw_options_labels',
		    'Labels',
		    array($this, 'section_labels'),
		    'tfw_options_page'
		);	
			
			add_settings_field(
			    'search_button', 
			    'Search button label:', 
			    array($this, 'field_search_button'), 
			    'tfw_options_page',
			    'tfw_options_labels'			
			);
			add_settings_field(
			    'reset_button', 
			    'Reset button label:', 
			    array($this, 'field_reset_button'), 
			    'tfw_options_page',
			    'tfw_options_labels'			
			);
			add_settings_field(
			    'search_box', 
			    'Search box label:', 
			    array($this, 'field_search_box'), 
			    'tfw_options_page',
			    'tfw_options_labels'			
			);
		
		*/
			