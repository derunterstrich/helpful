<?php
namespace Helpful\Core;
new Feedback;

class Feedback
{
  public function __construct()
  {
    // register post type
    add_action( 'init', [$this, 'register_post_type'] );

    // table fix
    add_action( 'admin_head', [$this, 'table_fix'] );

    if( get_option('helpful_feedback_messages_table') ) {
      $this->register_columns();
      $this->register_columns_content();
    }
  }

  /**
   * Set cpt labels
   * @return array
   */
  public function post_type_labels()
  {
    $labels = [
      'name' => _x( 'Feedback', 'post type general name', 'helpful' ),
      'singular_name' => _x( 'Feedback', 'post type singular name', 'helpful' ),
      'menu_name' => _x( 'Feedback', 'admin menu', 'helpful' ),
      'name_admin_bar' => _x( 'Feedback', 'add new on admin bar', 'helpful' ),
      'add_new' => _x( 'New Feedback', 'book', 'helpful' ),
      'add_new_item' => __( 'Add New Feedback', 'helpful' ),
      'new_item' => __( 'New Feedback', 'helpful' ),
      'edit_item' => __( 'Edit Feedback', 'helpful' ),
      'view_item' => __( 'View Feedback', 'helpful' ),
      'all_items' => __( 'Feedback', 'helpful' ),
      'search_items' => __( 'Search Feedback', 'helpful' ),
      'parent_item_colon' => __( 'Parent Feedback:', 'helpful' ),
      'not_found' => __( 'No Feedback found.', 'helpful' ),
      'not_found_in_trash' => __( 'No Feedback found in Trash.', 'helpful' )
    ];

    $labels = apply_filters('helpful_feedback_labels', $labels);

    return $labels;
  }

  /**
   * Set cpt args
   * @return array
   */
  public function post_type_args()
  {
    $labels = $this->post_type_labels();

    $args = [
      'labels' => $labels,
      'description' => __( 'Description.', 'helpful' ),
      'public' => true,
      'publicly_queryable' => false,
      'exclude_from_search' => true,
      'show_ui' => true,
      'show_in_menu' => 'helpful',
      'show_in_admin_bar' => false,
      'show_in_rest' => false,
      'query_var' => false,
      'rewrite' => false,
      'capability_type' => 'post',
      'capabilities' => [
        'create_posts' => 'do_not_allow',
      ],
      'map_meta_cap' => true,
      'has_archive' => false,
      'hierarchical' => false,
      'menu_position' => null,
      'can_export' => true,
      'supports' => [ 'title', 'editor', ],
    ];

    $args = apply_filters('helpful_feedback_args', $args);

    return $args;
  }

  /**
   * Register post type
   */
  public function register_post_type()
  {
    $args = $this->post_type_args();
    register_post_type( 'helpful_feedback', $args );
  }

  /**
   * Register admin columns
   */
  public function register_columns()
  {
    add_filter( 'manage_edit-helpful_feedback_columns', [$this, 'columns'], 10 );
  }

  /**
   * Set admin column titles
   * @param array $default default wordpress titles
   * @return array
   */
  public function columns( $defaults )
  {
    $columns = [];

    foreach ($defaults as $key => $value) {
      $columns[$key] = $value;

      if( 'title' == $key  ) {

        $columns['helpful-feedback-message'] = _x( 'Feedback', 'column name', 'helpful' );

        if( get_option('helpful_feedback_table_post') )
          $columns['helpful-feedback-post'] = _x( 'Post', 'column name', 'helpful');

        if( get_option('helpful_feedback_table_type') )
          $columns['helpful-feedback-type'] = _x( 'Type', 'column name', 'helpful');

        if( get_option('helpful_feedback_table_browser') )
          $columns['helpful-feedback-browser'] = _x( 'Browser', 'column name', 'helpful');

        if( get_option('helpful_feedback_table_platform') )
          $columns['helpful-feedback-platform'] = _x( 'Platform', 'column name', 'helpful');

        if( get_option('helpful_feedback_table_language') )
          $columns['helpful-feedback-language'] = _x( 'Language', 'column name', 'helpful');
      }
    }

    unset($columns['title']);

    return $columns;
  }

  /**
   * Register columns content
   */
  public function register_columns_content()
  {
    add_action( 'manage_helpful_feedback_posts_custom_column', [$this, 'columns_content'], 10, 2 );
  }

  /**
   * Columns content callback
   * @param string $column_name
   * @param integer $post_id
   */
  public function columns_content( $column_name, $post_id )
  {
    if( 'helpful-feedback-message' == $column_name ) {
      printf('<span class="helpful-feedback-message">%s</span>', get_post_field('post_content', $post_id, 'display'));
    }

    if( 'helpful-feedback-post' == $column_name && get_option('helpful_feedback_table_post') ) {
      $parent_post = get_post_meta($post_id, 'post_id', true);
      $parent_title = get_the_title($parent_post);

      if( get_option('helpful_feedback_table_post_shorten') ) {
        $parent_title = wp_trim_words(get_the_title($parent_post), 5);
      }

      $permalink = esc_url(get_the_permalink($parent_post));
      
      printf('<span class="helpful-feedback-post"><a href="%s" target="_blank">%s</a></span>', $permalink, $parent_title);
    }

    if( 'helpful-feedback-type' == $column_name && get_option('helpful_feedback_table_type') ) {
      printf('<span class="helpful-feedback-type">%s</span>', get_post_meta($post_id, 'type', true));
    }

    if( 'helpful-feedback-browser' == $column_name && get_option('helpful_feedback_table_browser') ) {
      printf('<span class="helpful-feedback-browser">%s</span>', get_post_meta($post_id, 'browser', true));
    }

    if( 'helpful-feedback-platform' == $column_name && get_option('helpful_feedback_table_platform') ) {
      printf('<span class="helpful-feedback-platform">%s</span>', get_post_meta($post_id, 'platform', true));
    }

    if( 'helpful-feedback-language' == $column_name && get_option('helpful_feedback_table_language') ) {
      printf('<span class="helpful-feedback-language">%s</span>', get_post_meta($post_id, 'language', true));
    }
  }

  /**
   * Fix for table width on larger screens
   */
  public function table_fix()
  {
    $screen = get_current_screen();

    if( 'edit-helpful_feedback' == $screen->id ) {
      $style = '<style>@media all and (min-width: 1020px) { #helpful-feedback-message { width: 600px; min-width: 600px; } }</style>';
      print $style;
    }
  }
}
