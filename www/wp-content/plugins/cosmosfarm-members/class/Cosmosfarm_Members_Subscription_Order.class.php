<?php
/**
 * Cosmosfarm_Members_Subscription_Order
 * @link https://www.cosmosfarm.com/
 * @copyright Copyright 2020 Cosmosfarm. All rights reserved.
 */
class Cosmosfarm_Members_Subscription_Order {
	
	var $post_type = 'cosmosfarm_order';
	var $post;
	var $post_id = 0;
	
	public function __construct($order_id=''){
		if($order_id){
			$this->init_with_id($order_id);
		}
	}
	
	public function __get($name){
		if($this->post_id && isset($this->post->{$name})){
			return $this->post->{$name};
		}
		return '';
	}
	
	public function __set($name, $value){
		if($this->post_id){
			$this->post->{$name} = $value;
		}
	}
	
	public function order_post_type(){
		return 'cosmosfarm_order';
	}
	
	public function init_with_id($post_id){
		$this->post_id = 0;
		$post_id = intval($post_id);
		if($post_id){
			$this->post = get_post($post_id);
			if($this->post && $this->post->ID){
				$this->post_id = $this->post->ID;
			}
		}
	}
	
	public function ID(){
		return intval($this->post_id);
	}
	
	public function title(){
		return $this->post_title;
	}
	
	public function content(){
		return $this->post_content;
	}
	
	public function excerpt(){
		return $this->post_excerpt ? $this->post_excerpt : $this->post_content;
	}
	
	public function name(){
		return $this->post_name;
	}
	
	public function user_id(){
		return $this->post_author;
	}
	
	public function user(){
		if($this->post_author){
			return new WP_User($this->post_author);
		}
		return new WP_User();
	}
	
	public function create($user_id, $args){
		$user_id = intval($user_id);
		$title = isset($args['title']) ? $args['title'] : '';
		$content = isset($args['content']) ? $args['content'] : '';
		$excerpt = isset($args['excerpt']) ? $args['excerpt'] : '';
		$name = isset($args['name']) ? $args['name'] : '';
		$meta_input = isset($args['meta_input']) ? $args['meta_input'] : array();
		
		$this->post_id = wp_insert_post(array(
			'post_title'     => wp_strip_all_tags($title),
			'post_content'   => $content,
			'post_excerpt'   => $excerpt,
			'post_name'      => $name,
			'post_status'    => 'publish',
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_author'    => $user_id,
			'post_type'      => $this->post_type,
			'meta_input'     => $meta_input
		));
		return $this->post_id;
	}
	
	public function update($args){
		if($this->post_id){
			$args['ID'] = $this->post_id;
			
			if(isset($args['title'])){
				$args['post_title'] = $args['title'];
			}
			
			if(isset($args['content'])){
				$args['post_content'] = $args['content'];
			}
			
			if(isset($args['excerpt'])){
				$args['post_excerpt'] = $args['excerpt'];
			}
			
			if(isset($args['name'])){
				$args['post_name'] = $args['name'];
			}
			
			wp_update_post($args);
		}
	}
	
	public function delete(){
		if($this->post_id){
			wp_delete_post($this->post_id);
		}
	}
	
	public function set_product_id($product_id){
		if($this->post_id){
			$product_id= intval($product_id);
			update_post_meta($this->post_id, 'product_id', $product_id);
		}
	}
	
