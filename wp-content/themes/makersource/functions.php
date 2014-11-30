<?php

define( 'MAKERSOURCE_THEME_FOLDER', dirname(__FILE__) );
$msjs = 'not set';

/*
 * Action to send "/blog" url to post archives.
 */
add_action( 'init', 'makersource_add_rewrite_rules' );
function makersource_add_rewrite_rules() {
    add_rewrite_rule(
        'blog/?$',
        'index.php?post_type=post',
        'top'
    );
}

/* 
 * Action to set up our child theme on init.
 */
add_action( 'wp_enqueue_scripts', 'enqueue_parent_theme_style' );
function enqueue_parent_theme_style() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
}

/* 
 * Action to set up better child/parent UI for Pods relationships.
 */
add_action( 'admin_init', 'makersource_admin_init' );
function makersource_admin_init() {
    // wp_enqueue_style('msmeta_css', MAKERSOURCE_THEME_FOLDER . '/custom/meta.css');

    // add a meta box for each of the wordpress page types: posts and pages
    foreach (array('project') as $type) {
        add_meta_box( 
			'makersource_' . $type . '_meta', 
			'Add a Resource', 
			'msmeta_setup', 
			$type, 
			'normal', 
			'core' 
		);
    }
}

/* 
 * Metabox for new project in wp-admin.
 */
function msmeta_setup() {
    include( MAKERSOURCE_THEME_FOLDER . '/metabox.php' );
}

/* 
 * Action to set up required javascript for add new project resource.
 */
add_action( 'admin_enqueue_scripts', 'makersource_admin_scripts' );
function makersource_admin_scripts() {
	global $msjs;
	
	$msjs = get_stylesheet_directory_uri() . '/makersource.js';
	wp_enqueue_script( 'jquery-ui-autocomplete' );
	wp_register_script( 'makersource', $msjs, array('jquery', 'jquery-ui-autocomplete'), null );
	wp_enqueue_script( 'makersource' );
}

/* 
 * Filter to set up resource_type and parent_resource meta for 
 * related project_resource type posts in wp-admin.
 *
 * How metaboxes are set up in WordPress:
 * post_categories_meta_box for hierarchical (checkboxes)
 *   -> wp_terms_checklist
 *    template.php:189 sets up 'selected_cats' to post object's terms
 *     -> wp_get_object_terms
 *     -> Walker_Category_Checklist::walk
 *       -> <input id="in-cat_name-termid" type="checkbox"...
 *
 * post_tags_meta_box for non-hierarchical (autocomplete)
 * 
 */
add_action( 'edit_form_top', 'makersource_new_project_resource' );
function makersource_new_project_resource( $post ) {
	if ( 0 != $post->ID && 'project_resource' == $post->post_type ) {
		/* taxonomy terms */
		foreach (array('resource_type') as $taxonomy) {
			if ( !empty( $_REQUEST[$taxonomy] ) ) {
				$terms = array($_REQUEST[$taxonomy]);
				wp_set_object_terms( $post->ID, $terms, $taxonomy, false );
			}
		}
	}
}

/* 
 * Filter to set up Pods relationship pick field.
 */
add_filter( 'pods_form_ui_field_pick_value', 'makersource_pods_pick_value', 10, 5 );
function makersource_pods_pick_value( $value, $name, $options, $pod, $id ) {
	/* relationship fields */
	$meta_key = str_replace( 'pods_meta_', '', $name );
	if ( in_array($meta_key, array('resource_parent') ) ) {
		if ( !empty( $_REQUEST[$meta_key] ) ) {
			$value = array( $_REQUEST['resource_parent'] );
		}
	}
	return $value;
}

