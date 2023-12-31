(function ($) {
	"use strict";
	$(function () {


	
		var twfForm = $(".taxonomies-filter-widget-form");
		var twfFormAjax = $(".tfw_auto");
 
		// Handle Ajax DrillDowns 
		twfForm.on('change','.tax-with-childrens', function(e)  {
			e.preventDefault();
		    var myForm = $(this).closest("form");
			var tax = $(this).attr('name');
		    var term = $(this).val();
		    var current_selection = $(this);
		    var ajaxLoader = '<div class="loading_img"></div>';
		    $(this).after(ajaxLoader);
		    $.ajax
		    (
		        {
		        	type: "POST",
		            url: ajax_object.ajax_url,     
		            data: {
				        action: 'get_term_childrens',
				        taxonomy: tax,
				        term: term
				    },
		            success:function(results)
		            {

		                if (results){
		                	$(current_selection).nextAll("#sub_cat_" + tax + ", .loading_img").remove();
			                $(current_selection).after(results);
			                if (myForm.hasClass("tfw_auto")) { myForm.submit(); };
			            } else {
			    			$(current_selection).nextAll("#sub_cat_" + tax + ", .loading_img").remove();
			    			if (myForm.hasClass("tfw_auto")) { myForm.submit(); };
			            }
		            }
		        }
		    ); 
	                                           
		});

		// Select Multiple
		$('.taxonomies-filter-widget-form select[multiple]').change(function()  {
		    var values = $(this).closest('li').find("select[multiple] option:selected").map(function () { return this.value; }).get().join(ajax_object.relation);
			$(this).closest('li').find("input[type=hidden]").val(values);
		});

		// Multiple Checkboxes
		$('.taxonomies-filter-widget-form input:checkbox').change(function()  {
		    var values = $(this).closest('ul.checkboxes_list').find("input:checked").map(function () { return this.value; }).get().join(ajax_object.relation);
			$(this).closest('ul.checkboxes_list').find("input[type=hidden]").val(values);
		});		
		
		// Auto submit 
		twfFormAjax.on('change', ".noUiSlider, input, select:not(.tax-with-childrens)", function() {
		    $(this).closest('form').submit(); 
		})

		// Clear the url from empty elements
	    twfForm.on('submit', function() {

			$(this).find('input:not([name=s], .input_cf), select').each(function() {
	            if (this.value == '0' || this.value == '') {
	                $(this).prop("disabled", true);
	            }
	    	});

	    });

	 	// Enable all previously disabled elements
	    twfForm.find('input[type=text], select').each(function() {
	                $(this).prop("disabled", false);
        });

}); 
	
	
}(jQuery));
