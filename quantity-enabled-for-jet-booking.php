<?php
/**
 *  Plugin Name: Quantity Enabled for Jet Booking
 *  Plugin URI:           https://fullstak.nl/
 *  Description:         This plugin enables the quantity field for Jet Booking products without altering the JetBooking plugin.
 *  Author:                Bram Hammer
 *  Version:               1.2.0
 *  Author URI:        https://fullstak.nl//
 *  Elementor tested up to: 3.14
 *
 * @link             https://fullstak.nl/
 * @package     Quantity_Enabled_Jet_Booking
 * @version       1.2.0
 * @since          1.0.0
 *
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
if( ! defined( 'JET_ABAF_DEBUG' ) )
    define('JET_ABAF_DEBUG', false);

if ( ! class_exists( 'Quantity_Enabled_Jet_Booking' ) ) {

    /**
     * Sets up and initializes the plugin.
     */
    #[AllowDynamicProperties]
    class Quantity_Enabled_Jet_Booking
    {

        /**
         * A reference to an instance of this class.
         *
         * @since 1.0.0
         * @access private
         * @var    object
         */
        private static $instance = null;

        /**
         * Plugin version
         *
         * @since 1.8.0
         * @var string
         */
        private $version = '1.2.0';

        /**
         * Require Elementor Version
         *
         * @since 1.8.0
         * @var string Elementor version required to run the plugin.
         */
        private static $require_elementor_version = '3.0.0';

        /**
         * Debug mode
         *
         * @since 1.8.0
         * @var bool
         */
        private $debug = false;

        /**
         * Initialize the plugin.
         *
         * @since 1.0.0
         */
        private function __construct()
        {
            // check if WooCommerce is active and elementor is active
            $this->check_dependencies();

            if(defined('JET_ABAF_DEBUG')) {
                $this->version = time();
                $this->debug = true;
            }

            add_action( 'wp_head', [$this,'get_blocked_days'] );
            add_action( 'wp_enqueue_scripts', [$this, 'load_scripts'] );

            add_filter( 'woocommerce_get_stock_html', '__return_null', 99, 2 );
            add_filter( 'woocommerce_product_add_to_cart_text', [$this, 'woocommerce_add_to_cart_text'], 10, 2 );
            add_filter( 'woocommerce_product_single_add_to_cart_text', [$this, 'woocommerce_single_add_to_cart_text'], 10, 2 );
            add_filter( 'woocommerce_widget_cart_item_visible', [$this,'filter_cart_contents'], 10, 3 );
            add_filter( 'woocommerce_checkout_cart_item_visible', [$this,'filter_cart_contents'], 10, 3 );
            add_filter( 'woocommerce_cart_item_visible', [$this,'filter_cart_contents'], 10, 3 );
            add_filter( 'woocommerce_cart_item_quantity', [$this,'get_cart_quantity'], 10, 3);
            add_filter( 'woocommerce_widget_cart_item_quantity', [$this,'get_widget_cart_quantity'], 99, 3 );
            add_filter( 'woocommerce_cart_item_subtotal', [$this,'customize_cart_item_subtotal'], 10, 3 );
            add_filter( 'woocommerce_is_sold_individually', [$this, 'woocommerce_is_sold_individually'], 10, 1 );

            // register ajax to add to cart
            add_action( 'wp_ajax_jet_booking_add_cart_single_product', [$this, 'add_cart_product_ajax'], 10 );
            add_action( 'wp_ajax_nopriv_jet_booking_add_cart_single_product', [$this, 'add_cart_product_ajax'], 10 );

            add_filter( 'woocommerce_product_data_tabs', [$this, 'add_product_data_tab'], 10, 1 );
            add_action( 'woocommerce_product_data_panels', [$this, 'quantity_panel'], 10, 0 );
            add_action( 'woocommerce_admin_process_product_object', [$this, 'save_product_data'], 10, 1 );
            add_action( 'wp_ajax_jet_abaf_qefjb_save_settings', [$this, 'save_settings'] );
            add_action( 'admin_menu', [$this,'add_admin'], 99 );
            add_action( 'admin_enqueue_scripts', [$this,'admin_enqueue'] );

        }

        public function save_product_data($product)
        {
            if(isset($_POST['_qefjb_cooldown']))
                $product->update_meta_data('_qefjb_cooldown', sanitize_text_field($_POST['_qefjb_cooldown']));
            else
                $product->delete_meta_data('_qefjb_cooldown');
            if(isset($_POST['_qefjb_warmup']))
                $product->update_meta_data('_qefjb_warmup', sanitize_text_field($_POST['_qefjb_warmup']));
            else
                $product->delete_meta_data('_qefjb_warmup');
            $product->save();
        }

        public function add_product_data_tab($tabs)
        {
            $tabs['qefjb'] = [
                'label' => 'Quantity enabled for Jet Booking',
                'target' => 'qefjb_product_data',
                'class' => ['show_if_jet_booking'],
            ];
            return $tabs;
        }

        public function quantity_panel()
        {
            echo '<div id="qefjb_product_data" class="panel woocommerce_options_panel show_if_jet_booking">';
            woocommerce_wp_text_input( [
                'id'            => '_qefjb_cooldown',
                'label'         => __( 'Cooldown period', 'quantity-enabled-for-jet-booking' ),
                'description'   => __( 'How many days do you block any new bookings before the actual booking?', 'quantity-enabled-for-jet-booking' ),
                'desc_tip'      => 'true',
                'type'          => 'number'
            ]);
            woocommerce_wp_text_input( [
                'id'            => '_qefjb_warmup',
                'label'         => __( 'Warmup period', 'quantity-enabled-for-jet-booking' ),
                'description'   => __( 'How many days do you block any new bookings after the actual booking?', 'quantity-enabled-for-jet-booking' ),
                'desc_tip'      => 'true',
                'type'          => 'number'
            ]);
            echo '</div>';
        }

        public function save_settings()
        {
            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }
            if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'jet_abaf_qefjb_save_settings') {
                return;
            }
            $options = get_option('jet_abaf_qefjb_settings', $this->default_values());
            parse_str($_POST['settings'], $new_options);
            $options = array_merge($options, $new_options['data']);
            update_option('jet_abaf_qefjb_settings', $options);
            header('Content-Type: application/json');
            echo json_encode(['status'=>'success']);
            exit;
        }

        public function woocommerce_add_to_cart_text($text, $product)
        {
            $options = get_option('jet_abaf_qefjb_settings', $this->default_values());
            return $options['add_to_cart_text']??__('View details', 'woocommerce');
        }

        public function woocommerce_single_add_to_cart_text($text, $product)
        {
            $options = get_option('jet_abaf_qefjb_settings', $this->default_values());
            return $options['single_add_to_cart_text']??__('Add to cart', 'woocommerce');
        }

        /**
         * Set if the product is sold individually
         *
         * @param bool $value
         * @return bool
         */
        public function woocommerce_is_sold_individually($value) {
            return false;
        }

        /**
         * Enqueue admin scripts
         *
         * @return void
         */
        public function admin_enqueue($hook) {
            if($hook === 'bookings_page_jet-abaf-qefjb') {
                $jet_booking_plugin_main_file = WP_PLUGIN_DIR . '/jet-booking/jet-booking.php'; // Replace with the path to the main file of the Jet Booking plugin
                $jet_booking_plugin_url = plugin_dir_url($jet_booking_plugin_main_file);
                wp_enqueue_style('quantity-jet-booking', $jet_booking_plugin_url . 'assets/css/admin/jet-abaf-admin-style.css', [], $this->version);
                wp_enqueue_style('quantity-jet-booking-dash', $jet_booking_plugin_url . 'assets/css/admin/dashboard.css', [], $this->version);
                wp_enqueue_style('quantity-jet-booking-settings', plugins_url('/assets/css/admin/settings.css', __FILE__ ), [], $this->version);
                wp_enqueue_script('quantity-jet-booking-settings', plugins_url('/assets/js/admin/settings'.($this->debug?'':'.min').'.js', __FILE__ ), [], $this->version, ['strategy'=>'defer','in_footer'=>true]);
            }
        }

        /**
         * Add admin page
         *
         * @return void
         */
        public function add_admin() {
            add_submenu_page(
                'jet-abaf-bookings',
                'Quantity Settings',
                'Quantity Settings',
                'manage_options',
                'jet-abaf-qefjb',
                [$this, 'settings_page'],
                10
            );
        }

        /**
         * Settings page
         *
         * @return void
         */
        public function settings_page() {
            $settings = get_option('jet_abaf_qefjb_settings', $this->default_values());
            ob_start();
            include 'pages/settings.php';
            echo ob_get_clean();
        }

        /**
         * Check if WooCommerce and Elementor are active
         *
         * @return void
         */
        public function check_dependencies() {
            if ( ! defined( 'ELEMENTOR_VERSION' ) || ! defined( 'WOOCOMMERCE_VERSION' ) ) {
                add_action( 'admin_notices', [$this, 'admin_notice_missing_dependencies'] );
                return;
            }
            if ( version_compare( ELEMENTOR_VERSION, self::$require_elementor_version, '<' ) ) {
                add_action( 'admin_notices', [$this, 'admin_notice_outdated_elementor'] );
                return;
            }
        }

        /**
         * Admin notice for missing dependencies
         *
         * @return void
         */
        public function admin_notice_missing_dependencies() {
            if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Quantity Enabled for Jet Booking requires Elementor to be installed and activated.', 'quantity-enabled-for-jet-booking' ) . '</p></div>';
            }
            if ( ! defined( 'WOOCOMMERCE_VERSION' ) ) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Quantity Enabled for Jet Booking requires WooCommerce to be installed and activated.', 'quantity-enabled-for-jet-booking' ) . '</p></div>';
            }
        }

        /**
         * Admin notice for outdated Elementor
         *
         * @return void
         */
        public function admin_notice_outdated_elementor() {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html__( 'Quantity Enabled for Jet Booking requires Elementor version 3.0.0 or higher.', 'quantity-enabled-for-jet-booking' ) . '</p></div>';
        }

        public static function default_values() {
            return [
                'hide_check_in_out' => 'on',
                'show_stock' => 'off',
                'show_totalstock' => '%s total stock',
                'out_of_stock' => 'Currently not in stock, please contact us for options',
                'no_units_available_date' => 'No units available for this date',
                'add_to_cart_text' => 'View details',
                'single_add_to_cart_text' => 'Add to cart',
                'cooldown' => 0,
                'warmup' => 0
            ];
        }

        /**
         * Return an instance of this class.
         *
         * @return object A single instance of this class.
         * @since 1.0.0
         */
        public static function get_instance()
        {
            if (is_null(self::$instance)) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         * Load plugin scripts
         *
         * @return void
         */
        public function load_scripts() {
            wp_register_script('quantity-jet-booking', plugins_url('/assets/js/quantity-fix'.($this->debug?'':'.min').'.js',__FILE__ ), ['jquery-date-range-picker'], $this->version, ['strategy'=>'defer','in_footer'=>true]);
            wp_register_style('quantity-jet-booking', plugins_url('/assets/css/stock-units.css',__FILE__ ), [], $this->version);
            if(is_product()) {
                wp_add_inline_style('quantity-jet-booking', $this->generate_css());
                wp_enqueue_script('quantity-jet-booking');
                //wp_register_script('quantity-jet-booking-init', false);
                //wp_enqueue_script('quantity-jet-booking-init');
                //wp_add_inline_script('quantity-jet-booking', $this->get_blocked_days(),'before');
                wp_enqueue_style('quantity-jet-booking');
            }
        }

        /**
         * Generate CSS
         *
         * @return string
         */
        public function generate_css() {
            $options = get_option('jet_abaf_qefjb_settings', $this->default_values());
            ob_start();
            if(isset($options['hide_check_in_out']) && $options['hide_check_in_out'] === 'on'):
            ?>
                .jet-booking-form > .jet-abaf-product-check-in-out{
                    display: none;
                }
            <?php
            endif;
            if(isset($options['show_stock']) && $options['show_stock'] === 'on'):
            ?>
                .stock-info {
                    font-family: var(--e-global-typography-primary-font-family), sans-serif;
                    font-weight: var(--e-global-typography-primary-font-weight);
                    text-transform: var(--e-global-typography-primary-text-transform);
                    font-style: var(--e-global-typography-primary-font-style);
                    background: var(--e-global-color-accent);
                    padding: 6px 15px;
                    color: white;
                    border-radius: 2px;
                    margin-left: auto;
                    max-width: 259px;
                }
                .out-of-stock {
                    background: white;
                    color: var(--e-global-color-accent);
                    font-size: 0.8rem;
                    border: 1px solid var(--e-global-color-accent);
                }
            <?php
            endif;
            return ob_get_clean();
        }

        /**
         * add to cart ajax
         *
         * @return void
         */
        public function add_cart_product_ajax() {
            wc_nocache_headers();
            $product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( wp_unslash( $_REQUEST['product'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            //$was_added_to_cart = false;
            $adding_to_cart    = wc_get_product( $product_id );

            if ( ! $adding_to_cart ) {
                return;
            }
            $add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );
            if ( 'variable' === $add_to_cart_handler || 'variation' === $add_to_cart_handler ) {
                $variation_id = empty($_REQUEST['variation_id']) ? '' : absint(wp_unslash($_REQUEST['variation_id']));  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $quantity = empty($_REQUEST['quantity']) ? 1 : wc_stock_amount(wp_unslash($_REQUEST['quantity']));  // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $variations = [];

                foreach ($_REQUEST as $key => $value) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                    if ('attribute_' !== substr($key, 0, 10)) {
                        continue;
                    }

                    $variations[sanitize_title(wp_unslash($key))] = wp_unslash($value);
                }

                for($i=0;$i<$quantity;$i++)
                    WC()->cart->add_to_cart($product_id, 1, $variation_id, $variations);
            }else{
                $quantity = empty($_REQUEST['quantity']) ? 1 : wc_stock_amount(wp_unslash($_REQUEST['quantity']));  // phpcs:ignore WordPress.Security.NonceVerification.Recommended

                for($i=0;$i<$quantity;$i++) {
                    WC()->cart->add_to_cart($product_id, 1);
                }
            }
            if (is_callable(['WC_AJAX', 'get_refreshed_fragments'])) {
                WC_AJAX::get_refreshed_fragments();
            }
            die();
        }

        /**
         * Customize cart item subtotal
         *
         * @param string $product_subtotal
         * @param array $cart_item
         * @param string $cart_item_key
         * @return string
         */
        public function customize_cart_item_subtotal( $product_subtotal, $cart_item, $cart_item_key ) {
            $product_id = $cart_item['product_id'];
            $qty = 0;
            $cart = WC()->cart;
            $cart_items = $cart->get_cart();
            $hit = 0;
            foreach ( $cart_items as $item_key => $item ) {
                if ( $item['product_id'] === $product_id ){
                    $hit++;
                    $qty += $item['quantity'];
                }
            }
            if($hit === 1)
                return $product_subtotal;
            $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

            return WC()->cart->get_product_subtotal( $_product, $qty );
        }

        /**
         * Get widget cart quantity
         *
         * @param string $product_quantity
         * @param array $cart_item
         * @param string $cart_item_key
         * @return string
         */
        public function get_widget_cart_quantity($product_quantity, $cart_item, $cart_item_key) {
            $product_id = $cart_item['product_id'];
            $qty = 0;
            $cart = WC()->cart;
            $cart_items = $cart->get_cart();
            $hit = 0;
            foreach ( $cart_items as $item_key => $item ) {
                if ( $item['product_id'] === $product_id){
                    $hit++;
                    $qty += $item['quantity'];
                }
            }
            if($hit === 1)
                return $product_quantity;
            $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
            return '<span class="quantity">' . sprintf( '<span class="product-quantity">%s &times;</span> %s', $qty, $product_price ) . '</span>';
        }

        /**
         * Get cart quantity
         *
         * @param string $product_quantity
         * @param string $cart_item_key
         * @param array $cart_item
         * @return string
         */
        public function get_cart_quantity($product_quantity, $cart_item_key, $cart_item) {
            $product_id = $cart_item['product_id'];
            $qty = 0;
            $cart = WC()->cart;
            $cart_items = $cart->get_cart();
            $hit = 0;
            foreach ( $cart_items as $item_key => $item ) {
                if ( $item['product_id'] === $product_id){
                    $hit++;
                    $qty += $item['quantity'];
                }
            }
            if($hit === 1)
                return $product_quantity;
            $_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            return woocommerce_quantity_input(
                array(
                    'input_name'   => "cart[{$cart_item_key}][qty]",
                    'input_value'  => $qty,
                    'max_value'    => $_product->get_max_purchase_quantity(),
                    'min_value'    => '0',
                    'product_name' => $_product->get_name(),
                ),
                $_product,
                false
            );
        }

        /**
         * Filter cart contents
         *
         * @param bool $visible
         * @param array $cart_item
         * @param string $cart_item_key
         * @return bool
         */
        public function filter_cart_contents( $visible, $cart_item, $cart_item_key ) {
            $product_id = $cart_item['product_id'];
            $seen = [];
            $cart = WC()->cart;
            $cart_items = $cart->get_cart();
            foreach ( $cart_items as $item_key => $item ) {
                if ( $item['product_id'] === $product_id && !isset($seen[$product_id])){
                    $seen[$product_id] = $item_key;
                    break;
                }
            }
            return $seen[$product_id] === $cart_item_key;
        }

        /**
         * Get blocked days and add to the product page
         *
         * @return void
         */
        public function get_blocked_days(){
            if(!is_product())
                return;
            $pid = get_the_ID();
            $product = wc_get_product($pid);
            $all_units = jet_abaf()->db->get_apartment_units( $pid );
            $days = [];
            $options = get_option('jet_abaf_qefjb_settings', $this->default_values());
            if(!empty($all_units)){
                $max = count($all_units);
                $test_range = [
                    'apartment_id'=>$pid,
                    'check_in_date' => time(),
                    'check_out_date'=>strtotime("+3 years")
                ];
                $booked_units = jet_abaf()->db->get_booked_units( $test_range );
                if ( !empty( $booked_units ) ){
                    $skip_statuses   = jet_abaf()->statuses->invalid_statuses();
                    $skip_statuses[] = jet_abaf()->statuses->temporary_status();
                    foreach ( $booked_units as $booked_unit ) {
                        if ( !isset( $booked_unit['status'] ) || !in_array( $booked_unit['status'], $skip_statuses ) ) {
                            $current_date = strtotime(date('d-m-Y 00:00:00', $booked_unit['check_in_date'])); //fix time issues
                            $check_out_date =strtotime(date('d-m-Y 20:00:00', $booked_unit['check_out_date'])); //fix time issues
                            $product_cooldown = $product->get_meta('_qefjb_cooldown', true);
                            if(!empty($options['cooldown']) || $product_cooldown!=="") {
                                $cooldown = $product_cooldown!=="" ? $product_cooldown : $options['cooldown'];
                                for($i = 1; $i <= $cooldown; $i++) {
                                    $date_before = date('d-m-Y', strtotime('-'.$i.' day', $current_date));
                                    if (isset($days[$date_before]))
                                        $days[$date_before]++;
                                    else
                                        $days[$date_before] = 1;
                                }
                            }
                            $product_warmup = $product->get_meta('_qefjb_warmup', true);
                            if(!empty($options['warmup']) || $product_warmup!=="") {
                                $warmup = $product_warmup!=="" ? $product_warmup : $options['warmup'];
                                for($i = 1; $i <= $warmup; $i++) {
                                    $date_after = date('d-m-Y', strtotime('+'.$i.' day', $check_out_date));
                                    if (isset($days[$date_after]))
                                        $days[$date_after]++;
                                    else
                                        $days[$date_after] = 1;
                                }
                            }
                            while ($current_date <= $check_out_date) {
                                $date = date('d-m-Y', $current_date);
                                if (isset($days[$date])) {
                                    $days[$date]++;
                                } else {
                                    $days[$date] = 1;
                                }
                                $current_date = strtotime('+1 day', $current_date);
                            }
                        }
                    }
                }
            }else{
                $max = 9999;
            }
            ob_start();?>
                <script>
                const JetBookingAgendaBlocks = <?php echo json_encode(['max'=>$max,'blocks'=>$days]); ?>;
                const createStockElements = (totalStock) => {
                    const stockElement = document.createElement('div');
                    stockElement.classList.add('stock-info');
                    if (totalStock > 0) {
                        stockElement.textContent = `<?=sprintf($options['show_totalstock']??'%s total stock','${totalStock}')?>`;
                        stockElement.classList.add('in-stock');
                    } else {
                        stockElement.textContent = '<?=$options['out_of_stock']??'Currently not in stock, please contact us for options'?>';
                        stockElement.classList.add('out-of-stock');
                    }
                    document.querySelector('.elementor-widget-woocommerce-product-add-to-cart .elementor-product-jet_booking > form')?.after(stockElement);
                    stockElement.after(document.querySelector('.jet-abaf-product-total'));
                }
                jQuery(document).on('jet-booking/init',()=>{
                    <?php if(isset($options['show_stock']) && $options['show_stock'] === 'on'):?>
                    createStockElements(JetBookingAgendaBlocks.max);
                    <?php endif;?>
                    const override_config = (config) => {
                        return {
                            ...config,
                            beforeShowDay: (t) => {
                                const qty = jQuery(document).find('.jet-booking-form').find('.quantity input.qty').val()
                                const key = `${t.getDate().toString().padStart(2, '0')}-${(t.getMonth()+1).toString().padStart(2, '0')}-${t.getFullYear()}`
                                let valid = true
                                if(typeof JetBookingAgendaBlocks.blocks[key] !== "undefined"){
                                    const free = JetBookingAgendaBlocks.max - JetBookingAgendaBlocks.blocks[key]
                                    valid = free>=qty
                                }
                                const _class = 'no_units_available'
                                const _tooltip = valid ? '' : '<?=$options['no_units_available_date']??'No units available for this date'?>'
                                return [valid,_class,_tooltip]
                            }
                        }
                    }
                    window.JetPlugins.hooks.addFilter('jet-booking.calendar.config','jetBooking',override_config)
                    window.JetPlugins.hooks.addFilter('jet-booking.input.config','jetBooking',override_config)
                });
                </script>
            <?php
            echo ob_get_clean();
        }

    }
}

Quantity_Enabled_Jet_Booking::get_instance();
