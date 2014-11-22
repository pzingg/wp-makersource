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

add_action('the_content', 'makersource_related_and_bookmark', 100);
function makersource_related_and_bookmark( $content ) {
	$content .= makersource_project_resource_links();
	$content .= makersource_bookmark_widget();
	return $content;
}

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
			$links = get_project_resource_links( get_the_id() );
			if ( ! empty( $links ) ) {
				$output .= '<div class="entry-related-links">';
				$output .= '<h4 class="entry-related-header">Project Resources</h4>';
				$output .= '<ul>';
				foreach ( $links as $res ) {
					$output .= '<li>'.$res['type'].': <a href="'.$res['link'].'">'.$res['title'].'</a></li>';
				}
				$output .= '</ul></div>';
			}
		} elseif ( $pt == 'project_resource' ) {
			$links = get_resource_project_links( get_the_id() );
			if ( ! empty( $links ) ) {
				$output .= '<div class="entry-related-links">';
				$output .= '<h4 class="entry-related-header">Links</h4>';
				$output .= '<ul>';
				$output .= '<li>'.$links['project']['type'].': <a href="'.$links['project']['link'].'">'.$links['project']['title'].'</a></li>';
				if ( $links['prev'] ) {
					$output .= '<li>&lt; Previous - '.$links['prev']['type'].': <a href="'.$links['prev']['link'].'">'.$links['prev']['title'].'</a></li>';
				}
				if ( $links['next'] ) {
					$output .= '<li>&gt; Next - '.$links['next']['type'].': <a href="'.$links['next']['link'].'">'.$links['next']['title'].'</a></li>';
				}
				$output .= '</ul></div>';
			}
		}
	}
	return $output;
}

function makersource_bookmark_widget() {
	$output = '';
	if (userpro_is_logged_in()) {
		global $userpro_fav; 
		$output .= '<div class="entry-bookmark">';
		$output .= '<h4 class="entry-bookmark-header">Bookmark This Post</h4>';
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