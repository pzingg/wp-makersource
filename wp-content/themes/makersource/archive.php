<?php
/**
 * The template for displaying Archive pages
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * If you'd like to further customize these archive views, you may create a
 * new template file for each specific one. For example, Twenty Thirteen
 * already has tag.php for Tag archives, category.php for Category archives,
 * and author.php for Author archives.
 *
 * @link http://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Thirteen
 * @since Twenty Thirteen 1.0
 */

get_header(); ?>

	<div id="primary" class="content-area">
		<div id="content" class="site-content" role="main">

		<?php if ( have_posts() ) :
			$author_name = false;
			$singular_name = '';
			$plural_name = 'Archives';
			$pt = get_query_var( 'post_type' );
			if ( $pt ) :
				$post_type = get_post_type_object( $pt ); 
				$singular_name = ' ' . $post_type->labels->singular_name;
				$plural_name = $post_type->labels->all_items;
				$author_name = get_query_var( 'author_name' );
				if ( $author_name ) :
					$plural_name .= ' by ' . $author_name;
				endif;
			else :
				$tt = get_query_var( 'taxonomy' );
				if ( $tt ) :
					$sparams = get_facetious_search_params();
					if ( $sparams )
						$plural_name = 'Archives for ' . $sparams;
				endif;
			endif; ?>
			
			<header class="archive-header">
				<h1 class="archive-title"><?php
					if ( is_day() ) :
						printf( __( 'Daily' . $singular_name . ' Archives: %s', 'twentythirteen' ), get_the_date() );
					elseif ( is_month() ) :
						printf( __( 'Monthly' . $singular_name . ' Archives: %s', 'twentythirteen' ), get_the_date( _x( 'F Y', 'monthly archives date format', 'twentythirteen' ) ) );
					elseif ( is_year() ) :
						printf( __( 'Yearly' . $singular_name . ' Archives: %s', 'twentythirteen' ), get_the_date( _x( 'Y', 'yearly archives date format', 'twentythirteen' ) ) );
					else :
						_e( $plural_name, 'twentythirteen' );
					endif;
				?></h1>
			</header><!-- .archive-header -->

			<?php if ( $author_name ) :
				if ( get_the_author_meta( 'description' ) ) :
					get_template_part( 'author-bio' );
				endif;
			endif; ?>
			
			<?php /* The loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'content', get_post_format() ); ?>
			<?php endwhile; ?>

			<?php twentythirteen_paging_nav(); ?>

		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>

		</div><!-- #content -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>