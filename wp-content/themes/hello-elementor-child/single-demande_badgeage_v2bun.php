<?php
/**
 * The template for displaying singular post-types: posts, pages and user-defined custom post types.
 *
 * @package HelloElementor
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
get_header(); 
?>
<?php
the_content();
$id = get_the_id();
$insertion = get_post_meta($id, "insertion_suppression");
$date = get_post_meta($id, "date");
$heure = get_post_meta($id, "heure");
$motif = get_post_meta($id, "motif");
$status = get_post_meta($id, "decision_responsable");
var_dump($status);
while ( have_posts() ) :
	the_post();
	?>
<div class="post-data" style="border: solid 5px; border-radius: 30px">
	<p style="font-weight: bolder; font-size: 3em; text-align: center;">Demande de : <?php the_author(); ?></p>
	<div style="margin: 0% 30% 0% 30%";>
    <p><b>Date de la demande :</b> <?php the_time('d F Y'); ?></p>
	<p><b>Type de la demande :</b> <?php echo $insertion[0]; ?></p>
	<p><b>Date concerné :</b> <?php echo date('d/m/Y',strtotime($date[0]));; ?></p>
	<p><b>Heure concerné :</b> <?php echo $heure[0]; ?></p>
	<p><b>Motif :</b> <?php echo $motif[0]; ?></p>
	</div>
</div>
<main id="content" <?php post_class( 'site-main' ); ?> role="main">
	<?php if ( apply_filters( 'hello_elementor_page_title', true ) ) : ?>
		<header class="page-header">
		</header>
	<?php endif; ?>
	<div class="page-content">
		<?php the_content(); ?>
		<div class="post-tags">
			<?php the_tags( '<span class="tag-links">' . __( 'Tagged ', 'hello-elementor' ), null, '</span>' ); ?>
		</div>
		<?php wp_link_pages(); ?>
	</div>

	<?php comments_template(); ?>
</main>

	<?php
get_footer();
endwhile;