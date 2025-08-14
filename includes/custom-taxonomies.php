<?php

/**\
 * This file contains:
 *  - beforeafter_add_taxonomy_support(): Function to link 'beforeafter' custom post type to 'location' AND 'type' taxonomy.
 */


/**
 * Link 'beforeafter' custom post type to 'location' taxonomy.
 */
function beforeafter_add_taxonomy_support()
{
    register_taxonomy_for_object_type('location', 'beforeafter');
    register_taxonomy_for_object_type('type', 'beforeafter');
}
add_action('init', 'beforeafter_add_taxonomy_support');

