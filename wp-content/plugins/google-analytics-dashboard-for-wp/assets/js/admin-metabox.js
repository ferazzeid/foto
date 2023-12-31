jQuery(document).ready(function () {
  if (0 === jQuery('#exactmetrics-metabox-page-insights').length) {
    return;
  }

  jQuery('#exactmetrics_show_page_insights').click(function (event) {
    event.preventDefault();
    jQuery('#exactmetrics-page-insights-content').slideDown('slow');
    jQuery('#exactmetrics_show_page_insights').fadeOut('slow');
  });

  jQuery('#exactmetrics_hide_page_insights').click(function (event) {
    event.preventDefault();
    jQuery('#exactmetrics-page-insights-content').slideUp('slow', function () {
      jQuery('#exactmetrics_show_page_insights').fadeIn('slow');
    });
  });

  jQuery('.exactmetrics-page-insights__tabs-tab').click(function (event) {
    event.preventDefault();
    let tab_target = jQuery(this).data('tab');

    jQuery('.exactmetrics-page-insights__tabs-tab.active').removeClass('active');
    jQuery(this).addClass('active');

    jQuery('.exactmetrics-page-insights-tabs-content__tab.active').removeClass('active');
    jQuery('#' + tab_target).addClass('active');
  });
});
