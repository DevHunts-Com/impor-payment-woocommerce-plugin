<?php

/*
  Plugin Name: Impor Payment - Woocomerce Plugin Payment Gateway
  Plugin URI: http://impor.payment.co.id/api-docs
  Description: Best payment gateway 
  Version: 1.0
  Author: DevHunt Company Team
  Author URI: http://impor.payment.co.id/api-docs
  License: GPLv2 or later
  WC requires at least: 1.0
  WC tested up to: 1.0
 */

if (!defined('ABSPATH')) exit;
add_action('plugins_loaded', 'woocommerce_imporPayment_init', 0);
// // require_once('wp-includes/template-loader.php');
// // require('wp-blog-header.php');


function woocommerce_imporPayment_init()
{


    if (!class_exists('WC_Payment_Gateway'))
        return;


    class WC_Gateway_ImporPayment extends WC_Payment_Gateway
    {

        public function __construct()
        {

            //plugin id
            $this->id = 'imporpayment';
            //Payment Gateway title
            $this->method_title = 'Impor Payment Gateway';
            //true only in case of direct payment method, false in our case
            $this->has_fields = false;
            //payment gateway logo
            $this->icon = plugins_url('/impor-logo.png', __FILE__);

            //redirect URL
            $this->redirect_url = add_query_arg('wc-api', 'WC_Gateway_ImporPayment', home_url('/'));

            //thank you page URL
            $returnUrl = home_url('/checkout/order-received/');

            //Load settings
            $this->init_form_fields();
            $this->init_settings();
            $this->enabled      = $this->settings['enabled'] ?? '';
            $this->sandbox_mode      = $this->settings['sandbox_mode'] ?? 'no';
            $this->expired_time    = $this->settings['expired_time'] ?? '24';
            $this->return_url      = $this->settings['return_url'] ?? $returnUrl;
            $this->title        = "Impor Payment";
            $this->description  = $this->settings['description'] ?? '';
            $this->apikey       = $this->settings['apikey'] ?? '';

            // custom field
            // Add custom field to checkout page
            // add_action('woocommerce_review_order_before_payment', array(&$this, 'add_payment_method_field'));
            // add_action('woocommerce_checkout_process', array(&$this, 'validate_payment_method'));
            add_action('woocommerce_checkout_update_order_meta', array(&$this, 'save_payment_method'));

            // Actions
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
            add_action('woocommerce_receipt_imporpayment', array(&$this, 'receipt_page'));

            // Payment listener/API hook
            add_action('woocommerce_api_wc_gateway_imporpayment', array($this, 'check_imporpayment_response'));
        }

        function add_payment_method_field()
        {
            $payment_methods = [
                'alfamart'    => [
                    'name'    => 'Alfamart',
                    'code'    => 'ALFAMART',
                    'class'    => 'WC_Gateway_ImporPayment_ALFAMART',
                    'type'    => 'DIRECT',
                ],
                'alfamidi'        => [
                    'name'    => 'Alfamidi',
                    'code'    => 'ALFAMIDI',
                    'class'    => 'WC_Gateway_ImporPayment_ALFAMIDI',
                    'type'    => 'DIRECT',
                ],
                'indomaret'    => [
                    'name'    => 'Indomaret',
                    'code'    => 'INDOMARET',
                    'class'    => 'WC_Gateway_ImporPayment_INDOMARET',
                    'type'    => 'DIRECT',
                ],
                'bniva'        => [
                    'name'    => 'BNI Virtual Account',
                    'code'    => 'BNIVA',
                    'class'    => 'WC_Gateway_ImporPayment_BNI_VA',
                    'type'    => 'DIRECT',
                ],
                'briva'        => [
                    'name'    => 'BRI Virtual Account',
                    'code'    => 'BRIVA',
                    'class'    => 'WC_Gateway_ImporPayment_BRI_VA',
                    'type'    => 'DIRECT',
                ],
                'mandiriva'        => [
                    'name'    => 'Mandiri Virtual Account',
                    'code'    => 'MANDIRIVA',
                    'class'    => 'WC_Gateway_ImporPayment_MANDIRI_VA',
                    'type'    => 'DIRECT',
                ],
                'bcava'         => [
                    'name'  => 'BCA Virtual Account',
                    'code'  => 'BCAVA',
                    'class' => 'WC_Gateway_ImporPayment_BCA_VA',
                    'type'  => 'DIRECT',
                ],
                'maybankva'        => [
                    'name'    => 'Maybank Virtual Account',
                    'code'    => 'MYBVA',
                    'class'    => 'WC_Gateway_ImporPayment_MAYBANK_VA',
                    'type'    => 'DIRECT',
                ],
                'permatava'        => [
                    'name'    => 'Permata Virtual Account',
                    'code'    => 'PERMATAVA',
                    'class'    => 'WC_Gateway_ImporPayment_PERMATA_VA',
                    'type'    => 'DIRECT',
                ],
                'sampoernava'   => [
                    'name'  => 'Sahabat Sampoerna Virtual Account',
                    'code'  => 'SAMPOERNAVA',
                    'class' => 'WC_Gateway_ImporPayment_SAMPOERNA_VA',
                    'type'  => 'DIRECT',
                ],
                'muamalatva'        => [
                    'name'    => 'Muamalat Virtual Account',
                    'code'    => 'MUAMALATVA',
                    'class'    => 'WC_Gateway_ImporPayment_MUAMALAT_VA',
                    'type'    => 'DIRECT',
                ],
                'smsva'        => [
                    'name'    => 'Sinarmas Virtual Account',
                    'code'    => 'SMSVA',
                    'class'    => 'WC_Gateway_ImporPayment_SMS_VA',
                    'type'    => 'DIRECT',
                ],
                'cimbva'    => [
                    'name'    => 'CIMB Niaga Virtual Account',
                    'code'    => 'CIMBVA',
                    'class'    => 'WC_Gateway_ImporPayment_CIMB_VA',
                    'type'    => 'DIRECT',
                ],
                'bsiva'        => [
                    'name'    => 'BSI Virtual Account',
                    'code'    => 'BSIVA',
                    'class'    => 'WC_Gateway_ImporPayment_BSI_VA',
                    'type'    => 'DIRECT',
                ],
                'ocbcva'        => [
                    'name'    => 'OCBC NISP Virtual Account',
                    'code'    => 'OCBCVA',
                    'class'    => 'WC_Gateway_ImporPayment_OCBC_VA',
                    'type'    => 'DIRECT',
                ],
                'danamonva'        => [
                    'name'    => 'Danamon Virtual Account',
                    'code'    => 'DANAMONVA',
                    'class'    => 'WC_Gateway_ImporPayment_DANAMON_VA',
                    'type'    => 'DIRECT',
                ],
                'qris'        => [
                    'name'    => 'QRIS by ShopeePay',
                    'code'    => 'QRIS',
                    'class'    => 'WC_Gateway_ImporPayment_QRIS',
                    'type'    => 'DIRECT',
                ],
                'qrisc'        => [
                    'name'    => 'QRIS Customizable',
                    'code'    => 'QRISC',
                    'class'    => 'WC_Gateway_ImporPayment_QRISC',
                    'type'    => 'DIRECT',
                ],
                'qris2'     => [
                    'name'  => 'QRIS',
                    'code'  => 'QRIS2',
                    'class' => 'WC_Gateway_ImporPayment_QRIS2',
                    'type'  => 'DIRECT',
                ],
                'ovo'     => [
                    'name'  => 'OVO',
                    'code'  => 'OVO',
                    'class' => 'WC_Gateway_ImporPayment_OVO',
                    'type'  => 'REDIRECT',
                ],
                'dana'     => [
                    'name'  => 'DANA',
                    'code'  => 'DANA',
                    'class' => 'WC_Gateway_ImporPayment_DANA',
                    'type'  => 'REDIRECT',
                ],
                'shopeepay'     => [
                    'name'  => 'ShopeePay',
                    'code'  => 'SHOPEEPAY',
                    'class' => 'WC_Gateway_ImporPayment_SHOPEEPAY',
                    'type'  => 'REDIRECT',
                ],
                'cc'        => [
                    'name'    => 'Kartu Kredit',
                    'code'    => 'CC',
                    'class'    => 'WC_Gateway_ImporPayment_CC',
                    'type'    => 'REDIRECT',
                ],
            ];


?>
            <p class="form-row form-row-wide">
                <label for="imporpayment_payment_method"><?php _e('Payment Method', 'woocommerce'); ?> <span class="required">*</span></label>
                <select name="imporpayment_payment_method" id="imporpayment_payment_method" class="select2-selection select2-selection--single">
                    <option value=""><?php _e('--- Select Payment Method ---', 'woocommerce'); ?></option>
                    <?php foreach ($payment_methods as $key => $method) : ?>
                        <option value="<?php echo esc_attr($method['code']); ?>"><?php echo esc_html($method['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
<?php
        }


        // function validate_payment_method()
        // {
        //     if ($_POST['imporpayment_payment_method'] === "") {
        //         wc_add_notice(__('Please select a payment method.', 'woothemes'), 'error');
        //     }
        // }


        function save_payment_method($order_id)
        {
            if (!empty($_POST['imporpayment_payment_method'])) {
                update_post_meta($order_id, 'imporpayment_payment_method', sanitize_text_field($_POST['imporpayment_payment_method']));
            }
        }



        function init_form_fields()
        {

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woothemes'),
                    'label' => __('Enable Impor Payment', 'woothemes'),
                    'type' => 'checkbox',
                    'description' => '',
                    'default' => 'no'
                ),
                'title' => array(
                    'title' => __('Title', 'woothemes'),
                    'type' => 'text',
                    'description' => __('', 'woothemes'),
                    'default' => __('Pembayaran Impor Payment', 'woothemes')
                ),
                'description' => array(
                    'title' => __('Description', 'woothemes'),
                    'type' => 'textarea',
                    'description' => __('', 'woothemes'),
                    'default' => 'Sistem pembayaran menggunakan Impor Payment.'
                ),
                'sandbox_mode' => array(
                    'title' => __('Mode Sandbox/Development', 'woothemes'),
                    'label' => __('Aktifkan Mode Sandbox/Development', 'woothemes'),
                    'type' => 'checkbox',
                    'description' => '<small>Mode Sandbox/Development digunakan untuk testing transaksi',
                    'default' => 'no'
                ),
                'apikey' => array(
                    'title' => __('importPayment API Key', 'woothemes'),
                    'type' => 'text',
                    'description' => __('<small>Dapatkan API Key Untuk Mengakses Api Pembayaran</small>.', 'woothemes'),
                    'default' => ''
                ),

                'return_url' => array(
                    'title' => __('Url Thank You Page', 'woothemes'),
                    'type' => 'text',
                    'description' => __('<small>Link halaman setelah pembeli melakukan checkout pesanan</small>.', 'woothemes'),
                    'default' => home_url('/checkout/order-received/')
                ),
                'expired_time' => array(
                    'title' => __('Expired kode pembayaran (expiry time of payment code)', 'woothemes'),
                    'type' => 'text',
                    'description' => __('<small>Dalam hitungan jam (in hours)</small>.', 'woothemes'),
                    'default' => '24'
                ),

                /*'debugrecip' => array(
                                'title' => __( 'Debugging Email', 'woothemes' ), 
                                'type' => 'text', 
                                'description' => __( 'Who should receive the debugging emails.', 'woothemes' ), 
                                'default' =>  get_option('admin_email')
                            ),*/
            );
        }

        public function admin_options()
        {
            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
        }

        function payment_fields()
        {
            if ($this->description)
                echo wpautop(wptexturize($this->add_payment_method_field()));
            // echo wpautop(wptexturize($this->description));
        }


        function receipt_page($order)
        {
            echo $this->generate_imporpayment_form($order);
        }


        public function generate_imporpayment_form($order_id)
        {

            global $woocommerce;

            $sandbox_url = 'https://payment.impor.co.id/api/payments';

            $production_url = 'https://payment.impor.co.id/api/payments';

            $payment_method = get_post_meta($order_id, 'imporpayment_payment_method', true);

            $order = new WC_Order($order_id);

            $url = $production_url;

            if ($this->sandbox_mode == 'yes') {
                $url = $sandbox_url;
            }


            $items = [];
            foreach ($order->get_items() as $item) {

                $filteredData = [
                    'sku' => $item->get_data()['product_id'],
                    'name' => $item->get_data()['name'],
                    'description' => $item->get_product()->get_description(),
                    'quantity' => $item->get_data()['quantity'],
                    'price' => $item->get_product()->get_price()
                ];

                $items[] = $filteredData;
            }

            $customer_name = $order->get_billing_first_name() . $order->get_billing_last_name();
            $customer_email = $order->get_billing_email();
            $customer_phone = $order->get_billing_phone();
            $customer_address = $order->get_billing_address_1() . ' ' . $order->get_billing_address_2();
            $data = array(
                // 'key'      => $this->apikey, // API Key
                'payment_method' => $payment_method,
                'invoice_id' => $order_id,
                'amount'    => $order->get_total(),
                'redirect_url' => $this->redirect_url,
                'unotify'  => $this->redirect_url . '&id_order=' . $order_id . '&param=notify',
                'ureturn'  => $this->return_url,
                'ucancel'  => $this->redirect_url . '&id_order=' . $order_id . '&param=cancel',
                'customer_name' => $customer_name,
                'customer_phone' => $customer_phone,
                'customer_email' => $customer_email,
                'customer_address' => $customer_address,
                'expired_at' => date('Y-m-d H:i:s', time() + ($this->expired_time * 3600)),
                'fee' => 0,
                'tax' => $order->get_data()['total_tax'],
                'order_items' => $items
            );


            update_post_meta($order_id, 'imporpayment_payment_method', '');

            // $params_string = http_build_query($data);
            $headers = [
                'accept: accept: application/json',
                'Authorization: Bearer ' . $this->apikey,
                'Content-Type: application/json'
            ];

            //open connection
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

            //execute post
            $request = curl_exec($ch);

            if ($request === false) {

                echo 'Curl Error: ' . curl_error($ch);
            } else {
                $result = json_decode($request, true);

                if (isset($result['data']['payment_url'])) {
                    $order->reduce_order_stock();
                    WC()->cart->empty_cart();
                    wp_redirect($result['data']['payment_url']);
                } else {
                    wp_redirect($_SERVER['HTTP_REFERER']);
                    wc_add_notice(__($result['message'], 'woothemes'), 'error');
                    echo "Request Error : " . json_encode($result);
                }
            }

            //close connection
            curl_close($ch);
        }


        function process_payment($order_id)
        {
            global $woocommerce;
            $order = new WC_Order($order_id);

            return array(
                'result' => 'success',
                'redirect' => $order->get_checkout_payment_url(true)
            );
        }

        function check_imporpayment_response()
        {

            global $woocommerce;



            $payload = file_get_contents('php://input');
            $payload = json_decode($payload, true);

            $order = new WC_Order($payload['invoice_id']);
            $order_received_url = wc_get_endpoint_url('order-received', $payload['invoice_id'], wc_get_page_permalink('checkout'));

            if ('yes' === get_option('woocommerce_force_ssl_checkout') || is_ssl()) {
                $order_received_url = str_replace('http:', 'https:', $order_received_url);
            }


            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if ($payload['status'] == 'paid') {
                    $order->add_order_note(__('Payment Success Impor Payment ID ' . $payload['invoice_id'], 'woocommerce'));
                    // 			$order->update_status( 'completed' );
                    $order->update_status('processing');
                    $order->payment_complete();
                    echo json_encode(['success' => true, 'message' => 'Order ' . $payload['invoice_id'] . ' has been processed']);
                    exit;
                } else if ($payload['status'] == 'pending') {
                    $order->add_order_note(__('Waiting Payment Impor Payment ID ' . $payload['invoice_id'], 'woocommerce'));
                    $order->update_status('on-hold');
                    echo json_encode(['success' => true, 'message' => 'Order ' . $payload['invoice_id'] . ' has been processed']);
                    exit;
                } else if ($payload['status'] == 'expired') {
                    $order->add_order_note(__('Payment Expired Impor Payment ID ' . $payload['invoice_id'] . ' expired', 'woocommerce'));
                    $order->update_status('cancelled');
                    echo json_encode(['success' => true, 'message' => 'Order ' . $payload['invoice_id'] . ' has been processed']);
                    exit;
                } else {
                    echo 'invalid status';
                    exit;
                }
            }


            // $order_received_url = add_query_arg('key', $order->order_key, add_query_arg('order', $_REQUEST['id_order'], $order_received_url));
            // $order_received_url = add_query_arg( 'key', $order->order_key, $order_received_url );
            $order_received_url = add_query_arg('key', $order->get_order_key(), $order_received_url);
            $redirect =  apply_filters('woocommerce_get_checkout_order_received_url', $order_received_url, $this);

            wp_redirect($redirect);
        }
    }

    function add_imporpayment_gateway($methods)
    {
        $methods[] = 'WC_Gateway_ImporPayment';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'add_imporpayment_gateway');
}
