<?php
//Wp media assts
function bundles_load_media() {
 if(isset($_GET['taxonomy']) AND $_GET['taxonomy'] === 'bundles') wp_enqueue_media(); 
}
add_action( 'admin_enqueue_scripts', 'bundles_load_media');

// Let us create Taxonomy for Custom Post Products
add_action( 'init', 'product_post_type_taxonomy', 0 );

function product_post_type_taxonomy() {
 
  $labels = array(
    'name' => _x( 'Bundles', 'taxonomy general name' ),
    'singular_name' => _x( 'Bundle', 'taxonomy singular name' ),
    'search_items' =>  __( 'Search Bundle' ),
    'all_items' => __( 'All Bundles' ),
    'parent_item' => __( 'Parent Bundle' ),
    'parent_item_colon' => __( 'Parent ParentBundle:' ),
    'edit_item' => __( 'Edit Bundle' ), 
    'update_item' => __( 'Update Bundle' ),
    'add_new_item' => __( 'Add New Bundle' ),
    'new_item_name' => __( 'New Bundle Name' ),
    'menu_name' => __( 'Bundles' ),
  ); 	
 
  register_taxonomy('bundles',array('products'), array(
    'hierarchical' => true,
    'labels' => $labels,
    'show_ui' => true,
    'show_admin_column' => true,
    'query_var' => true,
    'rewrite' => array( 'slug' => 'bundle' )
  ));
}

function gftw_term_radio_checklist( $args ) {
    if ( ! empty( $args['taxonomy'] ) && $args['taxonomy'] === 'bundles' /* <== Change to your required taxonomy */ ) {
        if ( empty( $args['walker'] ) || is_a( $args['walker'], 'Walker' ) ) { // Don't override 3rd party walkers.
            if ( ! class_exists( 'WPSE_139269_Walker_Category_Radio_Checklist' ) ) {

                class GFTW_Walker_Category_Radio_Checklist extends Walker_Category_Checklist {
                    function walk( $elements, $max_depth, $args = array() ) {
                        $output = parent::walk( $elements, $max_depth, $args );
                        $output = str_replace(
                            array( 'type="checkbox"', "type='checkbox'" ),
                            array( 'type="radio"', "type='radio'" ),
                            $output
                        );

                        return $output;
                    }
                }
            }
            $args['walker'] = new GFTW_Walker_Category_Radio_Checklist;
        }
    }
    return $args;
}

add_filter( 'wp_terms_checklist_args', 'gftw_term_radio_checklist' );

function category_add_image( $term ) {
	?>
	<div class="form-field">
            <label for="bundles_image">Choose Image(required)<br /><br />
            <img src="<?php echo plugins_url('giftwrap/assts/img/no-image-small.jpg')?>" width="120" id="bundles_image_display" />
				<input id="bundles_image" type="hidden"  name="bundles_image" value="<?php echo $term_image; ?>" style="width:240px" />
				<input id="upload_image_button" type="button" value="<?php _e( 'Choose Image', 'bundles-admin-plugin' ); ?>" class="button" />
			</label>
	</div>
    <script type="text/javascript">
	// Media upload
	jQuery(document).ready(function($) {
		var custom_uploader;
		$(document).on('click', '#upload_image_button', function(e) {
			e.preventDefault();
	
			// If the uploader object has already been created, reopen the dialog
			if (custom_uploader) {
				custom_uploader.open();
				return;
			}
	
			// Extend the wp.media object
			custom_uploader = wp.media.frames.file_frame = wp.media({
				title: 'Choose Image',
				button: {
					text: 'Choose Image'
				},
				//library: {type: 'image/png'}, 
				multiple: false
			});
	
			// When a file  is selected, grab the URL and set it as the text field's value
			custom_uploader.on( 'select', function() {
				attachment = custom_uploader.state().get( 'selection' ).first().toJSON();
				$('#bundles_image_display').attr('src', attachment.url); 
				$('#bundles_image').val(attachment.url);  
			});
	
			// Open the uploader dialog
			custom_uploader.open();
	
		});
	
	});
    </script>
<?php
}
add_action( 'bundles_add_form_fields', 'category_add_image', 3 );

