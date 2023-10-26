<?php 
/** 
 * Settings Class.
 **/
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} 

/** WordPress Administration Bootstrap */
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

//Embeded URLs Tables
class Orders_List extends WP_List_Table { 

	/** Class constructor */
	public function __construct() { 
		if(isset($_REQUEST['tracker'])) $this->upater();
		parent::__construct( [
			'singular' => __( 'Order', 'sp' ),
			'plural'   => __( 'Orders', 'sp' ),
			'ajax'     => false 

		] );   
		if(isset($_GET['action']) AND $_GET['action'] == 'view'){ $this->view_order($_GET['order']); }
		elseif(isset($_GET['action']) AND $_GET['action'] == 'approve'){ $this->approve_order($_GET['order']); }
		elseif(isset($_GET['action']) AND $_GET['action'] == 'reject'){ $this->reject_order($_GET['order']);  }
		else{ $this->plugin_orders_page(); } 
	} 

	public static function get_orders( $per_page = 50, $page_number = 1 ) {
		  global $wpdb;	
		  $sql = "SELECT * FROM {$wpdb->prefix}gftw_orders";		
		  if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		  }	else{
			  $sql .= ' ORDER BY id DESC';
		  }
		  $sql .= " LIMIT $per_page";		
		  $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page; 
		  $result = $wpdb->get_results( $sql, 'ARRAY_A' );
		  return $result; 
	}
	public static function record_count() {
		  global $wpdb;
		  $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}gftw_orders";
		  return $wpdb->get_var( $sql );
	}
	public function no_items() {
	  _e( 'No Orders avaliable.', 'sp' );
	}
	public static function delete_order( $id ) { 
		global $wpdb;
		$wpdb->delete(
			"{$wpdb->prefix}gftw_orders",
			[ 'id' => $id ],
			[ '%d' ]
		); 
	}

	public function get_bulk_actions() {
		$actions = [
			'bulk-delete' => 'Delete'
		];

		return $actions;
		
	}
	public function process_bulk_action() { 
		if ( 'delete' === $this->current_action() ) { 
			$nonce = esc_attr( $_REQUEST['_wpnonce'] );
			if ( ! wp_verify_nonce( $nonce, 'delete_order' ) ) { 
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_order( absint( $_GET['order'] ) );
		                wp_redirect( esc_url_raw(admin_url('admin.php?page=orders')) );
				exit;
			}

		}
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );
			foreach ( $delete_ids as $id ) {
				self::delete_order( $id );

			}
		        wp_redirect( esc_url_raw(admin_url('admin.php?page=orders')) );
			exit;
		}
	}
	public function column_default( $item, $column_name ) {  
	  switch ( $column_name ) {
		case 'order_no':
		case 'first_name': 
		case 'last_name': 
		case 'first_name': 
		case 'phone': 
		case 'email': 
		case 'payment_status': 
		case 'order_status': 
		case 'impressions':
		case 'action':
		case 'created':
		case 'id':
		  return $item[ $column_name ];
		default:
		  return print_r( $item, true ); //Show the whole array for troubleshooting purposes
	  }
	}
	function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="bulk-delete[]" value="%s" />', $item['id']
		);
	}
	function column_name($item) {
	  return $item['first_name'].' '.$item['last_name'] ;
	}
	function column_created($item) {
	  return $item['created'];
	}
	function column_order_no( $item ) { 
		$delete_nonce = wp_create_nonce( 'delete_order' );
	  $actions = [ 
			'view' => sprintf( '<a href="?page=%s&action=%s&order=%s&_wpnonce=%s">View</a>', esc_attr( $_REQUEST['page'] ), 'view', absint( $item['id'] ), $delete_nonce ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&order=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
		];
	 
	  return sprintf('%1$s %2$s', $item['order_no'], $this->row_actions($actions) );
	}
	function get_columns() { 
	  $columns = [
		'cb'      => '<input type="checkbox" />',
		'id'    => __( 'ID', 'sp' ),
		'order_no' => __( 'Order No', 'sp' ),
		'name'    => __( 'Name', 'sp' ),
		'phone'    => __( 'Phone', 'sp' ),
		'email'=> __( 'Email', 'sp' ), 
		'payment_status'=> __( 'Payment Status', 'sp' ),
		'order_status'=> __( 'Order Status', 'sp' ),
		'created'=> __( 'Date', 'sp' ),
		'id'    => __( 'ID', 'sp' )
	  ];
	  return $columns;
	}
	public function get_sortable_columns() {
	  $sortable_columns = array( 
		'id' => array( 'id', true ),
		'order_no' => array( 'order_no', true ),
		'payment_status' => array( 'payment_status', true ),
		'order_status' => array( 'order_status', true ),
		'created' => array( 'created', true )
	  );
	  return $sortable_columns;
	}
	public function prepare_items() {  
	  $this->_column_headers = $this->get_column_info();
	  $per_page     = $this->get_items_per_page( 'per_page', 100 );
	  $current_page = $this->get_pagenum();
	  $total_items  = self::record_count();
	  $this->set_pagination_args( [
		'total_items' => $total_items, //WE have to calculate the total number of items
		'per_page'    => $per_page //WE have to determine how many items to show on a page
	  ] );  
	  $columns = $this->get_columns();
	  $sortable = $this->get_sortable_columns();
      $this->_column_headers = array($columns, [], $sortable);
	  $this->items = self::get_orders( $per_page, $current_page ); 
	  $this->process_bulk_action(); 
	}
	public function plugin_orders_page() {
		print $this->addorder();
		?>
		<div class="wrap">
        <?php if(!empty($alert['type'])):
			    if($alert['type'] === 'error'): ?>
                <div class="error notice"><br>
                  <strong>Error!</strong> <?php echo $alert['msg']; ?><br>
                </div> 
		   <?php else:?>
                <div class="updated notice"><br>
                  <strong>Success!</strong> <?php echo $alert['msg']; ?><br>
                </div>
         <?php endif;
		endif; ?>
			<h2 class="wp-heading-inline">Manage Orders</h2> 
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-1">
					<div id="post-body-content">
						<div class="meta-box-sortables ui-sortable">
							<form method="post">
								<?php
								$this->prepare_items();
								$this->display(); ?>
							</form>
						</div>
					</div>
				</div>
				<br class="clear"> 
			</div>
		</div>
        <?php add_thickbox(); ?>
		<div id="addOrder" class="" style="display:none;">
        <?php  global $error; if (!empty($errors->errors)): ?><div class="error notice"><p>Something went wrong!</p>
            <ul>
				<?php
                    foreach ( $errors->get_error_messages() as $err )
                        echo "<li><strong>Error</strong> $err</li>\n";
                ?>
            </ul>
        </div><?php endif; ?>
        	
        </div>
	<?php
	}
	//View Order  
	public  function view_order( $id ) { 
		global $wpdb;
		$sql = "SELECT  * FROM ".$wpdb->prefix."gftw_orders WHERE id=".$id;
		$order = $wpdb->get_results( $sql )[0];
		if(is_null($order) OR empty($order)) { wp_redirect( esc_url_raw(admin_url('admin.php?page=orders')) ); }
		$payment = $this->__extract_payment($order->payment_meta);
		$cart = json_decode($order->cart_meta);
		$uploads = wp_upload_dir();
		require_once('order-details.php');  
		return ;
	}
	public  function approve_order( $id ) { 
		global $wpdb;
		$sql = "UPDATE ".$wpdb->prefix."gftw_orders SET order_status='Approved' WHERE id=".$id;
		$wpdb->query( $sql );
		if($wpdb->last_error !== ''){
			setAlert(['type'=>'error', 'msg'=>'something is wrong']); 
			wp_redirect(admin_url('admin.php?page=orders&action=view&order='.$id.'&_wpnonce='.$_GET['_wpnonce']));
			exit; 
		}else{
			setAlert(['type'=>'sucess', 'msg'=>'order updated']); 
			wp_redirect(admin_url('admin.php?page=orders&action=view&order='.$id.'&_wpnonce='.$_GET['_wpnonce']));
			$this->__send_approved_email($id); 
			exit;
		}
		exit;  
	} 
	public  function reject_order( $id ) { 
		global $wpdb;
		$data = wc_clean($_POST); 
		$sql = "UPDATE ".$wpdb->prefix."gftw_orders SET order_status='Rejected', comments='".$data['order_reject_comments']."' WHERE id=".$id;
		$wpdb->query( $sql );    
		if($wpdb->last_error !== ''){ 
			setAlert(['type'=>'error', 'msg'=>'something is wrong'.$wpdb->last_error]); 
			wp_redirect(admin_url('admin.php?page=orders&action=view&order='.$id.'&_wpnonce='.$_GET['_wpnonce']));
			exit; 
		}else{
			setAlert(['type'=>'sucess', 'msg'=>'order updated']);     
			wp_redirect(admin_url('admin.php?page=orders&action=view&order='.$id.'&_wpnonce='.$_GET['_wpnonce']));  
			$this->__send_rejected_email($id);   
			exit;
		}
		exit;  
	}
	
	private function __send_approved_email($id){ 
	global $wpdb;$arrVars = array();
	$table_name = $wpdb->prefix . "gftw_orders"; 
	$Order = $wpdb->get_results('SELECT * FROM '.$table_name.' WHERE `id`="'.$id.'";');
	$payment =  $this->__extract_payment($Order[0]->payment_meta);
	$cart = json_decode($Order[0]->cart_meta);
	$uploads = wp_upload_dir();
	$template = nl2br(get_option('gftw_order_approve'));
	$arrVars['{{first_name}}'] =   $Order[0]->first_name; 
	$arrVars['{{last_name}}'] =   $Order[0]->last_name; 
	$arrVars['{{site_name}}'] =   esc_attr( get_option('gftw_shop_name')); 
	$arrVars['{{shipping_detail}}'] ='
	<h4>Shipping Address</h4><br>
	Name: '.$Order[0]->first_name . ' ' .$Order[0]->last_name.'<br/>
	Email: '.$Order[0]->email.'<br/>
	Phone: '.$Order[0]->phone.'<br/>
	Address: <br/>'.$Order[0]->address.'<br/> '.$Order[0]->city.'<br/> '.$Order[0]->zipcode.'<br/>'.$Order[0]->state.'<br/> USA
	
	'; 
	
	$order_items = '<table class="wp-list-table widefat fixed striped striped">
    <tr><td>Image</td><td>ID</td><td>Name</td><td>Price</td><td>Qty.</td><td>Sub Total</td></tr>';
	$subtotal = 0; 
	foreach($cart as $cItem):
		$subtotal+=$cItem->price*$cItem->qty;
		$order_items .='<td><img width="150" src="'.$uploads['baseurl'].'/giftwrap/orders/order_'.$Order[0]->order_no.'/'.basename($cItem->img).'" /></td>';
		$order_items .='<td>'.$cItem->id.'</td>';
		$order_items .='<td><strong>'.$cItem->name.'</strong><br />';
		foreach($cItem->options as $oKey=>$oVal):
			$order_items .='<small>'.$oKey.': <i>'.$oVal.'</i> </small><br/>';
		endforeach;	
		if(isset($cItem->printtext) AND $cItem->printtext[0]->text !==null){ $i=1; foreach($cItem->printtext as $print):
			$text = trim($print->text);
			if(!empty($text)){
				$order_items .='<small>Text '.$i.': <i>'.$text.'</i> </small><br/>';
                $order_items .='<small>Color '.$i.': <i>'.$print->css->fontColor.'</i> </small><br/>';
                $order_items .='<small>Font '.$i.': <i>'.$print->css->fontfamily.'</i> </small><br/>';  
		} $i++; endforeach; }
		$order_items .='</td>';
        $order_items .='<td>'.$cItem->price.'</td>';
        $order_items .='<td>'.$cItem->qty.'</td>';
        $order_items .='<td>'.$cItem->price*$cItem->qty.'</td></tr>';
	endforeach;
	$order_items .='</table><br>';
	$order_items .='<h4>Order Sub Total: '.$subtotal.' &#36; </h4>';
	$order_items .='<h4>Order Shipping: '.$cItem->shipping.' &#36; </h4>';
	$order_items .='<h4>Order Grand Total: '.($subtotal+$cItem->shipping).' &#36; </h4>';
	$arrVars['{{order_items}}'] = $order_items;
	$email_body = strtr($template , $arrVars);
	$to = $Order[0]->email; 
	$subject = 'Your order #'.$Order[0]->order_no.' Approved by '. esc_attr( get_option('gftw_shop_name'));
	$body = $email_body;
	$headers[] = 'From: '.esc_attr( get_option('gftw_shop_name')).' <'.esc_attr( get_option('gftw_shop_email')).'>';
	$headers[] = 'Bcc: '.esc_attr( get_option('gftw_shop_name')).' <'.esc_attr( get_option('gftw_shop_email')).'>';
	$headers[] = 'Content-Type: text/html; charset=UTF-8'; 
	wp_mail( $to, $subject, $body, $headers );   
 
 }
 private function __send_rejected_email($id){ 
	global $wpdb;$arrVars = array();    
	$table_name = $wpdb->prefix . "gftw_orders"; 
	$Order = $wpdb->get_results('SELECT * FROM '.$table_name.' WHERE `id`="'.$id.'";');
	$payment =  $this->__extract_payment($Order[0]->payment_meta);
	$cart = json_decode($Order[0]->cart_meta);
	$uploads = wp_upload_dir();
	$template = nl2br(get_option('gftw_order_approve'));
	$arrVars['{{order_reject_comments}}'] =   nl2br($Order[0]->comments); 
	$arrVars['{{first_name}}'] =   $Order[0]->first_name; 
	$arrVars['{{last_name}}'] =   $Order[0]->last_name; 
	$arrVars['{{site_name}}'] =   esc_attr( get_option('gftw_shop_name')); 
	$arrVars['{{shipping_detail}}'] ='
	<h4>Shipping Address</h4><br>
	Name: '.$Order[0]->first_name . ' ' .$Order[0]->last_name.'<br/>
	Email: '.$Order[0]->email.'<br/>
	Phone: '.$Order[0]->phone.'<br/>
	Address: <br/>'.$Order[0]->address.'<br/> '.$Order[0]->city.'<br/> '.$Order[0]->zipcode.'<br/>'.$Order[0]->state.'<br/> USA
	
	'; 
	
	$order_items = '<table class="wp-list-table widefat fixed striped striped">
    <tr><td>Image</td><td>ID</td><td>Name</td><td>Price</td><td>Qty.</td><td>Sub Total</td></tr>';
	$subtotal = 0; 
	foreach($cart as $cItem):
		$subtotal+=$cItem->price*$cItem->qty;
		$order_items .='<td><img width="150" src="'.$uploads['baseurl'].'/giftwrap/orders/order_'.$Order[0]->order_no.'/'.basename($cItem->img).'" /></td>';
		$order_items .='<td>'.$cItem->id.'</td>';
		$order_items .='<td><strong>'.$cItem->name.'</strong><br />';
		foreach($cItem->options as $oKey=>$oVal):
			$order_items .='<small>'.$oKey.': <i>'.$oVal.'</i> </small><br/>';
		endforeach;	
		if(isset($cItem->printtext) AND $cItem->printtext[0]->text !==null){ $i=1; foreach($cItem->printtext as $print):
			$text = trim($print->text);
			if(!empty($text)){
				$order_items .='<small>Text '.$i.': <i>'.$text.'</i> </small><br/>';
                $order_items .='<small>Color '.$i.': <i>'.$print->css->fontColor.'</i> </small><br/>';
                $order_items .='<small>Font '.$i.': <i>'.$print->css->fontfamily.'</i> </small><br/>';  
		} $i++; endforeach; }
		$order_items .='</td>';
        $order_items .='<td>'.$cItem->price.'</td>';
        $order_items .='<td>'.$cItem->qty.'</td>';
        $order_items .='<td>'.$cItem->price*$cItem->qty.'</td></tr>';
	endforeach;
	$order_items .='</table><br>';
	$order_items .='<h4>Order Sub Total: '.$subtotal.' &#36; </h4>';
	$order_items .='<h4>Order Shipping: '.$cItem->shipping.' &#36; </h4>';
	$order_items .='<h4>Order Grand Total: '.($subtotal+$cItem->shipping).' &#36; </h4>';
	$arrVars['{{order_items}}'] = $order_items;
	$email_body = strtr($template , $arrVars);
	$to = $Order[0]->email;
	$subject = 'Your order #'.$Order[0]->order_no.' Rejected by '. esc_attr( get_option('gftw_shop_name'));
	$body = $email_body;
	$headers[] = 'From: '.esc_attr( get_option('gftw_shop_name')).' <'.esc_attr( get_option('gftw_shop_email')).'>';
	$headers[] = 'Bcc: '.esc_attr( get_option('gftw_shop_name')).' <'.esc_attr( get_option('gftw_shop_email')).'>';
	$headers[] = 'Content-Type: text/html; charset=UTF-8'; 
	wp_mail( $to, $subject, $body, $headers );
 
 }
	
	private function __extract_payment($var){
		$var = urldecode($var); 
		parse_str($var, $arr);
		return $arr;
	}

}?>






