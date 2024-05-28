<?php
/**
 * The template for displaying archive pages.
 *
 * @package HelloElementor
 */
get_header();

$user_id = get_current_user_id();
$user = get_user_by("id", $user_id);
if (get_user_meta($user_id, 'service')!=[]){
$service = get_user_meta($user_id, 'service')[0];
$users_service = get_users(['meta_key'=>'service', 'meta_value'=>$service]);




//var_dump($user->roles);



$allusers = get_users();
$allusers_ids = [];
foreach ($allusers as $element){
	array_push($allusers_ids, $element->ID);
}
$users_service_ids = [];
foreach ($users_service as $element){
	array_push($users_service_ids, $element->ID);
}
//var_dump($users_ids);
if (in_array("subscriber", $user->roles) /*&& $_GET['demandes']=='les_miennes'*/)	{
	$arg = array(
		"author__in"=>$user_id,
		"post_type"=>"demande_badgeage",
		//"meta_query"=>['relation'=>'OR',["key"=>"decision_responsable","value"=>"valider"],["key"=>"decision_responsable","value"=>"refuser"]],
	);

} elseif	(in_array("um_responsable-de-service", $user->roles) && $_GET['demandes']=='mon_service'){
	$arg = array(
		"author__in"=>$users_service_ids,
		"post_type"=>"demande_badgeage",
		//"meta_query"=>["key"=>"service"]
	);
} elseif	(in_array("um_direction", $user->roles) && $_GET['demandes']=='direction'){
	$arg = array(
		"author__in"=>$users_service_ids,
		"post_type"=>"demande_badgeage",
		"meta_query"=>[["key"=>"decision_responsable","value"=>"valider"]]
	);
} elseif	(in_array("um_direction", $user->roles) && $_GET['demandes']=='toutes'){
	$arg = array(
		"author__in"=>$allusers_ids,
		"post_type"=>"demande_badgeage"
	);
} 
 elseif	(in_array("um_ressources-humaines", $user->roles) && $_GET['demandes']=='rh'){
	$arg = array(
		//"author__in"=>$users_service_ids,
		"post_type"=>"demande_badgeage",
		"meta_query"=>[["key"=>"decision_responsable","value"=>"valider"],["key"=>"decision_direction","value"=>"valider"]]
	);
} 
 else {
	$arg = array(
		"author__in"=>$allusers_ids,
		"post_type"=>"demande_badgeage",
	);
}

$loop = new WP_Query($arg);

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>
<main id="content" class="site-main" role="main">

	<?php if ( apply_filters( 'hello_elementor_page_title', true ) ) : ?>
		<header class="page-header">
			<?php
			the_archive_title( '<h1 class="entry-title">', '</h1>' );
			the_archive_description( '<p class="archive-description">', '</p>' );
			?>
		</header>
	<?php endif; ?>
	<div class="page-content">
		<table>
			<tr>
				<th>nom</th>
				<th>date demande</th>
				<th>date concernée</th>
				<th>status responsable</th>
				<th>status direction</th>
				<th>status rh</rh>
			</tr>
			
			<?php
			while ( $loop->have_posts() ) {
				$loop->the_post();
				$id = get_the_id();
				$date = get_post_meta($id, "date");

				$post_link = get_permalink();
				echo "<tr><td><a href = '".$post_link."'>".get_the_author()."</a></td>
				<td>".get_the_time('d / m / Y' )."</td>".
				"<td>".date('d / m / Y', strtotime($date[0]))."</td>".
				"<td>".get_field('decision_responsable')."</td>".
				"<td>".get_field('decision_direction')."</td>".
				"<td>".get_field('traitement_rh')."</td>".
				"</tr>" ;

			//var_dump($post_link);
				?>
				
				
					<?php
					$status = get_field('decision_responsable');
					//var_dump($status);

					//var_dump($status);
				//	if ($status == NULL)	{
				//		printf( '<h4 class="%s"><a href="%s">%s</a></h4>', 'entry-title', esc_url( $post_link ), esc_html( get_the_author()." - en attente" ) );
				//		printf( '<a href="%s">%s</a>', esc_url( $post_link ), get_the_post_thumbnail( $post, 'large' ) );
				//		//the_field('decision_responsable');
				//	} elseif ($status == "valider")	{
				//		printf( '<h4 class="%s"><a href="%s">%s</a></h4>', 'entry-title', esc_url( $post_link ), esc_html( get_the_author()." - validé" ) );
				//		printf( '<a href="%s">%s</a>', esc_url( $post_link ), get_the_post_thumbnail( $post, 'large' ) );
				//		//the_field('decision_responsable');
				//	} elseif ($status == "refuser")	{
				//		printf( '<h4 class="%s"><a href="%s">%s</a></h4>', 'entry-title', esc_url( $post_link ), esc_html( get_the_author()." - refusé" ) );
				//		printf( '<a href="%s">%s</a>', esc_url( $post_link ), get_the_post_thumbnail( $post, 'large' ) );
				//		//the_field('decision_responsable');
				//	}else	{
				//		printf( '<h4 class="%s"><a href="%s">%s</a></h4>', 'entry-title', esc_url( $post_link ), esc_html( get_the_author()." - en attente" ) );
				//		printf( '<a href="%s">%s</a>', esc_url( $post_link ), get_the_post_thumbnail( $post, 'large' ) );
				//		//the_field('decision_responsable');
				//	}
					the_excerpt();
					?>
			
		
			
		<?php }} ?>
		</table>
	</div>

	<?php wp_link_pages(); ?>

	<?php
	global $wp_query;
	if ( $wp_query->max_num_pages > 1 ) :
		?>
		<nav class="pagination" role="navigation">
			<?php /* Translators: HTML arrow */ ?>
			<div class="nav-previous"><?php next_posts_link( sprintf( __( '%s older', 'hello-elementor' ), '<span class="meta-nav">&larr;</span>' ) ); ?></div>
			<?php /* Translators: HTML arrow */ ?>
			<div class="nav-next"><?php previous_posts_link( sprintf( __( 'newer %s', 'hello-elementor' ), '<span class="meta-nav">&rarr;</span>' ) ); ?></div>
		</nav>
	<?php endif; ?>
</main>
