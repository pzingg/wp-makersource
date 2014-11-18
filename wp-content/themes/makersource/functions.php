<?php

add_action( 'wp_enqueue_scripts', 'enqueue_parent_theme_style' );
function enqueue_parent_theme_style() {
    wp_enqueue_style( 'parent-style', get_template_directory_uri().'/style.css' );
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