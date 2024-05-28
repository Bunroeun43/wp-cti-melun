<?php
/**
 * The template for displaying singular post-types: posts, pages and user-defined custom post types.
 *
 * @package HelloElementor
 */
acf_form_head();
get_header();
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<?php
the_content();
$user_id = get_current_user_id();
$user = get_user_by("id", $user_id);
$id = get_the_id();
$insertion = get_post_meta($id, "insertion_suppression");
$date = get_post_meta($id, "date");
$heure = get_post_meta($id, "heure");
$motif = get_post_meta($id, "motif");
$status_responsable = get_post_meta($id, "decision_responsable");
$status_direction = get_post_meta($id,"decision_direction");

$my_post = get_post( $id ); // $id - Post ID
$author_id = $my_post->post_author; //var_dump($my_post);

//$user_id = $my_post->post_author;
$author = get_user_by("id", $author_id);

while ( have_posts() ) :
	the_post();
	?>
<div class="post-data">
	
	<div  style="margin: 0% 30% 0% 30%";>
<!-- 	<p>Demande de : <?php/* the_author(); */?></p> 
    <p>Date de la demande : <?php /*the_time('d F Y');*/ ?></p>
	<p>Heure de la demande : <?php/* the_time('g:i A');*/ ?></p>
	<p>Type de la demande : <?php /*echo $insertion[0];*/ ?></p>
	<p>Date concerné : <?php/*echo date('d/m/Y',strtotime($date[0]));;*/ ?></p>
	<p>Heure concerné : <?php/* echo $heure[0]; */?></p>
	<p>Motif : <?php/*echo $motif[0]; */?></p>  -->
	<?php
	
	//var_dump($status);
//	if ($status_responsable == [])	{
//		echo "<p>"."Status : "."en attente";
//	} elseif ($status_responsable[0] == "valider")	{
//		echo "<p>"."Status : "."validé";
//	} elseif ($status_responsable[0] == "refuser")	{
//		echo "<p>"."Status : "."refusé";
//	}else	{
//		echo "<p>"."Status : "."en attente";
//	}

	?>
	
	</div>
</div>
<main id="content" <?php post_class( 'site-main' ); ?> role="main">
	<?php if ( apply_filters( 'hello_elementor_page_title', true ) ) : ?>
		<header class="page-header">
			
		</header>
	<?php endif; ?>
	<div class="page-content">
		<?php the_content();
		echo "<p>"."Demande de : ".get_user_meta($author_id, "first_name")[0]." ".get_user_meta($author_id, "last_name")[0]."</p>";
		echo "<p>"."Date de la demande : ".get_the_time('d F Y' )."</p>";
		echo "<p>"."Type de la demande : ".$insertion[0]."</p>";
		echo "<p>"."Date concernée : ".date('d/m/Y', strtotime($date[0]))."</p>";
		echo "<p>"."Heure concernée : ".$heure[0]."</p>";
		echo "<p>"."Motif : ".$motif[0]."</p>";
		echo "<p>Décision responsable : ".get_field('decision_responsable')."</p>";
		if ($status_responsable[0] == "refuser") {
			echo "<p> Motif du refus du responsable : ".get_field('motif_refus_responsable');
		}
		echo "<p>Décision direction : ".get_field('decision_direction')."</p>";
		if ($status_direction[0] == "refuser") {
			echo "<p> Motif du refus de la direction : ".get_field('motif_refus_direction');
		}
		echo "<p>Traitement Ressources Humaines : ".get_field('traitement_rh')."</p>";
		// if ($status_responsable == [])	{
		// 	echo "<p>"."Décision du responsable : "."en attente";
		// } elseif ($status_responsable[0] == "valider")	{
		// 	echo "<p>"."Décision du responsable : "."validé";
		// } elseif ($status_responsable[0] == "refuser")	{
		// 	echo "<p>"."Décision du responsable : "."refusé";
		// }else	{
		// 	echo "<p>"."Décision du responsable : "."en attente";
		// }
/* 		if ($status_direction == [])	{
			echo "<p>"."Décision de la direction : "."en attente";
		} elseif ($status_direction[0] == "valider")	{
			echo "<p>"."Décision de la direction : "."validé";
		} elseif ($status_direction[0] == "refuser")	{
			echo "<p>"."Décision de la direction : "."refusé";
		}else	{
			echo "<p>"."Décision de la direction : "."en attente";
		} */
		if (in_array("um_directeur", $user->roles) || in_array("um_chef-de-service", $user->roles) || in_array("um_ressources-humaine", $user->roles))	{
		acf_form();}
		// if (in_array("um_direction", $user->roles))	{
		// acf_form(['fields'=>['decision_direction']]);
		// }elseif (in_array("um_responsable-de-service", $user->roles))	{
		// acf_form(['fields'=>['decision_responsable']]);
		// }elseif(in_array("um_ressources-humaines", $user->roles))	{
		// acf_form(['fields'=>['traitement_rh']]);
		// }
		?>
		<div class="post-tags">
			<?php the_tags( '<span class="tag-links">' . __( 'Tagged ', 'hello-elementor' ), null, '</span>' ); ?>
		</div>
		<?php wp_link_pages(); ?>
	</div>

	<?php comments_template(); ?>
</main>

	<?php
endwhile;?>
