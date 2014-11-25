jQuery(document).ready( function() {
  /* add resource_type to query_string when button is clicked */
  jQuery('#msmeta-resource-new').click( function() {
    var resource_type = jQuery('#msmeta-resource-type').val();
    if (!resource_type) {
      resource_type = 'example';
    }
    var url = jQuery(this).attr('href');
    if (url) {
      jQuery(this).attr('href', url + '&resource_type=' + resource_type);
    }
    return true;
  });
  
  /* do an ajax lookup for existing resource posts */
  var existing_posts = jQuery('#msmeta-resource-related-ids').val();
  var source_url = ajaxurl + '?action=autocomplete_post&existing_posts=' + existing_posts;
  jQuery('#msmeta-resource-link-ac').autocomplete( {
    source: source_url,
    minLength: 3,
    delay: 500,
    select: function(event, ui) {
      jQuery('#msmeta-resource-link-id').val(ui.item.id);
    },
    open: function() {
			jQuery(this).addClass('open');
		},
		close: function() {
			jQuery(this).removeClass('open');
		}
  });
});