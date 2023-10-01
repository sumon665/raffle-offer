<?php
/**
* Plugin Name: raffle-offer
* Plugin URI: https://github.com/sumon665
* Description: raffle-offer
* Version: 1.0
* Author: Md. Sumon Mia
* Author URI: https://github.com/sumon665
*/

/* Raffle offer setting page */
$pages = array(
    'rafoff-setting'  => array(
        'parent_slug'   => 'options-general.php',
        'page_title'    => __( 'Raffle offer', 'rafoff' ),
        'sections'      => array(
            'section-one'   => array(
                'title'         => __( 'Settings', 'rafoff' ),
                'fields'        => array(
                    'enable_rafoff'      => array(
                        'title'         => __( 'Raffle offer', 'rafoff' ),
                        'type'          => 'checkbox',
                        'text'          => __( 'Check the box enable the Raffle offer', 'rafoff' ),
                    ), 
                    'showrsi_rafoff'      => array(
                        'title'         => __( 'Rewarding social impact', 'rafoff' ),
                        'type'          => 'checkbox',
                        'text'          => __( 'Check the box show the rewarding social impact', 'rafoff' ),
                    ),    
                    'tardis_rafoff'      => array(
                        'title'         => __( 'Target discount', 'rafoff' ),
                        'type'   		=> 'number',
                        'text'          => __( 'Enter the target discount', 'rafoff' ),
                    ),       
                    'dis_rafoff'      => array(
                        'title'         => __( 'Discount amount', 'catalog' ),
                        'text'          => __( 'Save the giving discount amount', 'rafoff' ),
                        // 'attributes'    => array('readonly' => 'readonly'),
                    ),    
                ),
            ),
        
        ),      
    ),
);
$option_page = new RationalOptionPages( $pages );


/* metabox */

require_once "metabox.php";


/* Show Rewarding Social Impact */
add_action('woocommerce_after_add_to_cart_form','rewarding_social_impact_func');

function rewarding_social_impact_func() {
    global $product;
    $options = get_option( 'rafoff-setting', array() );
    $enable_social = $options['rewarding_social_impact'];
    $enable_social_url = $options['rewarding_social_impact_url'];
    $old_offer = $options['discount_amount'];
    $enable_offer = $options['raffle_offer'];
    $pid = $product->get_id();
    $win = get_post_meta( $pid, 'raf_pro_win', true);
    $target_dis = $options['target_discount'];
    $total_dis = array_sum(explode(',', $old_offer));    

    if ($enable_social == "on" && $enable_offer !="on") {
?>
    <div id="reward">
        <h3>Rewarding Social Impact</h3>
        <p>Every time you shop on The Kallective, we donate 100% of your purchase back to charity or social impact projects. </p>
        <!-- Rewarding Social Impact URL -->
        <a class='reward_btn lgbtn' href="#" title="Rewarding Social Impact">How it Works</a>
    </div>

<?php  
    } else {
        if ($enable_offer =="on") {

            if ($total_dis < $target_dis) {
?>
            <div id="reward" class="suboff">
                <?php if ($win > 0): ?>
                <h3>Submit an offer</h3>
                <?php else: ?>
                <h3>Offers Closed</h3>
                <?php endif; ?>

                <?php if ($win > 0): ?>
                <p>Every time you shop on The Kallective, we donate 100% of your purchase back to charity or social impact projects.</p>
                <?php else: ?>
                <p>Thanks for participating in our event. Unfortunately, this item is now sold out!</p>
                <?php endif; ?>
                
                <p class="error_msg"></p>
                <form action="<?php echo esc_url( home_url( '/' ) ); ?>" id="offer_form" method="post">
                    <div class="offer_field">
                            <label>$</label>
                            <input type="number" name="offer_amount"  id="offer_amount" class="offer_amount <?php if ($win < 1):?> rafbtndis <?php endif; ?>" placeholder="Enter Your Offer" required <?php if ($win < 1):?> disabled <?php endif; ?>>
                            <input type="hidden" name="pid" id="pid" value="<?php echo $pid; ?>">
                            <input type="hidden" name="uid" id="uid" value="<?php echo get_current_user_id(); ?>">                            
                    </div>
                    <?php
                        if ( is_user_logged_in() ) { 
                            if (wc_memberships_is_user_active_member(get_current_user_id(), 'kallective-membership')) {
                                
                                if ($win < 1) { 
                                    echo '<button type="submit" id="offerbtn" class="reward_btn rafbtndis" disabled>Submit an Offer</button>';
                                } else {
                                    echo '<button type="submit" id="offerbtn" class="reward_btn">Submit an Offer</button>';
                                } 

                            } else {
                                /*Membership purchase url*/
                                echo "<a id='offerbtn' class='reward_btn lgbtn' href='#'>Become a Member</a>";
                            }
                        } else {
                            /* Login url*/
                            echo "<a id='offerbtn' class='reward_btn lgbtn' href='#'>Login to Participate</a>";
                        }
                    ?>            
                </form>
            </div>

<?php 
        } else {
            /* disable event */
            $options['raffle_offer'] = "";
            update_option('rafoff-setting', $options);

            $my_options = get_option( 'catalog-setting', array() );
            $my_options['enable_catalog_mode'] = 'on';
            update_option('catalog-setting', $my_options);

            header('Location: https://woocommerce-532030-2365908.cloudwaysapps.com/');
            die();
    }

    } 
    
    }
}

