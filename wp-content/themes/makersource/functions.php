<?php

add_action( 'wp_enqueue_scripts', 'enqueue_parent_theme_style' );
function enqueue_parent_theme_style() {
 
   wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
}

add_action( 'init', 'makersource_add_rewrite_rules' );
function makersource_add_rewrite_rules() {
    add_rewrite_rule(
        'blog/?$',
        'index.php?post_type=post',
        'top'
    );
}

function makersource_debug_page_request() {
  global $wp, $template;

  echo '<!-- Request: ';
  echo empty($wp->request) ? 'None' : esc_html($wp->request);
  echo " -->\r\n";
  echo '<!-- Matched Rewrite Rule: ';
  echo empty($wp->matched_rule) ? 'None' : esc_html($wp->matched_rule);
  echo " -->\r\n";
  echo '<!-- Matched Rewrite Query: ';
  echo empty($wp->matched_query) ? 'None' : esc_html($wp->matched_query);
  echo " -->\r\n";
  echo '<!-- Loaded Template: ';
  echo basename($template);
  echo " -->\r\n";
}

function get_facetious_search_params() {
	$result = array();
	$val = get_search_query();
	if ( $val ) 
	 	$result[] = 'keyword "'.$val.'"';
	$val = get_query_var( 'post_type' );
	if ( $val )
		$result[] = 'type "'.$val.'"';
	foreach ( get_taxonomies( array( 'public' => true ), 'objects' ) as $tax ) {
		$key = $tax->name;
		$val = get_query_var( $key );
		if ( $val )
			$result[] = $key.' "'.$val.'"';
	}
	return implode( ' and ', $result);
}

function get_project_resource_type( $post_id ) {
	$terms = get_the_terms( $post_id, 'resource_type' );					
	if ( $terms && !is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			return $term->name;
		}
	}
	return '';
}

function get_project_resource_links( $proj_id ) {
	$resource_list = array();
	$pod = pods( 'project', $proj_id );
	$related = $pod->field( 'project_resources' );
	if ( ! empty( $related ) ) {
		foreach ( $related as $rel ) {
			$res_id = $rel['ID'];
			$resource_list[] = array(
				'ID'    => $res_id,
				'type'  => get_project_resource_type( $res_id ),
				'title' => get_the_title( $res_id ),
				'link'  => get_permalink( $res_id ) );
		}
	}
	return $resource_list;
}

function get_resource_project_links( $res_id ) {
	$pod = pods( 'project_resource', $res_id );
	$parent = $pod->field( 'resource_parent' );
	if ( !empty( $parent ) ) {
		$proj_id = $parent['ID'];
		$proj = array(
			'ID'    => $proj_id,
			'type'  => 'Project',
			'title' => get_the_title( $proj_id ),
			'link'  => get_permalink( $proj_id ) );
			
		$resource_list = get_project_resource_links( $proj_id );
		$last = null;
		$take_next = false;
		$prev = null;
		$next = null;
		foreach ( $resource_list as $res ) {
			if ( $res['ID'] == $res_id ) {
				$prev = $last;
				$take_next = true;
			} elseif ( $take_next ) {
				$next = $res;
				break;
			}
			$last = $res;
		}
		return array(
			'project' => $proj,
			'prev' => $prev,
			'next' => $next );
	}
	return array();
}