jQuery( document ).ready(
	function(r) {
		var a = {
			action: "bsearch_tracker",
			bsearch_search_query: ajax_bsearch_tracker.bsearch_search_query
		};
		jQuery.post( ajax_bsearch_tracker.ajax_url, a )
	}
);
