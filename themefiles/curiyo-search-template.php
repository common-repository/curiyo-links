<?php
/*
Template Name: Curiyo Search
*/

// the query
$keyword = $_GET["keyword"];

$tag = get_term_by('name', $keyword, 'post_tag');
$category = get_term_by('name', $keyword, 'category');

$have_results = false;

if ($tag) {
	$query_args = array( tag_id => $tag->term_id, posts_per_page => CURIYO_POSTS_PER_TAG ) ;
	$the_query = new WP_Query($query_args); 
	$have_results = $the_query->have_posts();
}

if ( (!$have_results) && $category) {
	wp_reset_postdata();
	$query_args = array( cat => $category->term_id, posts_per_page => CURIYO_POSTS_PER_TAG) ;
	$the_query = new WP_Query($query_args); 
	$have_results = $the_query->have_posts();
}

if ( !$have_results ) {
	wp_reset_postdata();	
	$query_args = array( s => $keyword, posts_per_page => CURIYO_POSTS_PER_TAG ) ;	
	$the_query = new WP_Query($query_args); 
}

?>

<?php if ( $the_query->have_posts() ) : ?>

<style type="text/css">
body{
font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
font-size:13px;
}

.curiyo_article{
border-bottom: 1px solid #ccc;
position: relative;
padding: 3px;
}

.curiyo_image > a > img {
width: 120px !important;
height: auto !important;
float:right;
} 

.curiyo_meta{
font-size: 70%;
font-style: italic;
//line-height: 0px;
}

h2.curiyo_title{
font-size: 15px;
}

a:link {
text-decoration:none;
color:blue;
}
a:visited {
text-decoration:none;
color:red;
}
</style>

  <!-- pagination here -->
  


  <!-- the loop -->
  <?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
<div class="curiyo_article">
<div class="curiyo_image"><a class="curiyo_url" href="<?php the_permalink() ?>" target="_new"><?php the_post_thumbnail(); ?></a></div>
<h2 class="curiyo_title"><a class="curiyo_url" href="<?php the_permalink() ?>" target="_new"><?php the_title(); ?></a></h2>
<div class="curiyo_meta"><span class="curiyo_date"><?php the_time('F jS, Y') ?></span> <span class="curiyo_author">by <?php the_author() ?></span></div>
<div class="curiyo_excerpt">
<?php 
	$excerpt = get_the_excerpt();
	echo $excerpt;
?>
</div>
</div>
  <?php endwhile; ?>
  <!-- end of the loop -->

  <!-- pagination here -->

  <?php wp_reset_postdata(); ?>

<?php else:  ?>
  <p><?php _e( 'Sorry, no posts matched the search for ' . $keyword . '.' ); ?></p>
<?php endif; ?>