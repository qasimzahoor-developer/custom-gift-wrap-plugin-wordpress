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
class Products_List extends WP_List_Table { 

	/** Class constructor */
	public function __construct() { 
		if(isset($_REQUEST['tracker'])) $this->upater();
		parent::__construct( [
			'singular' => __( 'Product', 'sp' ),
			'plural'   => __( 'Products', 'sp' ),
			'ajax'     => false 

		] );   
		if(isset($_GET['action']) AND $_GET['action'] == 'view'){ $this->view_product($_GET['product']); }
		elseif(isset($_GET['action']) AND $_GET['action'] == 'approve'){ $this->approve_product($_GET['product']); }
		elseif(isset($_GET['action']) AND $_GET['action'] == 'reject'){ $this->reject_product($_GET['product']);  }
		else{ $this->plugin_products_page(); } 
	} 

	public static function get_products( $per_page = 50, $page_number = 1 ) {
		  global $wpdb;	
		  $sql = "SELECT * FROM {$wpdb->prefix}gftw_products";		
		  if ( ! empty( $_REQUEST['productby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['productby'] );
			$sql .= ! empty( $_REQUEST['product'] ) ? ' ' . esc_sql( $_REQUEST['product'] ) : ' ASC';
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
		  $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}gftw_products";
		  return $wpdb->get_var( $sql );
	}
	public function no_items() {
	  _e( 'No Products avaliable.', 'sp' );
	}
	public static function delete_product( $id ) { 
		global $wpdb;
		$wpdb->delete(
			"{$wpdb->prefix}gftw_products",
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
			if ( ! wp_verify_nonce( $nonce, 'delete_product' ) ) { 
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_product( absint( $_GET['product'] ) );
		                wp_redirect( esc_url_raw(admin_url('admin.php?page=products')) );
				exit;
			}

		}
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );
			foreach ( $delete_ids as $id ) {
				self::delete_product( $id );

			}
		        wp_redirect( esc_url_raw(admin_url('admin.php?page=products')) );
			exit;
		}
	}
	public function column_default( $item, $column_name ) {  
	  switch ( $column_name ) {
		case 'product_no':
		case 'first_name': 
		case 'last_name': 
		case 'first_name': 
		case 'phone': 
		case 'email': 
		case 'payment_status': 
		case 'product_status': 
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
	function column_product_no( $item ) { 
		$delete_nonce = wp_create_nonce( 'delete_product' );
	  $actions = [ 
			'view' => sprintf( '<a href="?page=%s&action=%s&product=%s&_wpnonce=%s">View</a>', esc_attr( $_REQUEST['page'] ), 'view', absint( $item['id'] ), $delete_nonce ),
			'delete' => sprintf( '<a href="?page=%s&action=%s&product=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
		];
	 
	  return sprintf('%1$s %2$s', $item['product_no'], $this->row_actions($actions) );
	}
	function get_columns() { 
	  $columns = [
		'cb'      => '<input type="checkbox" />',
		'id'    => __( 'ID', 'sp' ),
		'product_no' => __( 'Product No', 'sp' ),
		'name'    => __( 'Name', 'sp' ),
		'phone'    => __( 'Phone', 'sp' ),
		'email'=> __( 'Email', 'sp' ), 
		'payment_status'=> __( 'Payment Status', 'sp' ),
		'product_status'=> __( 'Product Status', 'sp' ),
		'created'=> __( 'Date', 'sp' ),
		'id'    => __( 'ID', 'sp' )
	  ];
	  return $columns;
	}
	public function get_sortable_columns() {
	  $sortable_columns = array( 
		'id' => array( 'id', true ),
		'product_no' => array( 'product_no', true ),
		'payment_status' => array( 'payment_status', true ),
		'product_status' => array( 'product_status', true ),
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
	  $this->items = self::get_products( $per_page, $current_page ); 
	  $this->process_bulk_action(); 
	}
	public function plugin_products_page() {
		print $this->addproduct();
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
			<h2 class="wp-heading-inline">Sync Products</h2> 
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
		<div id="addProduct" class="" style="display:none;">
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
	//View Product  
	public  function view_product( $id ) { 
		global $wpdb;
		/*$sql = "SELECT  * FROM ".$wpdb->prefix."gftw_products WHERE id=".$id;
		$product = $wpdb->get_results( $sql )[0];
		if(is_null($product) OR empty($product)) { wp_redirect( esc_url_raw(admin_url('admin.php?page=products')) ); }
		$payment = $this->__extract_payment($product->payment_meta);
		$cart = json_decode($product->cart_meta);
		$uploads = wp_upload_dir();
		require_once('product-details.php'); */ 
		return ;
	}

}?>