/* Add css,js file */
function rafoff_enqueue() {
    wp_enqueue_style( 'rafoff-stye', plugins_url() . '/raffle-offer/css/main.css', array(),  time() );
    wp_enqueue_script('wcs-ajax-script', plugins_url() . '/raffle-offer/js/main.js', array('jquery'), time(), true);
    wp_localize_script( 'wcs-ajax-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ));  
}
add_action( 'wp_enqueue_scripts', 'rafoff_enqueue' );


/* Submit offer */
add_action( 'wp_ajax_submit_offer_request', 'submit_offer_request' );
add_action( 'wp_ajax_nopriv_submit_offer_request', 'submit_offer_request' );

function submit_offer_request() {
    if ( isset($_POST) ) {
        /* new data */
        $pid = $_POST['pid'];
        $uid = $_POST['uid'];
        $offer = $_POST['offer'];

        /* setting data old*/
        $options = get_option( 'rafoff-setting', array() );
        $target_dis = $options['target_discount'];        
        $dis = $options['discount_amount'];

        $total_dis = array_sum(explode(',', $dis));
        $rm_dis = $target_dis - $total_dis;

        /* product page data */
        $max = get_post_meta( $pid, 'raf_pro_max', true);
        $min = get_post_meta( $pid, 'raf_pro_min', true);
        $win = get_post_meta( $pid, 'raf_pro_win', true);

        /* Calculate the discount */
        $product = wc_get_product( $pid );
        $product_price = $product->get_price();
        $new_dis = $product_price - $offer;
 

        if ($offer > $max) {
            $result['error'] = 1;
        } else if ($offer < $min) {
            $result['error'] = 2;
        } else {
            if ($total_dis < $target_dis) {
                if ($rm_dis >= $new_dis) {
                    if ($win > 0 ) {

                        $old_uid = get_post_meta( $pid, 'raf_pro_uid', true);
                        $inpt_uid = array_func ($old_uid, $uid); 

                        $arr_uid = explode(',', $old_uid);

                        if (in_array($uid, $arr_uid)) {
                            $result['error'] = 3; 
                        } else {

                        /* update user meta */   
                        update_user_meta( $uid, 'raf_user_pid', $pid );
                        update_user_meta( $uid, 'raf_user_dis', $new_dis );
                        update_user_meta( $uid, 'raf_user_cart', "on" );

                        /* update product meta*/
                        update_post_meta( $pid, 'raf_pro_uid', $inpt_uid );

                        global $woocommerce;
                        $woocommerce->cart->empty_cart();
                        $woocommerce->cart->add_to_cart( $pid );

                        $result['error'] = 5; 

                        }

                    } else {
                        $result['error'] = 4; 
                    }
                } else {
                    $result['error'] = 1;
                }
            } else {
                $result['error'] = 4;
            }
        }

        echo json_encode($result);
        die();    
    }
}

/* cart validation */
add_filter( 'woocommerce_add_to_cart_validation', 'raf_the_validation', 10, 6 );

function raf_the_validation( $passed, $product_id ) { 
    
    $options = get_option( 'rafoff-setting', array() );
    $enable_offer = $options['raffle_offer'];
    
    $id = get_current_user_id();
    $upid = get_user_meta( $id, 'raf_user_pid', true );
    $add_pro = get_user_meta( $id, 'raf_user_cart', true );

    if ($enable_offer == "on") {
        $passed = false;
        if ( is_user_logged_in() ) { 
            if ($upid == $product_id && $add_pro != "on" ) {
                $passed = true;
            }
        }
    }

    if ($passed==false) {
        wc_add_notice( __( 'Sorry, this item is not currently available for purchase.', 'woocommerce' ), 'error' );
    }

    return $passed;
}


/* Add discount */
add_action( 'woocommerce_cart_calculate_fees', 'bbloomer_add_checkout_fee' );

function bbloomer_add_checkout_fee() {
    
    global $woocommerce;

    $options = get_option( 'rafoff-setting', array() );
    $enable_offer = $options['raffle_offer'];

    $id = get_current_user_id();
    $pid = get_user_meta( $id, 'raf_user_pid', true );
    $dis = get_user_meta( $id, 'raf_user_dis', true );

    if ( is_user_logged_in() ) { 
        
        $items = $woocommerce->cart->get_cart();
       
        foreach($items as $item => $values) { 
            $_product =  $values['data']->get_id(); 
        } 

        if ($pid == $_product && $enable_offer == "on" ) {
            $woocommerce->cart->add_fee( 'Discount', -$dis );
        }
    }
}

/* Thank you page */
add_action( 'woocommerce_thankyou', 'rafoffer_thankyou' );

function rafoffer_thankyou($order_id) {
  
    $order = wc_get_order($order_id);
    $options = get_option( 'rafoff-setting', array() );
    $enable_offer = $options['raffle_offer'];

    $id = get_current_user_id();
    $pid = get_user_meta( $id, 'raf_user_pid', true );
    $dis = get_user_meta( $id, 'raf_user_dis', true );
    $old_dis = $options['discount_amount'];
    $inpt_dis = array_func ($old_dis, $dis);
    $win = get_post_meta( $pid, 'raf_pro_win', true);
    $new_win = $win - 1;
    
    if ( ! $order->has_status( 'failed' ) && $enable_offer == "on" ) { 
        $options['discount_amount'] = $inpt_dis;
        update_option('rafoff-setting', $options);
        update_post_meta( $pid, 'raf_pro_win', $new_win );
    }

}

/* Array to string */
function array_func ($old, $new) {
    if ($old) {
        $arr = explode (",", $old);
        $arr[] = $new;
        $enter = implode (",", $arr);
        return $enter;  
    } else {
        return $new;          
    }
}


// Shortcode [calculation goal='100' pid='18,23' from='2022-01-01' to='2022-01-31']
add_shortcode( 'calculation', 'display_num_items_sold_func' );

function display_num_items_sold_func( $atts ) {
    // Shortcode attribute (or argument)
    extract( shortcode_atts( array(
        'goal'   => '',   
        'pid'    => '',   
        'from'   => '',
        'to'     => '',
    ), $atts, 'items_sold' ) );

    // Formating dates
    $date_from = date('Y-m-d', strtotime($from) );
    $date_to   = date('Y-m-d', strtotime($to) );
    $sold = 0;

    global $wpdb;

    $sql = "
    SELECT COUNT(*) AS sale_count
    FROM {$wpdb->prefix}woocommerce_order_items AS order_items
    INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_meta ON order_items.order_item_id = order_meta.order_item_id
    INNER JOIN {$wpdb->posts} AS posts ON order_meta.meta_value = posts.ID
    WHERE order_items.order_item_type = 'line_item'
    AND order_meta.meta_key = '_product_id'
    AND order_meta.meta_value = %d
    AND order_items.order_id IN (
        SELECT posts.ID AS post_id
        FROM {$wpdb->posts} AS posts
        WHERE posts.post_type = 'shop_order'
            AND posts.post_status IN ('wc-completed','wc-processing')
            AND DATE(posts.post_date) BETWEEN %s AND %s
    )
    GROUP BY order_meta.meta_value";


    if (explode(",",$pid)) {
        foreach (explode(",",$pid) as $proid) {
            $count = $wpdb->get_var($wpdb->prepare($sql, $proid, $date_from, $date_to));
            $_product = wc_get_product( $proid );

            if ($_product) {
                $sold += ((($_product->get_regular_price() * $count)/100)*20);
            }
        }
    } else {
            $count = $wpdb->get_var($wpdb->prepare($sql, $pid, $date_from, $date_to));
            $_product = wc_get_product( $pid );
            if ($_product) {
                $sold += ((($_product->get_regular_price() * $count)/100)*20);
            }
    }

    return '<span class="goal_cal">$'.number_format($sold).' pledged of $'.number_format($goal).' goal</span>';
}


/* shortcode [inventory pid='18,23'] */
add_shortcode( 'inventory', 'inventory_func' );

function inventory_func( $atts ) {
    $a = shortcode_atts( array(
        'pid' => '',
    ), $atts );

    $pid = $a['pid'];
    $stock = "";

    if (explode(",",$pid)) {
        foreach (explode(",",$pid) as $proid) {
            $product = wc_get_product($proid);
            if ($product) {
                $stock = 0;
                $stock += $product->get_stock_quantity();    
            }
        }
    } else {
        $product = wc_get_product($pid);
        if ($product) {
            $stock = $product->get_stock_quantity();
        }
    }

    return '<span class="item_stock">'.$stock.' Spots Remaining</span>';
}