<?php

/**
 * Plugin Name: Filter products by thumbnail
 * Description: Adds a filter to the admin products list to filter products if they have a main image image
 * Version:     1.0.0
 * Author:      Ioakeim Diamantidis
 */

defined('ABSPATH') || exit;

if (! class_exists('WC_Admin_Products_Filter_By_Thumbnail')) {

    class WC_Admin_Products_Filter_By_Thumbnail
    {

        const THUMB_FILTER_NAME = 'product_thumbnail_filter';

        /**
         * Constructor to initialize the plugin
         */
        public function __construct()
        {
            if (! is_admin()) {
                return;
            }

            if (! function_exists('is_plugin_active')) {
                require_once ABSPATH . 'wp-admin/includes/plugin.php';
            }

            if (! is_plugin_active('woocommerce/woocommerce.php')) {
                return;
            }

            add_action('restrict_manage_posts', array($this, 'filter_by_the_thumbnail'));
            add_action('parse_query', array($this, 'filter_products_by_thumbnail'));
        }

        /**
         * Adds a filter dropdown to the admin products list to filter by thumbnail
         *
         * @param string $post_type The post type being queried
         */
        public function filter_by_the_thumbnail($post_type)
        {
            if ($post_type !== 'product') {
                return;
            }

?>
            <select name="<?php echo self::THUMB_FILTER_NAME ?>" onchange="this.form.submit()">
                <option value=""><?php esc_html_e('Thumbnail filter', 'wc-filter-products-by-thumbnail'); ?></option>
                <?php
                $filter_img_opt = array(
                    '0' => esc_html__('No Thumbnail', 'wc-filter-products-by-thumbnail'),
                    '1' => esc_html__('Thumbnail', 'wc-filter-products-by-thumbnail')
                );

                $selected_opt = isset($_GET[self::THUMB_FILTER_NAME]) ? $_GET[self::THUMB_FILTER_NAME] : '';

                foreach ($filter_img_opt as $o => $opt) {
                ?>
                    <option value="<?php echo $o ?>" <?php selected($selected_opt, $o) ?>><?php echo $opt ?></option>
                <?php
                }
                ?>
            </select>
<?php
        }

        /**
         * Filters the products based on the selected thumbnail filter
         *
         * @param WP_Query $query The current query object
         */
        public function filter_products_by_thumbnail($query)
        {
            global $typenow;

            if ($typenow !== 'product' || !isset($_GET[self::THUMB_FILTER_NAME]) || !is_admin()) {
                return;
            }

            $thumbnail_filter = $_GET[self::THUMB_FILTER_NAME];

            if ($thumbnail_filter === '0') {
                $meta_query = array(
                    array(
                        'key'     => '_thumbnail_id',
                        'compare' => 'NOT EXISTS',
                    ),
                );
                $query->set('meta_query', $meta_query);
            } elseif ($thumbnail_filter === '1') {
                $meta_query = array(
                    array(
                        'key'     => '_thumbnail_id',
                        'compare' => 'EXISTS',
                    ),
                );
                $query->set('meta_query', $meta_query);
            }
        }
    }

    new WC_Admin_Products_Filter_By_Thumbnail();
}
