<?php

/**
Template Name: Homepage One
 */
get_header();

get_template_part('sections/cirnotob', 'slider');
get_template_part('sections/cirnotob', 'features');
get_template_part('sections/cirnotob', 'showcase');

get_template_part('sections/cirnotob', 'service');
get_template_part('sections/cirnotob', 'portfolio');
// get_template_part('sections/cirnotob', 'call-action');
get_template_part('sections/cirnotob', 'blog');

get_footer();