function category_edit_image( $term ) {
	$t_id = $term->term_id;
	$term_image = get_term_meta( $t_id, 'bundles_image', true ); 
	if($term_image=='') $term_image= plugins_url('giftwrap/assts/img/no-image-small.jpg');
	?>
	<tr class="form-field">
		<th><label for="bundles_image"><?php _e( 'Image', 'textdomain' ); ?> <small>(Required)</small></label></th>
		<td>	 
			<label for="bundles_image">
            	<img src="<?php echo $term_image; ?>" width="120" id="bundles_image_display" />
				<input id="bundles_image" type="hidden" name="bundles_image" value="<?php echo $term_image; ?>" style="width:280px" />
				<input id="upload_image_button" type="button" value="<?php _e( 'Choose Image', 'bundles-admin-plugin' ); ?>" class="button" />
			</label>
            <script type="text/javascript">
			// Media upload
			jQuery(document).ready(function($) {
				var custom_uploader;
				$(document).on('click', '#upload_image_button', function(e) {
					e.preventDefault();
			
					// If the uploader object has already been created, reopen the dialog
					if (custom_uploader) {
						custom_uploader.open();
						return;
					}
			
					// Extend the wp.media object
					custom_uploader = wp.media.frames.file_frame = wp.media({
						title: 'Choose Image',
						button: {
							text: 'Choose Image'
						},
						//library: {type: 'image/png'}, 
						multiple: false
					});
			
					// When a file  is selected, grab the URL and set it as the text field's value
					custom_uploader.on( 'select', function() {
						attachment = custom_uploader.state().get( 'selection' ).first().toJSON();
						$('#bundles_image_display').attr('src', attachment.url); 
						$('#bundles_image').val(attachment.url);  
					});
			
					// Open the uploader dialog
					custom_uploader.open();
			
				});
			
			});
			</script>
		</td>
	</tr>
<?php
}
add_action( 'bundles_edit_form_fields', 'category_edit_image', 3 );

function category_save_image( $term_id ) {
	
	if ( isset( $_POST['bundles_image'] ) ) {
		$term_image = $_POST['bundles_image'];
		if( $term_image ) {
			 update_term_meta( $term_id, 'bundles_image', $term_image );
		}
	} 
		
}  
add_action( 'edited_bundles', 'category_save_image' );  
add_action( 'create_bundles', 'category_save_image' );
 
add_filter('manage_edit-bundles_columns', 'image_columns');
function image_columns($columns) {
    $columns['bundles_image'] = 'Image';
    return $columns;
}
function add_bundles_column_content($content,$column_name,$term_id){
    $term_image = get_term_meta( $term_id, 'bundles_image', true );
	if($term_image=='') $term_image= plugins_url('giftwrap/assts/img/no-image-small.jpg');
    switch ($column_name) {
        case 'bundles_image':
            $content = '<img src="'.$term_image.'" width="100%" alt="Thumbnail" />';
            break;
        default:
            break;
    }
    return $content;
}
add_filter('manage_bundles_custom_column', 'add_bundles_column_content',10,3);

function thumbnail_column($columns) {
  $new = array();
  foreach($columns as $key => $title) {
    if ($key=='name') // Put the Thumbnail column before the Author column
      $new['bundles_image'] = 'Image';
    $new[$key] = $title;
  }
  return $new;
}
add_filter('manage_edit-bundles_columns', 'thumbnail_column');

//Assign Template for texonomy
function bundles_set_template( $template ){ 
	if( is_tax('bundles') && !bundles_is_template($template))
        $template = ABSPATH .('wp-content/plugins/giftwrap/templates/taxonomy-bundles.php');
    	return $template;
}
function bundles_is_template( $template_path ){
    $template = basename($template_path); 
    if( 1 == preg_match('/^taxonomy-bundles((-(\S*))?).php/',$template) )
         return true;
    return false;
}
add_filter('template_include', 'bundles_set_template');



/*
function sortable_bundles_column( $columns ) {
    $columns['image'] = 'image';
    return $columns;
}
add_filter( 'manage_edit-bundles_sortable_columns', 'sortable_bundles_column' );*/
/**
 * Callback runs when category is updated
 * Will save user-provided input into the wp_termmeta DB table
 */
/**
 * Display markup or template for custom field

function gwp_quick_edit_category_field( $column_name, $screen ) {
    // If we're not iterating over our custom column, then skip
    if ( $screen != 'edition' && $column_name != 'first-appeared' ) {
       // return false;
    }
    ?>
    <fieldset>
        <div id="gwp-first-appeared" class="inline-edit-col">
            <label>
                <span class="title"><?php _e( 'First Appeared', 'generatewp' ); echo '---'.$screen ?></span>
                <span class="input-text-wrap"><input type="text" name="<?php echo esc_attr( $column_name ); ?>" class="ptitle" value=""></span>
            </label>
        </div>
    </fieldset>
    <?php
}
add_action( 'quick_edit_custom_box', 'gwp_quick_edit_category_field', 10, 2 ); */