/* add_action( 'save_post', 'msmeta_save_post' ); */
function msmeta_save_post( $post_id ) {
	if ( !isset( $_POST['msmeta_nonce'] ) ) {
		return;
	}
	if ( !wp_verify_nonce( $_POST['msmeta_nonce'], 'makersource_meta_box' ) ) {
		return;
	}
	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( 'project' != $_POST['post_type'] ) {
		return;
	}
	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {
		if ( !current_user_can( 'edit_page', $post_id ) ) {
			return;
		}
	} else {
		if ( !current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
	}
	if ( ! isset( $_POST['myplugin_new_field'] ) ) {
		return;
	}
	// do something with custom data
}

/*
 * For project resoures, append the type to the tile
 */
add_filter( 'the_title', 'makersource_resource_title', 10, 2 );
function makersource_resource_title( $title, $post_id = null ) {
	if ( $post_id ) {
		$res_type = get_project_resource_type( $post_id );
		if ( $res_type ) {
			$title .= ' (' . $res_type . ')';
		}
	}
	return $title;
}

/*
 * Action to append related links and bookmark widget when project
 * content is displayed on content.php template.
 */
add_action( 'the_content', 'makersource_content', 10 );
function makersource_content( $content ) {
	$content .= makersource_project_resource_links();
	$content .= makersource_bookmark_widget();
	return $content;
}

/* 
 * Ajax action based on wp_ajax_autocomplete_user()
 */
add_action( 'wp_ajax_autocomplete_post', 'makersource_ajax_autocomplete_post' );
function makersource_ajax_autocomplete_post() {
	global $wpdb;
	
	$return = array();
	
	// Must specify a post type
	$post_type = isset( $_REQUEST['post_type'] ) ? $_REQUEST['post_type'] : 'project_resource';

	// Must specify a title fragment
	$q = trim( isset( $_REQUEST['term'] ) ? $_REQUEST['term'] : '' );
	if ( '' != $q ) {
		// Check the type of request
		// Current allowed values are `add` and `search`
		if ( isset( $_REQUEST['autocomplete_type'] ) && 'search' === $_REQUEST['autocomplete_type'] ) {
			$type = $_REQUEST['autocomplete_type'];
		} else {
			$type = 'add';
		}
		
		// Exclude current related posts
		$existing_posts = isset( $_REQUEST['existing_posts'] ) ? 
			array_map( 'absint', explode( ',', $_REQUEST['existing_posts'] ) ) : array();
		$include_posts = ( $type == 'search' ? $existing_posts : array() );
		$exclude_posts = ( $type == 'add' ? $existing_posts : array() );

		$like_posts = $wpdb->get_col( "SELECT ID FROM " . $wpdb->posts . " WHERE post_title LIKE '" . $q . "%'" );
		$posts = get_posts( array(
			'post__in'         => $like_posts,
			'orderby'          => 'title',
			'order'            => 'ASC',
			'include'          => $include_posts,
			'exclude'          => $exclude_posts,
			'post_type'        => $post_type,
			'post_status'      => 'publish',
			'suppress_filters' => true
		) );

		foreach ( $posts as $post ) {
			$res_type = get_project_resource_type( $post->ID );
			$label = $post->post_title;
			if ( $res_type ) {
				$label .= ' (' . $res_type . ')';
			}
			$return[] = array(
				'label' =>     $label,
				'title' =>     $post->post_title,
				'ID' =>        $post->ID,
				'post_type' => $post->post_type,
				'author' =>    $post->post_author,
				'status' =>    $post->post_status,
				'resource_type' => $res_type
			);
		}
	}
	wp_die( wp_json_encode( $return ) );
}


/*
function msmeta_show_existing( ) {
	foreach ( $children as $link_id => $child ) {
		$child_id = $child->ID;

		$edit_url = get_admin_url() . "post.php?post={$child_id}&amp;action=edit&amp;sp_parent=" . SP_Parent_Param::generate_sp_parent_param( $post->ID, $sp_pt_link, $sp_parent, 0 ) . "&sp_pt_link=" . $this->connection->get_id();

		echo "<tr id='{$link_id}'>\n";
		echo "<td>";
		echo "<strong><a href='{$edit_url}' class='row-title' title='{$child->post_title}'>{$child->post_title}</a></strong>\n";
		echo "<div class='row-actions'>\n";
		echo "<span class='edit'><a href='{$edit_url}' title='" . __( 'Edit this item', 'post-connector' ) . "'>";
		_e( 'Edit', 'post-connector' );
		echo "</a> | </span>";
		echo "<span class='trash'><a class='submitdelete' title='" . __( 'Delete this item', 'post-connector' ) . "' href='javascript:;'>";
		_e( 'Delete', 'post-connector' );
		echo "</a></span>";
		echo "</div>\n";
		echo "</td>\n";
		echo "</tr>\n";
		$i ++;
	}
	echo "</tbody>\n";
	echo "</table>\n";

} else {

	echo '<br/>';
	printf( __( 'No %s found.', 'post-connector' ), $child_post_type->labels->name );
}
*/

function get_public_taxonomy_terms( $post_id = false ) {
	if ( !empty( $post_id ) ) {
		$post_taxonomies = array();
		$post_type = get_post_type( $post_id );
		$taxonomies = get_object_taxonomies( $post_type , 'object' );

		foreach ( $taxonomies as $tax ) {
			if ( $tax->public ) {
				$taxonomy = $tax->name;
				$term_links = array();
				$terms = get_the_terms( $post_id, $taxonomy );

				if ( is_wp_error( $terms ) )
					return $terms;

				if ( $terms ) {
					foreach ( $terms as $term ) {
						$link = get_term_link( $term, $taxonomy );
						if ( is_wp_error( $link ) )
							return $link;
						$term_links[] = '<a href="' . esc_url( $link ) . '" rel="' . $taxonomy . '">' . $term->name . '</a>';
					}
				}

				$term_links = apply_filters( "term_links-$taxonomy" , $term_links );
				$post_terms[$taxonomy] = $term_links;
			}
		}
		return $post_terms;
	}
	return false;
}

function get_public_taxonomies_list( $separator = '', $post_id = false ) {
	if ( !$post_id ) {
		$post_id = get_the_ID();
	}
	if ( $post_id ) {
		$my_terms = get_public_taxonomy_terms( $post_id );
		if ( $my_terms ) {
			$my_taxonomies = array();
			foreach ( $my_terms as $taxonomy => $terms ) {
				$my_taxonomy = get_taxonomy( $taxonomy );
				if ( !empty( $terms ) )          
					$my_taxonomies[] = '<span class="' . $my_taxonomy->name . '-links">' . '<span class="entry-utility-prep entry-utility-prep-' . $my_taxonomy->name . '-links">' . $my_taxonomy->labels->name . ': ' . implode( $terms , ', ' ) . '</span></span>';
			}

			if ( !empty( $my_taxonomies ) ) {
				$thelist = '';
				if ( '' == $separator ) {
					$thelist .= '<ul class="post-categories">';
        			foreach ( $my_taxonomies as $my_taxonomy ) {
						$thelist .= "\n\t<li>" . $my_taxonomy . '</li>';
        			}
        			$thelist .= '</ul>';
      			} else {
					$i = 0;
					foreach ( $my_taxonomies as $my_taxonomy ) {
						if ( 0 < $i )
							$thelist .= $separator;
						$thelist .= $my_taxonomy;
					++$i;
					}
				}
				return $thelist;
			}
		}
	} 
	return false;
}

function get_author_projects_url( $author_id, $post_type = 'project' ) {
	$link = get_author_posts_url( $author_id );
	$link .= '?post_type=' . $post_type;
	return $link;
}

function makersource_project_resource_links() {
	$output = '';
	if ( is_single() ) {
		$pt = get_post_type();
		if ( $pt == 'project' ) {
			$resources = get_project_resource_links( get_the_id() );
			if ( !empty( $resources['all'] ) ) {
				$output .= '<div class="entry-related-links">';
				$output .= '<h4 class="entry-related-header">Project Resources</h4>';
				$output .= '<ul>';
				foreach ( $resources['all'] as $res ) {
					$output .= '<li><a href="'.$res['view'].'">'.$res['title'].'</a></li>';
				}
				$output .= '</ul></div>';
			}
		} elseif ( $pt == 'project_resource' ) {
			$projects = get_resource_project_links( get_the_id() );
			if ( !empty( $projects ) ) {
				$output .= '<div class="entry-related-links">';
				$output .= '<h4 class="entry-related-header">Projects That Include This Resource</h4>';
				$output .= '<ul>';
				foreach ( $projects as $project ) {
					$output .= '<li>' . $project['type'] . ': <a href="'.$project['view'].'">' . $project['title'] . '</a>';
					if ( $project['prev_resource'] || $project['next_resource'] )  {
						$output .= '<ul>';
						if ( $project['prev_resource'] ) {
							$output .= '<li>&lt; Previous in ' . $project['title'] . 
								' - <a href="' . $project['prev_resource']['view'] . '">' . 
								$project['prev_resource']['title'] . '</a></li>';
						}
						if ( $project['next_resource'] ) {
							$output .= '<li>&gt; Next in ' . $project['title'] . 
								' - <a href="' . $project['next_resource']['view'] . '">' .
								$project['next_resource']['title'] . '</a></li>';
						}
						$output .= '</ul>';
					}
					$output .= '</li>';
				}
				$output .= '</ul></div>';
			}
		}
	}
	return $output;
}

function get_post_type_name() {
	$pt = get_post_type();
	$lookup = array(
		'post' => 'Post',
		'page' => 'Page',
		'project' => 'Project',
		'project_resource' => 'Resource');
	return isset( $lookup[$pt] ) ? $lookup[$pt] : 'Post';
}

function makersource_bookmark_widget() {
	$output = '';
	if (userpro_is_logged_in()) {
		global $userpro_fav; 		
		$output .= '<div class="entry-bookmark">';
		$output .= '<h4 class="entry-bookmark-header">Bookmark This ' . get_post_type_name() . '</h4>';
		$output .= $userpro_fav->bookmark();
		$output .= '</div>';
	}
	return $output;
}

/**
 * Print HTML with meta information for current post: categories, tags, permalink, author, and date.
 *
 * Create your own twentythirteen_entry_meta() to override in a child theme.
 *
 * @since Twenty Thirteen 1.0
 */
function twentythirteen_entry_meta() {
	if ( is_sticky() && is_home() && ! is_paged() )
		echo '<span class="featured-post">' . __( 'Sticky', 'twentythirteen' ) . '</span>';

	$pt = get_post_type();
	if ( ! has_post_format( 'link' ) && 'post' == $pt )
		twentythirteen_entry_date();

	// Translators: used between list items, there is a space after the comma.
	$categories_list = get_the_category_list( __( ', ', 'twentythirteen' ) );
	if ( $categories_list ) {
		echo '<span class="categories-links"><span class="entry-utility-prep entry-utility-prep-categories-links">Categories: ' . $categories_list . '</span></span><br/>';
	}

	// Translators: used between list items, there is a space after the comma.
	$tag_list = get_the_tag_list( '', __( ', ', 'twentythirteen' ) );
	if ( $tag_list ) {
		echo '<span class="tags-links"><span class="entry-utility-prep entry-utility-prep-tags-links">Tags: ' . $tag_list . '</span></span><br/>';
	}
	
	$taxonomies_list = get_public_taxonomies_list( '<br/>' );
	if ( $taxonomies_list ) {
		echo '<span class="categories-links">' . $taxonomies_list . '</span><br/>';
	}
	
	// Post author
	if ( in_array( $pt, array( 'post', 'project', 'project_resource' ) ) ) {
		global $userpro;
		$author_id = get_the_author_meta( 'ID' );
		$post_count = count_user_posts( $author_id );
		$project_count = count_user_posts( $author_id, 'project' );
		$profile_url = $userpro->permalink( $author_id ); 
		$author_display_name = get_the_author( );
		/* 
		 * /profile/pzingg for author profile
		 * /search/author_name/pzingg for all contributions
		 * /search/type/project/author_name/pzingg for projects
		 * /search/blog/author/pzingg for posts
		 */
		printf( '<span class="author vcard"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
			esc_url( $profile_url ),
			esc_attr( sprintf( __( "View %s's profile", 'twentythirteen' ), $author_display_name ) ),
			$author_display_name
		);
		if ( $project_count > 0 ) {
			printf( ' <span class="author-projects-links"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$d %4$s</a></span>',
				esc_url( get_author_projects_url( $author_id ) ),
				esc_attr( sprintf( __( "View all projects by %s", 'twentythirteen' ), $author_display_name ) ),
				$project_count,
				$project_count > 1 ? "Projects" : "Project"
			);
		}
		if ( $post_count > 0 ) {
			printf( ' <span class="author-posts-links"><a class="url fn n" href="%1$s" title="%2$s" rel="author">%3$d %4$s</a></span>',
				esc_url( get_author_posts_url( $author_id ) ),
				esc_attr( sprintf( __( "View all posts by %s", 'twentythirteen' ), $author_display_name ) ),
				$post_count,
				$post_count > 1 ? "Posts" : "Post"
			);
		}
	}
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

/* 
 * Get the first resource type associated with the project resource 
 */
function get_project_resource_type( $post_id, $with_order = false ) {
	$terms = get_the_terms( $post_id, 'resource_type' );
	if ( $terms && !is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( $with_order ) {
				return array( $term->name, $term->custom_order );
			} else {
				return $term->name;
			}
		}
	}
	return '';
}

function get_project_resource_ids( $proj_id ) {
	$resource_list = array();
	$pod = pods( 'project', $proj_id );
	$related = $pod->field( 'project_resources' );
	if ( ! empty( $related ) ) {
		foreach ( $related as $rel ) {
			$resource_list[] = $rel['ID'];
		}
	}
	return $resource_list;
}

function resource_link_from_id( $res_id ) {
	$rt_array = get_project_resource_type( $res_id, true );
	$title = get_the_title( $res_id );
	return array(
		'ID'    => $res_id,
		'type'  => $rt_array[0],
		'order' => sprintf('%02d-%s', $rt_array[1], $title ),
		'title' => $title,
		'view'  => get_permalink( $res_id ),
		'edit'  => get_admin_url() . "edit.php?post_type=project_resource&id=" . $res_id,
 	);
}

function sort_resource_links( $a, $b ) {
	return strcmp( $a['order'], $b['order'] );
}

function get_project_resource_links( $proj_id, $res_id = null ) {
	$resources = array_map( 'resource_link_from_id',  get_project_resource_ids( $proj_id ) );
	usort( $resources, 'sort_resource_links' );
	$prev = null;
	$next = null;
	if ( $res_id ) {
		$last = null;
		$take_next = false;
		foreach ( $resources as $res ) {
			if ( $res['ID'] == $res_id ) {
				$prev = $last;
				$take_next = true;
			} elseif ( $take_next ) {
				$next = $res;
				break;
			}
			$last = $res;
		}
	}
	return array(
		'all'  => $resources,
		'prev' => $prev,
		'next' => $next
	);
}

function get_resource_project_ids( $res_id ) {
	$project_list = array();
	$pod = pods( 'project_resource', $res_id );
	$parents = $pod->field( 'resource_parent' );
	if ( !empty( $parents ) ) {
		foreach ( $parents as $parent ) {
			$project_list[] = $parent['ID'];
		}
	}
	return $project_list;
}

function project_link_from_id( $proj_id ) {
	return array(
		'ID'    => $proj_id,
		'type'  => 'Project',
		'title' => get_the_title( $proj_id ),
		'view'  => get_permalink( $proj_id ),
		'edit'  => get_admin_url() . "edit.php?post_type=project&id=" . $proj_id
	);
}

function sort_project_links( $a, $b ) {
	return strcmp( $a['title'], $b['title'] );
}

function get_resource_project_links( $res_id ) {
	$projects = array_map( 'project_link_from_id', get_resource_project_ids( $res_id ) );
	usort( $projects, 'sort_project_links' );
	$prev = null;
	$next = null;
	if ( empty($projects) ) {
		return array();
	}
	for ( $i = 0; $i < count( $projects ); $i++ ) {
		$resources = get_project_resource_links( $projects[$i]['ID'], $res_id );
		$projects[$i]['next_resource'] = $resources['next'];
		$projects[$i]['prev_resource'] = $resources['prev'];
	}
	return $projects;
}
