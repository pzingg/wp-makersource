<?php 
function msmeta_resource_type_picker() {
	$terms = get_terms( 'resource_type', array( 'hide_empty' => false ) );
	$output = "<select name='msmeta_resource_type' id='msmeta-resource-type'>";
	$i = 0;
	foreach ( $terms as $term ) {
		$output .= "<option value='" . $term->slug . "'";
		if ( 0 == $i ) $output .= " SELECTED";
		$output .= ">" . $term->name . "</option>";
		$i++;
	}
	$output .= "</select>";
	return $output;
}

function msmeta_new_resource_url() {
	global $post;
	return admin_url() . '/post-new.php?post_type=project_resource&resource_parent=' . $post->ID;
}
?>
<input type='hidden' id='msmeta-resource-related-ids' value='<?php echo implode( ',', get_project_resource_ids( get_the_id() ) ); ?>' />
<?php wp_nonce_field( 'makersource_meta_box', 'msmeta_nonce' ); ?>

<p>To add a new resource linked to this project, save all updates to this project, 
then select the type of resource and click the Create Resource button.</p>
<table class='form-table'>
<tr><th style='width:60%;font-weight:normal'><span style='font-weight:600'>Add New&nbsp;</span>
<?php echo msmeta_resource_type_picker(); ?></th>
   <td><a class='button' id='msmeta-resource-new' href='<?php echo msmeta_new_resource_url(); ?>'>Create Resource</a>
</td></tr></table>
