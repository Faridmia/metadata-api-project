<?php
/*
Plugin Name: Related Post Widget
Plugin URI: http://example.com
Description: Display related posts in the sidebar of single post pages.
Version: 1.0
Author: faridmia
Author URI: http://example.com
License: GPL2
*/

class Related_Posts_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'related_posts_widget',
            'Related Posts Widget',
            array('description' => 'Display related posts in the sidebar of single post pages.')
        );
    }

    // Widget output
    public function widget($args, $instance)
    {

        echo $args['before_widget'];
        $title = isset($instance['title']) ? $instance['title'] : '';
        $title = apply_filters('widget_title',  $title);
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        global $post;

        $posts_per_page = !empty($instance['posts_per_page']) ? $instance['posts_per_page'] : 5;
        $ignore_sticky = !empty($instance['ignore_sticky']) ? 1 : 0;


        $categories = get_the_category($post->ID);
        if ($categories) {
            $category_ids = array();
            foreach ($categories as $category) $category_ids[] = $category->term_id;

            $related_args = array(
                'category__in' => $category_ids,
                'post__not_in' => array($post->ID),
                'posts_per_page' => $posts_per_page,
                'ignore_sticky_posts' => $ignore_sticky
            );
        }


        $tags = wp_get_post_tags($post->ID);

        if ($tags) {
            $tag_ids = array();
            foreach ($tags as $individual_tag) $tag_ids[] = $individual_tag->term_id;
            $related_args = array(
                'tag__in' => $tag_ids,
                'post__not_in' => array($post->ID),
                'posts_per_page' => $posts_per_page,
                'ignore_sticky_posts' => $ignore_sticky
            );
        }


        $related_posts = new WP_Query($related_args);

        if ($related_posts->have_posts()) {
            echo '<ul>';
            while ($related_posts->have_posts()) {
                $related_posts->the_post();
                echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
            }
            echo '</ul>';
        }

        wp_reset_postdata();

        echo $args['after_widget'];
    }

    // Widget form
    public function form($instance)
    {
        $title = !empty($instance['title']) ? $instance['title'] : 'Related Posts';
        $posts_per_page = !empty($instance['posts_per_page']) ? $instance['posts_per_page'] : 5;
        $ignore_sticky = isset($instance['ignore_sticky']) ? (bool) $instance['ignore_sticky'] : true;
?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>">
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('posts_per_page'); ?>">Posts Per Page:</label>
            <input class="widefat" id="<?php echo $this->get_field_id('posts_per_page'); ?>" name="<?php echo $this->get_field_name('posts_per_page'); ?>" type="text" value="<?php echo esc_attr($posts_per_page); ?>">
        </p>
        <p>
            <input id="<?php echo $this->get_field_id('ignore_sticky'); ?>" name="<?php echo $this->get_field_name('ignore_sticky'); ?>" type="checkbox" <?php checked($ignore_sticky); ?>>
            <label for="<?php echo $this->get_field_id('ignore_sticky'); ?>">Ignore Sticky Posts</label>
        </p>
<?php
    }

    // Update widget
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['posts_per_page'] = (!empty($new_instance['posts_per_page'])) ? intval($new_instance['posts_per_page']) : 5;
        $instance['ignore_sticky'] = isset($new_instance['ignore_sticky']) ? (bool) $new_instance['ignore_sticky'] : false;
        return $instance;
    }
}


// Register the widget
function register_related_posts_widget()
{
    register_widget('Related_Posts_Widget');
}

add_action('widgets_init', 'register_related_posts_widget');
?>