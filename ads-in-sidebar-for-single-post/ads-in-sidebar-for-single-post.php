<?php
/*
Plugin Name: Ads in sidebar for single post (ASFSP)
Plugin URI: http://www.gizlogic.com
Description: Add HTML Code advertising to Sidebar. For each single post you will display a different ads in Sidebar with theme integration as widget.
Version: 1.0
Author: Jose A. de la O
Author URI: http://www.gizlogic.com
*/
/**
 * AdsForPost Class
 */
class AdsForPost extends WP_Widget {
    /** constructor */
    function AdsForPost() {
        parent::WP_Widget(false, $name = 'Ads for post');
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        global $post;
        $title = apply_filters('widget_title', $instance['title']);
        if( get_post_meta($post->ID, 'ads_for_post', true) != ""){
          ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; ?>
                  <?php echo get_post_meta($post->ID, 'ads_for_post', $single = true); ?>
              <?php echo $after_widget; ?>
          <?php
        }
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
      $instance = $old_instance;
      $instance['title'] = strip_tags($new_instance['title']);
      return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {
        $title = esc_attr($instance['title']);
        ?>
            <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
        <?php
    }

} // AdsForPost Class

add_action('widgets_init', create_function('', 'return register_widget("AdsForPost");'));

// Post custom ads
$new_meta_boxes = array(
  "ads_for_post" => array(
  "name" => "ads_for_post",
  "std" => "",
  "title" => "Put your ads code",
  "description" => "Put your <b>html code</b> for your ads to display Ads for post Widget.")
);

function ads_meta_box() {
  global $post, $new_meta_boxes;

  foreach($new_meta_boxes as $meta_box) {
    $meta_box_value = get_post_meta($post->ID, $meta_box['name'], true);

    if($meta_box_value == "")
      $meta_box_value = $meta_box['std'];

    echo'<input type="hidden" name="'.$meta_box['name'].'_noncename" id="'.$meta_box['name'].'_noncename" value="'.wp_create_nonce( plugin_basename(__FILE__) ).'" />';
    echo'<label style="font-weight: bold; display: block; padding: 5px 0 2px 2px" for="'.$meta_box['name'].'">'.$meta_box['title'].'</label>';
    echo'<textarea cols="40" rows="5" name="'.$meta_box['name'].'">'.$meta_box_value.'</textarea><br />';
    echo'<p><label for="'.$meta_box['name'].'">'.$meta_box['description'].'</label></p>';
    echo'You should use HTML code.<br>';
  }
}

function create_ads_meta_box() {
  global $theme_name;
  if ( function_exists('add_meta_box') ) {
    add_meta_box( 'new-meta-boxes', 'Ads for post', 'ads_meta_box', 'post', 'normal', 'high' );
  }
}

function save_post_data( $post_id ) {
  global $post, $new_meta_boxes;

  foreach($new_meta_boxes as $meta_box) {
    if ( !wp_verify_nonce( $_POST[$meta_box['name'].'_noncename'], plugin_basename(__FILE__) )) {
      return $post_id;
    }

    if ( 'page' == $_POST['post_type'] ) {
      if ( !current_user_can( 'edit_page', $post_id ))
        return $post_id;
    } else {
      if ( !current_user_can( 'edit_post', $post_id ))
        return $post_id;
    }

    $data = $_POST[$meta_box['name']];

    if(get_post_meta($post_id, $meta_box['name']) == "")
      add_post_meta($post_id, $meta_box['name'], $data, true);
    elseif($data != get_post_meta($post_id, $meta_box['name'], true))
      update_post_meta($post_id, $meta_box['name'], $data);
    elseif($data == "")
      delete_post_meta($post_id, $meta_box['name'], get_post_meta($post_id, $meta_box['name'], true));
  }
}

add_action('admin_menu', 'create_ads_meta_box');
add_action('save_post', 'save_post_data');
