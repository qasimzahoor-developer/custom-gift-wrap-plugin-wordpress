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
function gftw_font_page(){
	$urls_obj = new Fonts_Font();
}
//Embeded URLs Tables
class Fonts_Font extends WP_List_Table { 

	/** Class constructor */
	public function __construct() { 
		if(isset($_REQUEST['tracker'])) $this->upater();
		parent::__construct( [
			'singular' => __( 'Font', 'sp' ),
			'plural'   => __( 'Fonts', 'sp' ),
			'ajax'     => false 

		] );
		$this->plugin_fonts_page();

	} 
	public static function get_fonts( $per_page = 5, $page_number = 1 ) {
		  global $wpdb;	
		  $sql = "SELECT * FROM {$wpdb->prefix}gftw_fonts";		
		  if ( ! empty( $_REQUEST['orderby'] ) ) {
			$sql .= ' ORDER BY ' . esc_sql( $_REQUEST['orderby'] );
			$sql .= ! empty( $_REQUEST['order'] ) ? ' ' . esc_sql( $_REQUEST['order'] ) : ' ASC';
		  }		
		  $sql .= " LIMIT $per_page";		
		  $sql .= ' OFFSET ' . ( $page_number - 1 ) * $per_page; 
		  $result = $wpdb->get_results( $sql, 'ARRAY_A' );
		  return $result; 
	}
	public static function record_count() {
		  global $wpdb;
		  $sql = "SELECT COUNT(*) FROM {$wpdb->prefix}gftw_fonts";
		  return $wpdb->get_var( $sql );
	}
	public function no_items() {
	  _e( 'No Fonts avaliable.', 'sp' );
	}
	public static function delete_font( $id ) { 
		global $wpdb;
		$sql = "SELECT * FROM {$wpdb->prefix}gftw_fonts WHERE id=".$id;	
		$result = $wpdb->get_results( $sql ); 
		unlink($result[0]->file); unlink($result[0]->preview);  
		$wpdb->delete(
			"{$wpdb->prefix}gftw_fonts",
			[ 'id' => $id ],
			[ '%d' ]
		);
		$table_name = $wpdb->prefix . "gftw_fonts";
					$wpdb->update(
							$table_name, //table  
							array('font' =>0), array( 'font' => $id ) 	 		
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
			if ( ! wp_verify_nonce( $nonce, 'delete_font' ) ) { 
				die( 'Go get a life script kiddies' );
			}
			else {
				self::delete_font( absint( $_GET['font'] ) );
		                wp_redirect( esc_url_raw(admin_url('admin.php?page=gftw_manage_font')) );
				exit;
			}

		}
		if ( ( isset( $_POST['action'] ) && $_POST['action'] == 'bulk-delete' )
		     || ( isset( $_POST['action2'] ) && $_POST['action2'] == 'bulk-delete' )
		) {

			$delete_ids = esc_sql( $_POST['bulk-delete'] );
			foreach ( $delete_ids as $id ) {
				self::delete_font( $id );

			}
		        wp_redirect( esc_url_raw(admin_url('admin.php?page=gftw_manage_font')) );
			exit;
		}
	}
	public function column_default( $item, $column_name ) {  
	  switch ( $column_name ) {
		case 'name':
		case 'preview': 
		//case 'impressions':
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
	  $delete_nonce = wp_create_nonce( 'delete_font' );
	  $actions = [ 
			'delete' => sprintf( '<a href="?page=%s&action=%s&font=%s&_wpnonce=%s">Delete</a>', esc_attr( $_REQUEST['page'] ), 'delete', absint( $item['id'] ), $delete_nonce )
		];
	
	  return sprintf('%1$s %2$s', $item['name'], $this->row_actions($actions) );
	}
	function column_preview( $item ) { 
		return  '<img width="100%" src="'.$item['p_url'].'"/>';
	}
	function get_columns() {
	  $columns = [
		'cb'      => '<input type="checkbox" />',
		'id'    => __( 'ID', 'sp' ),
		'name' => __( 'Name', 'sp' ),
		'preview'    => __( 'Preview', 'sp' ) 
	  ];
	  return $columns;
	}
	public function get_sortable_columns() {
	  $sortable_columns = array( 
		'id' => array( 'id', true ),
		'name' => array( 'name', true )
	  );
	  return $sortable_columns;
	}
	public function prepare_items() {  
	  $this->_column_headers = $this->get_column_info();
	 $per_page     = $this->get_items_per_page( 'per_page', 5 );
	  $current_page = $this->get_pagenum();
	  $total_items  = self::record_count();
	  $this->set_pagination_args( [
		'total_items' => $total_items, //WE have to calculate the total number of items
		'per_page'    => $per_page //WE have to determine how many items to show on a page
	  ] );  
	  $columns = $this->get_columns();
	  $sortable = $this->get_sortable_columns();
      $this->_column_headers = array($columns, [], $sortable);
	  $this->items = self::get_fonts( $per_page, $current_page ); 
	  $this->process_bulk_action(); 
	}
	public function addfont(){
		if(isset($_POST['name']) && wp_verify_nonce( $_POST['font-add-new'], 'font-add-new')){
			global $error;
			$errors = new WP_Error();
			$name = sanitize_text_field(esc_sql($_POST['name']));
			if(empty($name)) $errors->add('name','Font Name is required.');
			if(isset($_FILES['file']) AND is_uploaded_file($_FILES['file']['tmp_name']) AND empty($errors->errors)){
					$finfo = finfo_open(FILEINFO_MIME_TYPE); 
					$tt_mime = array('font/ttf', 'application/x-font-ttf', 'font/truetype');
					$mime = finfo_file($finfo, $_FILES['file']['tmp_name']); 
					if (!in_array($mime, $tt_mime)) {
						$errors->add('mime','Only .ttf font allowed.'); 
					}else{  
						$Basedir = wp_upload_dir()['basedir'].'/giftwrap/fonts/';
						if (!file_exists($Basedir)) {
							mkdir($Basedir, 0777, true);  
						}
						$uploaded=move_uploaded_file($_FILES['file']['tmp_name'], $Basedir.$_FILES['file']['name']);
					}
					if($uploaded AND empty($errors->errors)):
						$fontPreview = $Basedir.'prv_'.$_FILES['file']['name'].'.jpg';
						$fontFile = $Basedir.$_FILES['file']['name'];
						// Create the preview image 
						$type_space = imagettfbbox(20, 0, $fontFile, $name);
						$image_width = abs($type_space[4] - $type_space[0]) + 10;
						$image_height = abs($type_space[5] - $type_space[1]) + 10;
						$im = imagecreatetruecolor($image_width, $image_height); 
						$black = imagecolorallocate($im, 0, 0, 0);
						$white = imagecolorallocate($im, 255, 255, 255);
						imagefilledrectangle($im, 0, 0, $image_width, $image_height, $white);
						$x = 5;
						$y = $image_height - 7;
						imagettftext($im, 20, 0, $x, $y, $black, $fontFile, $name);
						imagejpeg($im, $fontPreview); 	  	 				
					endif; 
			} 
			if(empty($errors->errors) AND isset($uploaded) AND $uploaded==true) //proceed if no error
			global $wpdb;	
			$p_url = wp_upload_dir()['baseurl'].'/giftwrap/fonts/'.'prv_'.$_FILES['file']['name'].'.jpg';  
			$table_name = $wpdb->prefix . "gftw_fonts";
					$wpdb->insert( 
							$table_name, //table  
							array('name' => $_POST['name'], 'file' => $fontFile, 'preview' => $fontPreview, 'p_url' => $p_url)); 
					
					if($wpdb->last_error !== ''){
						$str   = htmlspecialchars( $wpdb->last_result, ENT_QUOTES );
						$query = htmlspecialchars( $wpdb->last_query, ENT_QUOTES );
						return "<div id='error'>
						<p class='wpdberror'><strong>WordPress database error:</strong> [$str]<br />
						<code>$query</code></p>
						</div>"; 
					}else { return "<div id='success'>
						<p class=''>Font Added</p>
						</div>";  }
		}else{
			return ''; 
		}
	}
	
	public function plugin_fonts_page() {
		print $this->addfont();
		?>
		<div class="wrap">
			<h2 class="wp-heading-inline">Manage Fonts</h2><br /> 
			<a href="#TB_inline?&width=300&height=200&inlineId=addFont" class="page-title-action thickbox">Add New Font</a>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
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
		<div id="addFont" class="" style="display:none;">
        <?php  global $error; if (!empty($errors->errors)): ?><div class="error notice"><p>Something went wrong!</p>
            <ul>
				<?php
                    foreach ( $errors->get_error_messages() as $err )
                        echo "<li><strong>Error</strong> $err</li>\n";
                ?>
            </ul>
        </div><?php endif; ?>
        	<form method="post" enctype="multipart/form-data" action="<?php echo esc_html( admin_url('admin.php?page=gftw_manage_font' )); ?>">
                <div id="universal-message-container">
                <h2>Add New Font</h2>
     
                <div class="options"> 
                    <p> 
                        <label>Title</label>
                        <br />
                        <input type="text" name="name" value="" />
                        <br />
                        <input type="file" name="file" />
                    </p>
                </div>
			<?php
				wp_nonce_field( 'font-add-new', 'font-add-new' );
				submit_button();
        	?>
            </form>
        </div>
	<?php
	}
	 
}?>


