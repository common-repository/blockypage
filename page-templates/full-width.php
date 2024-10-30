<?php get_header(); // Template Name: blpge full width ?>
<div class="blpge_container blpge_container--fluid-vw">
<?php if (have_posts()):
  while (have_posts()) : the_post();
    the_content();
  endwhile;
else:
  echo '<p>Sorry, no posts matched your criteria.</p>';
endif; ?>
</div>
<?php get_footer(); ?>
