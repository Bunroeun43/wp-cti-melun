<?php
/**
 * The template for displaying singular post-types: posts, pages and user-defined custom post types.
 *
 * @package HelloElementor
 */
get_header();




//function get_display_name($user_id) {
//	if (!$user = get_userdata($user_id))
//		 return false;
//	return $user->data->display_name;
//	var_dump ($user_id);
//}


if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<?php
while ( have_posts() ) :
	the_post();
	?>

<main id="content" <?php post_class( 'site-main' ); ?> role="main">
	<?php if ( apply_filters( 'hello_elementor_page_title', true ) ) : ?>
		<header class="page-header">
			<?php the_title( '<h1 class="entry-title">', '</h1>' ); ?>
		</header>
	<?php endif; ?>
	<div class="page-content">
		<?php the_content(); //the_author();
		$id = get_the_id();
		$my_post = get_post( $id ); // $id - Post ID
		$author_id = $my_post->post_author; var_dump($my_post);
		
		//$user_id = $my_post->post_author;
		$author = get_user_by("id", $author_id);
		var_dump($author);
		
		echo "<p>".get_user_meta($author_id, "first_name")[0]."</p>";
		$heure = get_post_meta($id, "heure");
		echo "<p>".$heure[0]."</p>";
		?>
		<div class="post-tags">
			<?php the_tags( '<span class="tag-links">' . __( 'Tagged ', 'hello-elementor' ), null, '</span>' ); ?>
		</div>
		<?php wp_link_pages(); ?>
	</div>

	<?php comments_template(); ?>
</main>

	<?php
endwhile;