	public function product_id(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'product_id', true);
			if($value){
				return intval($value);
			}
		}
		return 0;
	}
	
	public function set_sequence_id($sequence_id){
		if($this->post_id){
			update_post_meta($this->post_id, 'sequence_id', $sequence_id);
		}
	}
	
	public function sequence_id(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'sequence_id', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	/**
	 * ????????? sequence_id??? ???????????? ?????? ????????? ????????????.
	 * @return Cosmosfarm_Members_Subscription_Order
	 */
	public function get_prev_order(){
		global $wpdb;
		$order = new Cosmosfarm_Members_Subscription_Order();
		if($this->post_id){
			$sequence_id = esc_sql($this->sequence_id());
			$order_id = $wpdb->get_var("SELECT {$wpdb->prefix}posts.ID FROM {$wpdb->prefix}posts INNER JOIN {$wpdb->prefix}postmeta ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE 1=1 AND {$wpdb->prefix}posts.ID < '{$this->post_id}' AND ( ( {$wpdb->prefix}postmeta.meta_key = 'sequence_id' AND {$wpdb->prefix}postmeta.meta_value LIKE '{$sequence_id}' ) ) AND {$wpdb->prefix}posts.post_type = 'cosmosfarm_order' GROUP BY {$wpdb->prefix}posts.ID ORDER BY {$wpdb->prefix}posts.ID DESC LIMIT 1");
			if($order_id){
				$order->init_with_id($order_id);
			}
		}
		return $order;
	}
	
	/**
	 * ????????? sequence_id??? ???????????? ?????? ????????? ????????????.
	 * @return Cosmosfarm_Members_Subscription_Order
	 */
	public function get_next_order(){
		global $wpdb;
		$order = new Cosmosfarm_Members_Subscription_Order();
		if($this->post_id){
			$sequence_id = esc_sql($this->sequence_id());
			$order_id = $wpdb->get_var("SELECT {$wpdb->prefix}posts.ID FROM {$wpdb->prefix}posts INNER JOIN {$wpdb->prefix}postmeta ON ( {$wpdb->prefix}posts.ID = {$wpdb->prefix}postmeta.post_id ) WHERE 1=1 AND {$wpdb->prefix}posts.ID > '{$this->post_id}' AND ( ( {$wpdb->prefix}postmeta.meta_key = 'sequence_id' AND {$wpdb->prefix}postmeta.meta_value LIKE '{$sequence_id}' ) ) AND {$wpdb->prefix}posts.post_type = 'cosmosfarm_order' GROUP BY {$wpdb->prefix}posts.ID ORDER BY {$wpdb->prefix}posts.ID ASC LIMIT 1");
			if($order_id){
				$order->init_with_id($order_id);
			}
		}
		return $order;
	}
	
	public function set_price($price){
		if($this->post_id){
			$price = floatval($price);
			update_post_meta($this->post_id, 'price', $price);
		}
	}
	
	public function price(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'price', true);
			if($value){
				$price = floatval($value);
			}
			else{
				$price = 0;
			}
			return apply_filters('cosmosfarm_members_subscription_product_price', $price, $this);
		}
		return 0;
	}
	
	public function set_first_price($first_price){
		if($this->post_id){
			$first_price = floatval($first_price);
			update_post_meta($this->post_id, 'first_price', $first_price);
		}
	}
	
	public function first_price(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'first_price', true);
			if($value){
				$first_price = floatval($value);
			}
			else{
				$first_price = $this->price();
			}
			return apply_filters('cosmosfarm_members_subscription_product_first_price', $first_price, $this);
		}
		return 0;
	}
	
	/**
	 * ??? ?????? ????????? ?????? ?????? ?????? ?????? ?????? ???????????? ????????????.
	 * @return int
	 */
	public function first_price_discount_rate(){
		$discount_rate = 0;
		if($this->post_id){
			if($this->first_price() && $this->first_price() != $this->price()){
				$discount_rate = round(($this->price()-$this->first_price())/$this->price()*100);
			}
		}
		return $discount_rate;
	}
	
	/**
	 * ??? ?????? ????????? ?????? ?????? ?????? ?????? ?????? ???????????? ????????????.
	 * @param string $format
	 * @return string
	 */
	public function first_price_discount_rate_format($format='%d%%'){
		$discount_rate_format = '';
		if($this->post_id){
			if($this->first_price_discount_rate()){
				$discount_rate_format = sprintf($format, $this->first_price_discount_rate());
			}
		}
		return $discount_rate_format;
	}
	
	/**
	 * ?????? ????????? ????????????.
	 * @param int $balance
	 */
	public function set_balance($balance){
		if($this->post_id){
			$balance = floatval($balance);
			update_post_meta($this->post_id, 'balance', $balance);
		}
	}
	
	/**
	 * ?????? ????????? ????????????.
	 * @return int
	 */
	public function balance(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'balance', true);
			if($value){
				$balance = floatval($value);
			}
			else{
				$balance = 0;
			}
			return apply_filters('cosmosfarm_members_subscription_product_balance', $balance, $this);
		}
		return 0;
	}
	
	/**
	 * ????????? ?????? ID ?????? ????????????.
	 * @param int $coupon_id
	 */
	public function set_coupon_id($coupon_id){
		if($this->post_id){
			$coupon_id = intval($coupon_id);
			update_post_meta($this->post_id, 'coupon_id', $coupon_id);
		}
	}
	
	/**
	 * ????????? ?????? ID ?????? ????????????.
	 * @return int
	 */
	public function coupon_id(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'coupon_id', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	/**
	 * ?????? ?????? ????????? ????????????.
	 * @param int $coupon_price
	 */
	public function set_coupon_price($coupon_price){
		if($this->post_id){
			$coupon_price = floatval($coupon_price);
			update_post_meta($this->post_id, 'coupon_price', $coupon_price);
		}
	}
	
	/**
	 * ?????? ?????? ?????? ????????? ????????? ????????????.
	 * @return int
	 */
	public function coupon_price(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'coupon_price', true);
			if($value){
				$coupon_price = floatval($value);
			}
			else{
				$coupon_price = 0;
			}
			return apply_filters('cosmosfarm_members_subscription_product_coupon_price', $coupon_price, $this);
		}
		return 0;
	}
	
	/**
	 * ?????? ?????? ??? ????????? ????????????.
	 * @param int $coupon_price
	 */
	public function set_before_coupon_price($before_coupon_price){
		if($this->post_id){
			$before_coupon_price = floatval($before_coupon_price);
			update_post_meta($this->post_id, 'before_coupon_price', $before_coupon_price);
		}
	}
	
	/**
	 * ?????? ?????? ??? ?????? ????????? ????????? ????????????.
	 * @return int
	 */
	public function before_coupon_price(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'before_coupon_price', true);
			if($value){
				$before_coupon_price = floatval($value);
			}
			else{
				$before_coupon_price = 0;
			}
			return apply_filters('cosmosfarm_members_subscription_product_before_coupon_price', $before_coupon_price, $this);
		}
		return 0;
	}
	
	/**
	 * ???????????? ?????? ????????? [?????????]?????? ????????????.
	 */
	public function set_status_paid(){
		if($this->post_id){
			do_action('cosmosfarm_members_subscription_order_status_update', $this, 'paid');
			update_post_meta($this->post_id, 'status', 'paid');
		}
	}
	
	/**
	 * ???????????? ?????? ????????? [?????????]?????? ????????????.
	 */
	public function set_status_cancelled(){
		if($this->post_id){
			do_action('cosmosfarm_members_subscription_order_status_update', $this, 'cancelled');
			update_post_meta($this->post_id, 'status', 'cancelled');
		}
	}
	
	public function status(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'status', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	public function get_status(){
		return $this->status();
	}
	
	public function status_format(){
		$status_format = '';
		if($this->post_id){
			switch($this->status()){
				case 'paid': $status_format = '?????????'; break;
				case 'cancelled': $status_format = '?????????'; break;
			}
		}
		return apply_filters('cosmosfarm_members_status_format', $status_format, $this);
	}
	
	public function get_type(){
		if($this->post_id){
			$item_type = get_post_meta($this->post_id, 'item_type', true);
			if($item_type){
				return $item_type;
			}
		}
		return 'default';
	}
	
	public function is_paid(){
		if($this->status() == 'paid'){
			return true;
		}
		return false;
	}
	
	public function is_cancelled(){
		if($this->status() == 'cancelled'){
			return true;
		}
		return false;
	}
	
	public function set_subscription_type($subscription_type){
		if($this->post_id){
			update_post_meta($this->post_id, 'subscription_type', $subscription_type);
		}
	}
	
	public function subscription_type_format(){
		$subscription_type_format = '';
		if($this->post_id){
			switch($this->subscription_type()){
				case 'onetime': $subscription_type_format = '????????????'; break;
				case 'daily': $subscription_type_format = '1???'; break;
				case 'weekly': $subscription_type_format = '1??????'; break;
				case '2weekly': $subscription_type_format = '2??????'; break;
				case '3weekly': $subscription_type_format = '3??????'; break;
				case '4weekly': $subscription_type_format = '4??????'; break;
				case 'monthly': $subscription_type_format = '1??????'; break;
				case '2monthly': $subscription_type_format = '2??????'; break;
				case '3monthly': $subscription_type_format = '3??????'; break;
				case '4monthly': $subscription_type_format = '4??????'; break;
				case '5monthly': $subscription_type_format = '5??????'; break;
				case '6monthly': $subscription_type_format = '6??????'; break;
				case '7monthly': $subscription_type_format = '7??????'; break;
				case '8monthly': $subscription_type_format = '8??????'; break;
				case '9monthly': $subscription_type_format = '9??????'; break;
				case '10monthly': $subscription_type_format = '10??????'; break;
				case '11monthly': $subscription_type_format = '11??????'; break;
				case '12monthly': $subscription_type_format = '1???'; break;
				case '24monthly': $subscription_type_format = '2???'; break;
			}
		}
		return apply_filters('cosmosfarm_members_subscription_product_type_format', $subscription_type_format, $this);
	}
	
	public function next_subscription_datetime($ymdhis='', $format='YmdHis'){
		$datetime = '';
		if($this->post_id){
			if($ymdhis){
				$timestamp = strtotime($ymdhis);
			}
			else{
				$timestamp = current_time('timestamp');
			}
			switch($this->subscription_type()){
				case 'daily': $datetime = date($format, strtotime('+1 day', $timestamp)); break;
				case 'weekly': $datetime = date($format, strtotime('+1 week', $timestamp)); break;
				case '2weekly': $datetime = date($format, strtotime('+2 week', $timestamp)); break;
				case '3weekly': $datetime = date($format, strtotime('+3 week', $timestamp)); break;
				case '4weekly': $datetime = date($format, strtotime('+4 week', $timestamp)); break;
				case 'monthly': $datetime = date($format, strtotime('+1 month', $timestamp)); break;
				case '2monthly': $datetime = date($format, strtotime('+2 month', $timestamp)); break;
				case '3monthly': $datetime = date($format, strtotime('+3 month', $timestamp)); break;
				case '4monthly': $datetime = date($format, strtotime('+4 month', $timestamp)); break;
				case '5monthly': $datetime = date($format, strtotime('+5 month', $timestamp)); break;
				case '6monthly': $datetime = date($format, strtotime('+6 month', $timestamp)); break;
				case '7monthly': $datetime = date($format, strtotime('+7 month', $timestamp)); break;
				case '8monthly': $datetime = date($format, strtotime('+8 month', $timestamp)); break;
				case '9monthly': $datetime = date($format, strtotime('+9 month', $timestamp)); break;
				case '10monthly': $datetime = date($format, strtotime('+10 month', $timestamp)); break;
				case '11monthly': $datetime = date($format, strtotime('+11 month', $timestamp)); break;
				case '12monthly': $datetime = date($format, strtotime('+1 year', $timestamp)); break;
				case '24monthly': $datetime = date($format, strtotime('+2 year', $timestamp)); break;
			}
		}
		return $datetime;
	}
	
	public function subscription_type(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'subscription_type', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	public function set_subscription_active($subscription_active){
		if($this->post_id){
			update_post_meta($this->post_id, 'subscription_active', $subscription_active);
		}
	}
	
	public function subscription_active(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'subscription_active', true);
			if($value){
				return true;
			}
		}
		return false;
	}
	
	public function set_subscription_first_free($subscription_first_free){
		if($this->post_id){
			update_post_meta($this->post_id, 'subscription_first_free', $subscription_first_free);
		}
	}
	
	public function subscription_first_free(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'subscription_first_free', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	public function subscription_first_free_format(){
		$subscription_first_free = '';
		if($this->post_id){
			switch($this->subscription_first_free()){
				case '1day': $subscription_first_free = '1???'; break;
				case '2day': $subscription_first_free = '2???'; break;
				case '3day': $subscription_first_free = '3???'; break;
				case '4day': $subscription_first_free = '4???'; break;
				case '5day': $subscription_first_free = '5???'; break;
				case '6day': $subscription_first_free = '6???'; break;
				case '7day': $subscription_first_free = '7???'; break;
				case '8day': $subscription_first_free = '8???'; break;
				case '9day': $subscription_first_free = '9???'; break;
				case '10day': $subscription_first_free = '10???'; break;
				case '11day': $subscription_first_free = '11???'; break;
				case '12day': $subscription_first_free = '12???'; break;
				case '13day': $subscription_first_free = '13???'; break;
				case '14day': $subscription_first_free = '14???'; break;
				case '15day': $subscription_first_free = '15???'; break;
				case '16day': $subscription_first_free = '16???'; break;
				case '17day': $subscription_first_free = '17???'; break;
				case '18day': $subscription_first_free = '18???'; break;
				case '19day': $subscription_first_free = '19???'; break;
				case '20day': $subscription_first_free = '20???'; break;
				case '21day': $subscription_first_free = '21???'; break;
				case '22day': $subscription_first_free = '22???'; break;
				case '23day': $subscription_first_free = '23???'; break;
				case '24day': $subscription_first_free = '24???'; break;
				case '25day': $subscription_first_free = '25???'; break;
				case '26day': $subscription_first_free = '26???'; break;
				case '27day': $subscription_first_free = '27???'; break;
				case '28day': $subscription_first_free = '28???'; break;
				case '29day': $subscription_first_free = '29???'; break;
				case '30day': $subscription_first_free = '30???'; break;
				case '1month': $subscription_first_free = '1??????'; break;
				case '2month': $subscription_first_free = '2??????'; break;
				case '3month': $subscription_first_free = '3??????'; break;
				default: $subscription_first_free = '?????? ???????????? ??????';
			}
		}
		return apply_filters('cosmosfarm_members_subscription_first_free_format', $subscription_first_free, $this);
	}
	
	public function next_subscription_datetime_first_free($ymdhis='', $format='YmdHis'){
		$datetime = '';
		if($this->post_id){
			if($ymdhis){
				$timestamp = strtotime($ymdhis);
			}
			else{
				$timestamp = current_time('timestamp');
			}
			switch($this->subscription_first_free()){
				case '1day': $datetime = date($format, strtotime('+1 day', $timestamp)); break;
				case '2day': $datetime = date($format, strtotime('+2 day', $timestamp)); break;
				case '3day': $datetime = date($format, strtotime('+3 day', $timestamp)); break;
				case '4day': $datetime = date($format, strtotime('+4 day', $timestamp)); break;
				case '5day': $datetime = date($format, strtotime('+5 day', $timestamp)); break;
				case '6day': $datetime = date($format, strtotime('+6 day', $timestamp)); break;
				case '7day': $datetime = date($format, strtotime('+7 day', $timestamp)); break;
				case '8day': $datetime = date($format, strtotime('+8 day', $timestamp)); break;
				case '9day': $datetime = date($format, strtotime('+9 day', $timestamp)); break;
				case '10day': $datetime = date($format, strtotime('+10 day', $timestamp)); break;
				case '11day': $datetime = date($format, strtotime('+11 day', $timestamp)); break;
				case '12day': $datetime = date($format, strtotime('+12 day', $timestamp)); break;
				case '13day': $datetime = date($format, strtotime('+13 day', $timestamp)); break;
				case '14day': $datetime = date($format, strtotime('+14 day', $timestamp)); break;
				case '15day': $datetime = date($format, strtotime('+15 day', $timestamp)); break;
				case '16day': $datetime = date($format, strtotime('+16 day', $timestamp)); break;
				case '17day': $datetime = date($format, strtotime('+17 day', $timestamp)); break;
				case '18day': $datetime = date($format, strtotime('+18 day', $timestamp)); break;
				case '19day': $datetime = date($format, strtotime('+19 day', $timestamp)); break;
				case '20day': $datetime = date($format, strtotime('+20 day', $timestamp)); break;
				case '21day': $datetime = date($format, strtotime('+21 day', $timestamp)); break;
				case '22day': $datetime = date($format, strtotime('+22 day', $timestamp)); break;
				case '23day': $datetime = date($format, strtotime('+23 day', $timestamp)); break;
				case '24day': $datetime = date($format, strtotime('+24 day', $timestamp)); break;
				case '25day': $datetime = date($format, strtotime('+25 day', $timestamp)); break;
				case '26day': $datetime = date($format, strtotime('+26 day', $timestamp)); break;
				case '27day': $datetime = date($format, strtotime('+27 day', $timestamp)); break;
				case '28day': $datetime = date($format, strtotime('+28 day', $timestamp)); break;
				case '29day': $datetime = date($format, strtotime('+29 day', $timestamp)); break;
				case '30day': $datetime = date($format, strtotime('+30 day', $timestamp)); break;
				case '1month': $datetime = date($format, strtotime('+1 month', $timestamp)); break;
				case '2month': $datetime = date($format, strtotime('+2 month', $timestamp)); break;
				case '3month': $datetime = date($format, strtotime('+3 month', $timestamp)); break;
			}
		}
		return $datetime;
	}
	
	public function set_subscription_again_price_type($subscription_again_price_type){
		if($this->post_id){
			update_post_meta($this->post_id, 'subscription_again_price_type', $subscription_again_price_type);
		}
	}
	
	public function subscription_again_price_type(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'subscription_again_price_type', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	public function set_subscription_role($subscription_role){
		if($this->post_id){
			update_post_meta($this->post_id, 'subscription_role', $subscription_role);
		}
	}
	
	public function subscription_role(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'subscription_role', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	public function set_subscription_prev_role($subscription_prev_role){
		if($this->post_id){
			update_post_meta($this->post_id, 'subscription_prev_role', $subscription_prev_role);
		}
	}
	
	public function subscription_prev_role(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'subscription_prev_role', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	public function set_subscription_multiple_pay($subscription_multiple_pay){
		if($this->post_id){
			update_post_meta($this->post_id, 'subscription_multiple_pay', $subscription_multiple_pay);
		}
	}
	
	public function subscription_multiple_pay(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'subscription_multiple_pay', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	/**
	 * ????????? ?????? ?????? ????????? ????????????.
	 * @return boolean
	 */
	public function is_subscription_multiple_pay(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'subscription_multiple_pay', true);
			if($value && !$this->subscription_role()){ // ????????? ??????(Role) ????????? ?????? ???????????? ??????, ????????? ????????? ??? ????????? ?????? ???????????? ?????? ??????
				return true;
			}
		}
		return false;
	}
	
	public function set_start_datetime($start_datetime=''){
		if($this->post_id){
			if(!$start_datetime){
				$start_datetime = date('YmdHis', current_time('timestamp'));
			}
			else{
				$start_datetime = date('YmdHis', strtotime($start_datetime));
			}
			update_post_meta($this->post_id, 'start_datetime', $start_datetime);
		}
	}
	
	public function start_datetime(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'start_datetime', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	public function set_end_datetime($end_datetime){
		if($this->post_id){
			$end_datetime = date('YmdHis', strtotime($end_datetime));
			update_post_meta($this->post_id, 'end_datetime', $end_datetime);
		}
	}
	
	public function end_datetime(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'end_datetime', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	public function set_subscription_next($subscription_next){
		if($this->post_id && in_array($subscription_next, array('success', 'wait', 'cancel', 'expiry'))){
			update_post_meta($this->post_id, 'subscription_next', $subscription_next);
		}
	}
	
	public function subscription_next(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'subscription_next', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	public function subscription_next_format(){
		$subscription_next = '';
		if($this->post_id){
			switch($this->subscription_next()){
				case 'wait': $subscription_next = '?????????'; break;
				case 'expiry': $subscription_next = '?????????'; break;
				case 'success': $subscription_next = ''; break;
			}
		}
		return apply_filters('cosmosfarm_members_subscription_next_format', $subscription_next, $this);
	}
	
	public function set_pay_count($pay_count){
		if($this->post_id){
			$pay_count = intval($pay_count);
			update_post_meta($this->post_id, 'pay_count', $pay_count);
		}
	}
	
	public function pay_count(){
		$value = 0;
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'pay_count', true);
		}
		return intval($value);
	}
	
	public function set_pay_count_limit($pay_count_limit){
		if($this->post_id){
			$pay_count_limit = intval($pay_count_limit);
			update_post_meta($this->post_id, 'pay_count_limit', $pay_count_limit);
		}
	}
	
	public function pay_count_limit(){
		$value = 0;
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'pay_count_limit', true);
		}
		return intval($value);
	}
	
	/**
	 * ???????????? ?????? ????????? ????????????.
	 * @param string $iamport_customer_uid
	 */
	public function set_iamport_customer_uid($iamport_customer_uid){
		if($this->post_id){
			update_post_meta($this->post_id, 'iamport_customer_uid', $iamport_customer_uid);
		}
	}
	
	/**
	 * ???????????? ?????? ????????? ????????????.
	 * @return string
	 */
	public function iamport_customer_uid(){
		$value = '';
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'iamport_customer_uid', true);
		}
		return $value;
	}
	
	/**
	 * ?????? ????????? ????????????.
	 * @param string $payment_method
	 */
	public function set_payment_method($payment_method){
		if($this->post_id){
			update_post_meta($this->post_id, 'payment_method', $payment_method);
		}
	}
	
	/**
	 * ?????? ????????? ????????????.
	 * @return string
	 */
	public function payment_method(){
		$value = '';
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'payment_method', true);
			if(!$value){
				$value = 'card';
			}
		}
		return $value;
	}
	
	/**
	 * ???????????? ????????????.
	 * @param string $courier_company
	 */
	public function set_courier_company($courier_company){
		if($this->post_id){
			update_post_meta($this->post_id, 'courier_company', $courier_company);
		}
	}
	
	/**
	 * ???????????? ????????????.
	 * @return string
	 */
	public function courier_company(){
		$value = '';
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'courier_company', true);
		}
		return $value;
	}
	
	/**
	 * ????????? ????????? ????????????.
	 * @param string $tracking_code
	 */
	public function set_tracking_code($tracking_code){
		if($this->post_id){
			update_post_meta($this->post_id, 'tracking_code', $tracking_code);
		}
	}
	
	/**
	 * ????????? ????????? ????????????.
	 * @return string
	 */
	public function tracking_code(){
		$value = '';
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'tracking_code', true);
		}
		return $value;
	}
	
	/**
	 * ?????? ???????????? ????????? ????????????.
	 * @param string $order_comment
	 */
	public function set_order_comment($order_comment){
		if($this->post_id){
			update_post_meta($this->post_id, 'order_comment', $order_comment);
		}
	}
	
	/**
	 * ?????? ????????? ????????? ????????????.
	 * @return string
	 */
	public function order_comment(){
		$value = '';
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'order_comment', true);
		}
		return $value;
	}
	
	/**
	 * ?????? ????????? ????????????.
	 * @param string $coupon_code
	 */
	public function set_coupon_code($coupon_code){
		if($this->post_id){
			update_post_meta($this->post_id, 'coupon_code', $coupon_code);
		}
	}
	
	/**
	 * ?????? ????????? ????????????.
	 * @return string
	 */
	public function coupon_code(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'coupon_code', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	/**
	 * ????????? ?????? ????????? ????????????.
	 * @param string $earn_points_type
	 */
	public function set_earn_points_type($earn_points_type){
		if($this->post_id){
			update_post_meta($this->post_id, 'earn_points_type', $earn_points_type);
		}
	}
	
	/**
	 * ????????? ?????? ????????? ????????????.
	 * @return string
	 */
	public function earn_points_type(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'earn_points_type', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	/**
	 * ?????? ???????????? ????????????.
	 * @param int $earn_points
	 */
	public function set_earn_points($earn_points){
		if($this->post_id){
			update_post_meta($this->post_id, 'earn_points', $earn_points);
		}
	}
	
	/**
	 * ?????? ???????????? ????????????.
	 * @return int
	 */
	public function earn_points(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'earn_points', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	/**
	 * ????????? ?????? ????????? ??????????????? ????????????.
	 * @return boolean
	 */
	public function is_use_earn_points(){
		if(function_exists('mycred_add') && function_exists('mycred_get_users_balance')){
			if($this->earn_points()){
				return true;
			}
		}
		return false;
	}
	
	/**
	 * PG ????????? ????????????.
	 * @param string $builtin_pg
	 */
	public function set_builtin_pg($builtin_pg){
		if($this->post_id){
			update_post_meta($this->post_id, 'builtin_pg', $builtin_pg);
		}
	}
	
	/**
	 * PG ????????? ????????????.
	 * @return string
	 */
	public function builtin_pg(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'builtin_pg', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	/**
	 * PG ??????????????? ????????????.
	 * @param string $builtin_pg_id
	 */
	public function set_builtin_pg_id($builtin_pg_id){
		if($this->post_id){
			update_post_meta($this->post_id, 'builtin_pg_id', $builtin_pg_id);
		}
	}
	
	/**
	 * PG ??????????????? ????????????.
	 * @return string
	 */
	public function builtin_pg_id(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'builtin_pg_id', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	/**
	 * PG ????????????TID??? ????????????.
	 * @param string $builtin_pg_tid
	 */
	public function set_builtin_pg_tid($builtin_pg_tid){
		if($this->post_id){
			update_post_meta($this->post_id, 'builtin_pg_tid', $builtin_pg_tid);
		}
	}
	
	/**
	 * PG ????????????TID??? ????????????.
	 * @return string
	 */
	public function builtin_pg_tid(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'builtin_pg_tid', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	/**
	 * ????????? ID?????? ????????????.
	 * @return string
	 */
	public function cancel_id(){
		if($this->post_id){
			if($this->imp_uid){
				return $this->imp_uid;
			}
			return $this->builtin_pg_tid();
		}
		return '';
	}
	
	/**
	 * ?????? ????????? ?????? ???????????? ????????????.
	 * @param string $error_message
	 */
	public function set_error_message($error_message){
		if($this->post_id){
			update_post_meta($this->post_id, 'error_message', $error_message);
		}
	}
	
	/**
	 * ?????? ????????? ?????? ???????????? ????????????.
	 * @return string
	 */
	public function error_message(){
		if($this->post_id){
			$value = get_post_meta($this->post_id, 'error_message', true);
			if($value){
				return $value;
			}
		}
		return '';
	}
	
	/**
	 * ?????? ????????? ?????? ???????????? ??????????????? ????????????.
	 */
	public function show_error_message_admin_notice(){
		if($this->post_id){
			update_post_meta($this->post_id, 'error_message_admin_notice', '1');
		}
	}
	
	/**
	 * ?????? ????????? ?????? ???????????? ??????????????? ?????????.
	 * @return string
	 */
	public function hide_error_message_admin_notice(){
		if($this->post_id){
			update_post_meta($this->post_id, 'error_message_admin_notice', '');
		}
	}
	
	/**
	 * ?????? ??????????????? ???????????? ?????? ????????? ????????????.
	 */
	public function set_subscription_again_error(){
		if($this->post_id){
			update_post_meta($this->post_id, 'subscription_again_error', '1');
		}
	}
	
	/**
	 * ?????? ????????? ????????????. (????????????:billing, ????????????:general)
	 * @return string
	 */
	public function get_pg_type(){
		if($this->post_id){
			$pg_type = $this->pg_type;
			if(!$pg_type){
				$product = new Cosmosfarm_Members_Subscription_Product($this->product_id());
				$pg_type = $product->get_subscription_pg_type();
			}
			return $pg_type;
		}
		return '';
	}
	
	/**
	 * ?????? ?????? ??? ??????????????? ????????????.
	 * @param int $user_id
	 * @return Cosmosfarm_Members_Subscription_Order
	 */
	public function is_in_use($user_id=''){
		if($this->post_id){
			if(!$user_id){
				$user_id = get_current_user_id();
			}
			
			$user = new WP_User($user_id);
			
			if($user->ID && $this->product_id()){
				$args = array(
					'post_type'  => $this->order_post_type(),
					'author' => $user->ID,
					'orderby' => 'ID',
					'posts_per_page' => -1,
					'meta_query' => array(
						array(
							'key'     => 'product_id',
							'value'   => $this->product_id(),
							'compare' => '=',
						),
						array(
							'key'     => 'status',
							'value'   => 'paid',
							'compare' => '=',
						),
						array(
							'key'     => 'subscription_next',
							'value'   => array('success', 'wait'),
							'compare' => 'IN',
						),
					),
				);
				$query = new WP_Query($args);
				if($query->found_posts){
					return new Cosmosfarm_Members_Subscription_Order($query->post->ID);
				}
			}
		}
		return false;
	}
	
	public function get_order_field_template($field=array(), $field2=array(), $field3=array()){
		ob_start();
		if(isset($field['type']) && $field['type']){
			$order = $this;
			$field_type = $field['type'];
			include COSMOSFARM_MEMBERS_DIR_PATH . '/admin/subscription_order_field_template.php';
			do_action('cosmosfarm_members_order_admin_field_template', $order, $field_type, $field, $field2, $field3);
		}
		return ob_get_clean();
	}
	
	public function execute_expiry_action(){
		if($this->post_id){
			if($this->subscription_type() == 'onetime'){
				$this->set_subscription_next('cancel');
				
				if($this->subscription_prev_role()){
					$user = $this->user();
					
					if(!is_super_admin($user->ID)){
						$user->remove_role($this->subscription_role());
						$user->add_role($this->subscription_prev_role());
					}
				}
				
				$order = $this;
				$product = new Cosmosfarm_Members_Subscription_Product($order->product_id());
				
				do_action('cosmosfarm_members_subscription_expiry', $order, $product);
			}
			else{
				$this->set_end_datetime(date('YmdHis', current_time('timestamp')));
				
				// ???????????? ??????
				wp_clear_scheduled_hook('cosmosfarm_members_subscription_again');
				wp_schedule_event(time(), 'hourly', 'cosmosfarm_members_subscription_again');
			}
		}
	}
	
	/**
	 * ?????? ?????? ????????? ????????? ?????? ????????????.
	 * @param string $meta_key
	 * @return string
	 */
	public function meta_value($meta_key){
		return $this->get_meta_value($meta_key);
	}
	
	/**
	 * ?????? ?????? ????????? ????????? ?????? ????????????.
	 * @param string $meta_key
	 * @return string
	 */
	public function get_meta_value($meta_key){
		$value = '';
		if($this->post_id){
			$value = get_post_meta($this->post_id, $meta_key, true);
			if(!$value){
				$meta_fields = $this->get_meta_fields();
				if(isset($meta_fields[$meta_key])){
					$value = $meta_fields[$meta_key]['default_value'];
				}
			}
		}
		return $value;
	}
	
	/**
	 * ?????? ?????? ?????? ????????? ????????????.
	 * @param array $fields
	 * @return array
	 */
	public function get_meta_fields($fields=array()){
		$fields = apply_filters('cosmosfarm_members_subscription_product_get_meta_fields', $fields, $this);
		
		if(!is_array($fields)){
			$fields = array();
		}
		
		$meta_fields = array();
		foreach($fields as $key=>$item){
			$item = array_merge(array(
				'meta_key'      => '',
				'label'         => '',
				'type'          => '',
				'default_value' => '',
				'placeholder'   => '',
				'description'   => ''
			), $item);
			
			if(!$item['meta_key'] || $key!=$item['meta_key'] || !$item['label'] || !in_array($item['type'], array('text', 'email', 'number'))){
				continue;
			}
			
			$meta_fields[$key] = $item;
		}
		return $meta_fields;
	}
	
	/**
	 * ?????? ?????? ?????? ?????? ?????? ????????????.
	 * @param array $fields
	 * @param array $data
	 */
	public function update_meta_fields($fields, $data){
		if($this->post_id){
			foreach($fields as $field){
				if($field['type'] == 'hr') continue;
				if(!$field['meta_key']) continue;
				
				if($field['type'] == 'checkbox'){
					$meta_value = array();
				}
				else{
					$meta_value = '';
				}
				
				if(isset($data[$field['meta_key']])){
					$meta_value = $data[$field['meta_key']];
				}
				
				if(is_array($meta_value)){
					if($field['type'] == 'textarea'){
						$meta_value = array_map('sanitize_textarea_field', $meta_value);
					}
					else{
						$meta_value = array_map('sanitize_text_field', $meta_value);
					}
					
					delete_post_meta($this->post_id, $field['meta_key']);
					foreach($meta_value as $value){
						add_post_meta($this->post_id, $field['meta_key'], $value);
					}
				}
				else{
					if($field['type'] == 'textarea'){
						$meta_value = sanitize_textarea_field($meta_value);
					}
					else{
						$meta_value = sanitize_text_field($meta_value);
					}
					
					update_post_meta($this->post_id, $field['meta_key'], $meta_value);
				}
				
				$this->init_with_id($this->post_id);
				
				$order = $this;
				do_action('cosmosfarm_members_order_update_field', $order, $field, $meta_value);
			}
		}
	}
}