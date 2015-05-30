<?php $links = get_project_resource_links( get_the_id() ); ?>
<?php if ( ! empty( $links ) ) : ?>
<p>To unlink a project resource from this project, check the 
	Unlink Resource box, then update this post.</p>
<table class='form-table'>
<?php foreach ( $links as $res ) : ?>
	<tr><th style='width:60%'><?php echo $res['title'] . ' (' . $res['type'] . ')'; ?></th>;
	<td><input type='checkbox' name='msmeta_unlink_resource_'<?php echo $res['ID']; ?>' id='msmeta-unlink-<?php echo $res['ID']; ?>' />
	<label for='msmeta-unlink-<?php echo $res['ID']; ?>'>Unlink Resource</label></td></tr>
<?php endforeach; ?>
</table>
<?php endif; ?>

<input type='hidden' id='msmeta-resource-link-id' name='msmeta_link_id' value='' />
<p>To link an existing project resource, save all updates to this project, 
then type in the search box for the title of of resource, select the match, 
check the Link Resource box, then update this post.</p>
<table class='form-table'>
<tr><th style='width:60%;font-weight:normal'><span style='font-weight:600'>Link Existing&nbsp;</span>
<input type='text' id='msmeta-resource-link-ac' value='' /></th>
  <td><input type='checkbox' name='msmeta_link_existing' id='msmeta-link-existing' />
  <label for='msmeta-link-existing'>Link Resource</label></td></tr></table>

