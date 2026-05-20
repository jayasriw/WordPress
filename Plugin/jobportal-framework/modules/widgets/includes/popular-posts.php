<?php
if (!class_exists('JobPortal_Widget_Popular_Posts')) {
    class JobPortal_Widget_Popular_Posts extends JobPortal_Widget
    {
        public function __construct()
        {
            $this->widget_cssclass = 'jobportal-widget-popular_posts';
            $this->widget_description = esc_html__("Popular posts widget", 'jobportal-framework');
            $this->widget_id = 'jobportal_popular_posts';
            $this->widget_name = esc_html__('JobPortal - Popular Posts', 'jobportal-framework');
            $this->settings = array(
                'title' => array(
                    'type' => 'text',
                    'std' => esc_html__('Popular Posts', 'jobportal-framework'),
                    'label' => esc_html__('Title', 'jobportal-framework')
                ),
                'number' => array(
                    'type' => 'number',
                    'std' => '6',
                    'label' => esc_html__('Number of posts to show', 'jobportal-framework')
                ),
                'sort_by' => array(
                    'type' => 'select',
                    'label' => esc_html__('Sort By', 'jobportal-framework'),
                    'std' => 'date',
                    'options' => array(
                        'date' => esc_html__('Date', 'jobportal-framework'),
                        'title' => esc_html__('Title', 'jobportal-framework'),
                        'rand' => esc_html__('Random', 'jobportal-framework'),
                    )
                ),
                'cate' => array(
                    'type' => 'checkbox',
                    'std' => 'true',
                    'label' => esc_html__('Show post categories', 'jobportal-framework')
                ),
                'date' => array(
                    'type' => 'checkbox',
                    'std' => 'true',
                    'label' => esc_html__('Show post date', 'jobportal-framework')
                ),
            );
            parent::__construct();
        }

        function widget($args, $instance)
        {
            if ($this->get_cached_widget($args))
                return;
            extract($args, EXTR_SKIP);
            $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
            $number = empty($instance['number']) ? '' : apply_filters('widget_number', $instance['number']);
            $sort_by = empty($instance['sort_by']) ? '' : apply_filters('widget_sort_by', $instance['sort_by']);
            $date = empty($instance['date']) ? '' : apply_filters('widget_cate', $instance['date']);
            $cate = empty($instance['cate']) ? '' : apply_filters('widget_cate', $instance['cate']);
            ob_start();
            echo wp_kses_post($args['before_widget']);
            ?>

            <?php
            $arr = array(
                'post_type' => 'post',
                'numberposts' => $number,
                // 'meta_key' => 'post_views_count',
                'orderby' => $sort_by,
                'order' => 'DESC'
            );
            $posts = get_posts($arr);

            ?>

            <?php if (!empty($title)) { ?>
            <h3 class="widget-title"><?php esc_html_e($title); ?></h3>
        <?php } ?>

            <div class="jobportal-popular-posts listing-posts">
                <?php
                foreach ($posts as $post) {
                    $postid = $post->ID;
                    $size = 'medium';
                    $categores = wp_get_post_categories($postid);
                    $size = '100x100';
                    $attach_id = get_post_thumbnail_id($postid);
                    $thumb_url = jobportal_image_resize($attach_id, $size);

                    $no_image_src = JOBPORTAL_PLUGIN_URL . 'assets/images/no-image.jpg';
                    if ($thumb_url) {
                        $cur_url = $thumb_url;
                    } else {
                        $cur_url = $no_image_src;
                    }
                    ?>

                    <article class="post">
                        <div class="inner-post-wrap">

                            <!-- post thumbnail -->
                            <?php if ($cur_url) : ?>
                                <div class="entry-post-thumbnail">
                                    <a href="<?php echo get_the_permalink($postid); ?>">
                                        <img src="<?php echo esc_url($cur_url); ?>"
                                             alt="<?php the_title_attribute($postid); ?>">
                                    </a>
                                </div>
                            <?php endif; ?>

                            <div class="entry-post-detail">

                                <!-- list categories -->
                                <?php if ($categores && $cate) : ?>
                                    <ul class="post-categories">
                                        <?php
                                        foreach ($categores as $category) {
                                            $cate = get_category($category);
                                            ?>
                                            <li>
                                                <a href="<?php echo get_category_link($cate); ?>"><?php esc_html_e($cate->name); ?></a>
                                            </li>
                                        <?php } ?>
                                    </ul>
                                <?php endif; ?>

                                <!-- post date -->
                                <?php if ($date) : ?>
                                    <p class="post-date"><?php echo get_the_date(); ?></p>
                                <?php endif; ?>

                                <!-- post title -->
                                <h3 class="post-title"><a href="<?php echo get_the_permalink($postid); ?>"
                                                          rel="bookmark"><?php echo get_the_title($postid); ?></a></h3>

                                <?php if (is_sticky($postid)) { ?>
                                    <span class="is-sticky"><?php esc_html_e('Featured', 'jobportal-framework'); ?></span>
                                <?php } ?>

                            </div>

                        </div>
                    </article><!-- #post-## -->
                <?php } ?>
            </div>

            <?php
            echo wp_kses_post($args['after_widget']);
            $content = ob_get_clean();
            echo $content;
            $this->cache_widget($args, $content);
        }
    }
}
