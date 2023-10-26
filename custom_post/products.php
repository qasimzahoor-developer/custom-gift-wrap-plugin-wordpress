<?php

function products_load_media() {
 if(isset($_GET['post_type']) AND $_GET['post_type'] === 'products') wp_enqueue_media(); 
}
add_action( 'admin_enqueue_scripts', 'products_load_media');

 /*
* Creating a function to create our Product
*/
 
function product_post_type() { 
 
// Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Products', 'Post Type General Name', 'twentythirteen' ),
        'singular_name'       => _x( 'Product', 'Post Type Singular Name', 'twentythirteen' ),
        'menu_name'           => __( 'Products', 'twentythirteen' ),
        'parent_item_colon'   => __( 'Parent Product', 'twentythirteen' ),
        'all_items'           => __( 'All Product', 'twentythirteen' ),
        'view_item'           => __( 'View Product', 'twentythirteen' ),
        'add_new_item'        => __( 'Add New Product', 'twentythirteen' ),
        'add_new'             => __( 'Add New', 'twentythirteen' ),
        'edit_item'           => __( 'Edit Product', 'twentythirteen' ),
        'update_item'         => __( 'Update Product', 'twentythirteen' ),
        'search_items'        => __( 'Search Product', 'twentythirteen' ),
        'not_found'           => __( 'Not Found', 'twentythirteen' ),
        'not_found_in_trash'  => __( 'Not found in Trash', 'twentythirteen' ),
    );
     
// Set other options for Custom Post Type
     
    $args = array(
        'label'               => __( 'Products', 'twentythirteen' ),
        'description'         => __( 'Product Description', 'twentythirteen' ),
        'labels'              => $labels,
        'supports'            => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'revisions'),
        'taxonomies'          => array( 'bundles'),  
        /* A hierarchical CPT is like Pages and can have 
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */ 
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_position'       => 2,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'page',  
    );
     
    // Registering your Custom Post Type
    register_post_type( 'products', $args );
 
} 
 
/* Hook into the 'init' action so that the function
* Containing our post type registration is not 
* unnecessarily executed. 
*/
 
add_action( 'init', 'product_post_type', 0 );



//////////////////////////////
///// Form Customizatins ////
////////////////////////////

//excerpt label change
/*function wpartisan_excerpt_label( $translation, $original ) {
    if ( 'Excerpt' == $original ) {
        return __( 'Short Description' );
    } elseif ( false !== strpos( $original, 'Excerpts are optional hand-crafted summaries of your' ) ) {
        return __( 'Short Description' );
    }
    return $translation; 
}
add_filter( 'gettext', 'wpartisan_excerpt_label', 10, 2 );*/

///
// Add Designer Meta Box
function add_designer_meta_box() {
    add_meta_box(
        'designer_meta_box', // $id
        'Product Designer', // $title 
        'show_designer_meta_box', // $callback
        'products', // $page
        'normal', // $context
        'high'); // $priority
}
function show_designer_meta_box() {
	global $post; global $wpdb;
	 $d_text1 = json_decode(str_replace('\\"', '"', get_post_meta( $post->ID, 'giftwrap_designer_text1', true )));
	 $d_text2 = json_decode(str_replace('\\"', '"', get_post_meta( $post->ID, 'giftwrap_designer_text2', true )));
	 $d_image = json_decode(str_replace('\\"', '"', get_post_meta( $post->ID, 'giftwrap_designer_image', true )));
	 $d_image_url = wp_get_attachment_image_url($d_image->attachement, 'giftwrap-product300');  
	 //Post Product Image
	 $p_image_id =  get_post_thumbnail_id($post->ID);
	 $p_image_url = wp_get_attachment_image_url($p_image_id, 'giftwrap-product500');
		// fonts
		$fonts = $wpdb->get_results("select * from ".$wpdb->prefix."gftw_fonts;", OBJECT);
	 ?>  
     <div class="main">
     	<div class="imageContiner gftw-container">
        <img src="<?php echo (isset($p_image_url) AND !empty($p_image_url))? $p_image_url : plugins_url('giftwrap/assts/img/no-image-wide.jpg') ?>" id="imageMainP" />
        <div class="imgDesigner gftw-designer"> </div><!--imgDesigner-->     	
        </div>  
      <script type="text/javascript">
jQuery(document).ready(function(){ 

		 $( "#dBtn1" ).click(function() {
			 var dText = $( "#dText1" ).val() 
			 if(dText === ''){ alert('"Print Text 1" is Required'); return }
									$( ".gftw-designer" ).txtPrinter({ name: 'designer_text1', text:dText, containment:'.gftw-container', 
										required:$( "#dRequired1:checked" ).val(), css:{fontfamily:$( "#dFamily1" ).val(), 
										fontColor:$( "#dColor1" ).val(), fontSize:22, fontStyle:$( "#dStyle1" ).val(), 
										left:100, top:40} });
			 });
		
		$( "#dBtn2" ).click(function() {
			 var dText = $( "#dText2" ).val() 
			 if(dText === ''){ alert('"Print Text 2" is Required'); return }
									$( ".gftw-designer" ).txtPrinter({ name: 'designer_text2', text:dText, containment:'.gftw-container', 
										required:$( "#dRequired2:checked" ).val(), css:{fontfamily:$( "#dFamily2" ).val(), 
										fontColor:$( "#dColor2" ).val(), fontSize:22, fontStyle:$( "#dStyle2" ).val(), 
										left:200, top:40} });
			 });
			 
	
	//Uploader 
	var custom_uploader;
	//Upload Select Products Image
	$( '#dImage' ).click(function(e) {
		e.preventDefault();
		if (custom_uploader) {
			custom_uploader.open();
			return;
		}
		custom_uploader = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
			library: {type: 'image'}, 
			multiple: false
		});
		custom_uploader.on( 'select', function() {
			attachment = custom_uploader.state().get( 'selection' ).first().toJSON();
			$('#imageMainP').attr('src', attachment.url);
			$( 'input[name=product_image]' ).val(attachment.id);
		});
		custom_uploader.open();
	});
	var custom_uploader2;
	//Upload Select Print Image
	$( '#dPrint' ).click(function(e) {
		e.preventDefault();
		if (custom_uploader2) {
			custom_uploader2.open();
			return;
		}
		custom_uploader2 = wp.media.frames.file_frame = wp.media({
			title: 'Choose Image',
			button: {
				text: 'Choose Image'
			},
			library: {type: 'image'}, 
			multiple: false
		});
		custom_uploader2.on( 'select', function() {
			attachment = custom_uploader2.state().get( 'selection' ).first().toJSON(); 
			$('.gftw-designer').imgPrinter({ name: 'designer_image', url:attachment.url, containment:'.gftw-container', attachement:attachment.id, css:{width:100, left:100, top:30} });  
			$( 'input[name=print_image]' ).val(attachment.id);  
		});
		custom_uploader2.open(); 
	}); 
 	
	//Add Designer elem om load
	<?php if(isset($d_text1->text) AND !empty($d_text1->text)): ?>
	$( ".imgDesigner" ).txtPrinter({ name: 'designer_text1', text:"<?php echo $d_text1->text; ?>", containment:'.imgDesigner', 
										required:<?php echo $d_text1->required; ?>, css:{fontfamily:"<?php echo $d_text1->css->fontfamily; ?>", 
										fontColor:"<?php echo $d_text1->css->fontColor; ?>", fontSize:<?php echo $d_text1->css->fontSize; ?>, fontStyle:"<?php echo $d_text1->css->fontStyle; ?>", left:<?php echo $d_text1->css->left; ?>, top:<?php echo $d_text1->css->top; ?>} });
	<?php endif; ?>	
	<?php if(isset($d_text2->text) AND !empty($d_text2->text)): ?>
	$( ".imgDesigner" ).txtPrinter({ name: 'designer_text2', text:"<?php echo $d_text2->text; ?>", containment:'.imgDesigner', 
										required:<?php echo $d_text2->required; ?>, css:{fontfamily:"<?php echo $d_text2->css->fontfamily; ?>", 
										fontColor:"<?php echo $d_text2->css->fontColor; ?>", fontSize:<?php echo $d_text2->css->fontSize; ?>, fontStyle:"<?php echo $d_text2->css->fontStyle; ?>", left:<?php echo $d_text2->css->left; ?>, top:<?php echo $d_text2->css->top; ?>} });
	<?php endif; ?>	
	<?php if(isset($d_image) AND !empty($d_image) AND $d_image_url): ?>
	$('.imgDesigner').imgPrinter({ name: 'designer_image', url:'<?php echo $d_image_url ?>', containment:'.imgDesigner', attachement:<?php echo $d_image->attachement ;?>, css:{width:<?php echo $d_image->css->width ;?>, left:<?php echo $d_image->css->left ;?>, top:<?php echo $d_image->css->top ;?>} });  
	<?php endif; ?>								
	
		 
			 
});     
     </script>
        <div class="imgTools">
        	<p>
            <input type="button" class="button button-primary button-large" id="dImage" value="Product Image"><br />
            Select / change the default product image.
            <input type="hidden" name="product_image" value="<?php echo $p_image_id; ?>" /> 
            </p>
            <p>
            <input type="button" class="button button-primary button-large" id="dPrint" value="Print Image"><br />
            Select / change default print image for product.
            <input type="hidden" name="print_image" value="" />
            </p>
            <p>
            <input type="text"  id="dText1" placeholder="Print Text 1" value="<?php echo $d_text1->text; ?>"><br />
            <input  type="text" class="dcolor" id="dColor1" name="text-color" value="#000000">  <br />
                  <select class="custom-select nice-select" id="dFamily1">
                    <?php foreach($fonts as $font){ ?>
                    	<option value="<?php echo $font->name ?>" data-file="<?php echo $font->file_url ?>" data-display="<?php echo $font->name ?>" data-image="<?php echo $font->p_url ?>" <?php echo ($d_text1->css->fontfamily == $font->name)? 'selected=selected' : '' ?>><?php echo $font->name ?></option> 
                    <?php } ?>
            </select> <br />
            	<select class="custom-select" id="dStyle1" style="display:none;">
                    <option>Font Style</option>
                    <option value="italic"><i>Italic</i></option>
                    <option value="normal" selected>Normal</option>
            </select><br />
            <label> <input type="checkbox" id="dRequired1" value="1" <?php if(isset($d_text1->required)) checked($d_text1->required, 1 ); ?>/> Required</label>
            </p>
            <p><input type="button" class="button button-primary button-large" id="dBtn1" value="Update Text 1"></p>
            <p>
            <input type="text"  id="dText2" placeholder="Print Text 2" value="<?php echo $d_text2->text; ?>"><br />
            <input  type="text" class="dcolor" id="dColor2" name="text-color" value="#000000"> <br />
                  <select class="custom-select nice-select" id="dFamily2" name="text-style">
                    <?php foreach($fonts as $font){ ?>
                    	<option value="<?php echo $font->name ?>" data-file="<?php echo $font->file_url ?>" data-display="<?php echo $font->name ?>" data-image="<?php echo $font->p_url ?>" <?php echo ($d_text2->css->fontfamily == $font->name)? 'selected=selected' : '' ?>><?php echo $font->name ?></option> 
                    <?php } ?> 
            </select> <br />
            	<select class="custom-select" id="dStyle2" name="text-style" style="display:none;">
                    <option>Font Style</option>
                    <option value="italic"><i>Italic</i></option>  
                    <option value="normal" selected>Normal</option>
            </select><br />
            <label> <input type="checkbox" id="dRequired2" value="1"  <?php if(isset($d_text2->required)) checked($d_text2->required, 1 ); ?>/> Required</label>
            </p>
            <p><input type="button" class="button button-primary button-large" id="dBtn2" value="Update Text 2"></p>
            
            <p>
            This section is required to design properly, Print image position and Text size, font, color, positions are used as default for this product upon genrating auto preview of bundle. 
            </p>
        </div>   
     </div><!--main-->
     <script>
				jQuery( document ).ready(function() {
					//Font
					$('.nice-select').niceSelect();
					$('.nice-select').change(function(){ $('head').append('<style>@font-face { font-family: "'+$(this).find(':selected').data('display')+'"; src:url('+$(this).find(':selected').data('file')+') format("truetype"); }</select>'); });
					//Color
					$(".dcolor").spectrum({
						preferredFormat: "hex3"
					}); 
				});
				</script>
                <?php foreach($fonts as $font){ if($d_text1->css->fontfamily == $font->name OR $d_text2->css->fontfamily == $font->name)?>
                	<style> 
                    	@font-face { font-family: "<?php echo $font->name; ?>"; src:url('<?php echo $font->file_url; ?>') format("truetype"); }
                    </style>
                <?php } ?>
     <style>
	 	.gftw.ui-draggable.ui-resizable{ padding:3px; cursor:move; display:flex; justify-content:center; align-items: center; position:absolute !important; left:220px; top:250px; z-index:100; }
		.gftw.ui-draggable.ui-resizable:hover{ border:1px solid #000; padding:2px; }
		.gftw.ui-draggable.ui-resizable .ui-resizable-se{ background-position: -64px -1000000px; }
		.gftw.ui-draggable.ui-resizable:hover .ui-resizable-se{ background-position: -64px -224px; right:-5px; bottom:-5px; display:block; }
		.gftw.ui-draggable.ui-resizable .close{ cursor:pointer; display:none; position:absolute; top:0px; left:0; }
		.gftw.ui-draggable.ui-resizable:hover .close{ display:block; }
		.gftw.ui-draggable.ui-resizable .gftw-required{ color:#fff; background:#f00; font-size:11px; position:absolute; top:-17px; right:0; display:none }
		.gftw.ui-draggable.ui-resizable:hover .gftw-required{ display:block; }
		.gftw.ui-draggable.ui-resizable .ovl-txt{ display:inline-block; white-space: nowrap; font-size:25px; line-height:0.75; }
		.gftw.ui-draggable.ui-resizable.pImage{ width:200px; }
		
	 	.main{ overflow: hidden; min-width:708px; }
     	.imageContiner{ position:relative; background:#fff; border:2px solid #ccc; width:500px; min-height:500px; float:left; }
		#imageMainP, #imageMainPr{ max-width:100%; }
		.imgDesigner{ position:absolute; z-index:900; width:500px; height:625px; top:0; left:0; }
		.imgTools{ float:left; margin-left:10px; width:195px;  } 
		
		.nice-select{ min-width:158px; }
     </style>

	 <?php

}
add_action('add_meta_boxes', 'add_designer_meta_box'); 

// Add Price Meta Box
function add_price_meta_box() {
    add_meta_box(
        'price_meta_box', // $id
        'Price', // $title 
        'show_price_meta_box', // $callback
        'products', // $page
        'normal', // $context
        'high'); // $priority
}
function show_price_meta_box() {
	global $post;
	echo '<input type="hidden" name="price_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
	 $meta = get_post_meta($post->ID, 'giftwrap_price', true);
	 echo '<input type="text" name="giftwrap_price" id="giftwrap_price" value="'.$meta.'" size="30" />';

}
add_action('add_meta_boxes', 'add_price_meta_box'); 


//Variants Meta
function add_variants_meta_box() {
    add_meta_box(
        'variants_meta_box', // $id
        'Variants', // $title 
        'show_variants_meta_box', // $callback
        'products', // $page
        'normal', // $context
        'high'); // $priority
}
function show_variants_meta_box() {
	global $post;
	echo '<input type="hidden" name="price_meta_box_nonce" value="'.wp_create_nonce(basename(__FILE__)).'" />';
	 $varaints = get_post_meta($post->ID, 'giftwrap_varaints', true);
	 $varaints =  json_decode($varaints);
	?>
    <div class="blockVariants"> 
    <?php 
	if(is_array($varaints))
	foreach($varaints as $varaint){ ?> 
    	<div class="gfwr-row">
            <div class="gfwr-image gfwr-col">
                <div class="gfwr-image" id=""><img src="<?php echo $varaint->url; ?>" alt="select image" width="50" height="50" class="gfwr-src" />
                <input type="hidden" name="gfwr-image[id][]" id="v_attach" value="<?php echo $varaint->id; ?>" />
                <input type="hidden" name="gfwr-image[url][]" id="v_image" value="<?php echo $varaint->url; ?>" /></div>
            </div> 
            <div class="gfwr-size gfwr-col">
                <label>Size</label>
                <input type="text" name="gfwr-image[size][]" id="size_1" value="<?php echo $varaint->size; ?>" />
            </div>
            <div class="gfwr-color gfwr-col">
                <label>Color</label>  
                <input type="text" name="gfwr-image[color][]" id="color_1" value="<?php echo $varaint->color; ?>" />
            </div>
            <div class="gfwr-color gfwr-col"> 
                <a class="gfwr-delete" href="javascript:void(0);">Detele</a>  
            </div>
        </div>
 	<?php }; ?>
        <div id="colne"><!--
        	<div class="gfwr-row">
                <div class="gfwr-image gfwr-col">
                    <div class="gfwr-image" id=""><img src="" width="50" height="50" class="gfwr-src" alt="Select Image" />
                    <input type="hidden" name="gfwr-image[id][]" id="v_attach" value="" />
                    <input type="hidden" name="gfwr-image[url][]" id="v_image" value="" /></div>
                </div>
                <div class="gfwr-size gfwr-col">
                    <label>Size</label>
                    <input type="text" name="gfwr-image[size][]" id="size_1" value="" />
                </div>
                <div class="gfwr-color gfwr-col">
                    <label>Color</label> 
                    <input type="text" name="gfwr-image[color][]" id="color_1" value="" />
                </div> 
                <div class="gfwr-color gfwr-col"> 
                    <a class="gfwr-delete" href="javascript:void(0);">Detele</a> 
                </div>
            </div> -->
        </div>
    </div>
    <p><a id="addNewVariant" href="javascript:void(0);">Add New</a></p>
    <style>
    	.gfwr-row{ overflow:hidden; }
		.gfwr-col{ display:inline-block; margin:10px; }
		.gfwr-image{ cursor:pointer; }
		a.gfwr-delete { color:#D20000; }
		a.gfwr-delete:hover{ color:#F00; }
    </style>
    <script type="text/javascript">
	// Media upload
	jQuery(document).ready(function($) {
		var custom_uploader;
	
		$(document).on('click', '.gfwr-image', function(e) {
			$('.gfwr-image').attr('id', '');
			$(this).attr('id', 'addImage'); 
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
				$('#addImage img').attr('src', attachment.url); 
				$('#addImage #v_image').val(attachment.url); 
				$('#addImage #v_attach').val(attachment.id);   
			});
	
			// Open the uploader dialog
			custom_uploader.open();
	
		});
		
		//Remove Row
		$(document).on('click', '.gfwr-delete', function(e) {
			$(this).closest('.gfwr-row').remove();
		});
		//Add New
		var CloneHtml = $("#colne").html().replace('<!--', '').replace('-->', '');
		$( '#addNewVariant' ).click(function(e) {
			$('.blockVariants').append(CloneHtml);
		});
	
	});
    </script>
    <?php

} 
add_action('add_meta_boxes', 'add_variants_meta_box');   

function add_featured_meta() {
    add_meta_box( 'featured_meta', __( 'Featured Product', 'textdomain' ), 'featured_meta_box', 'products', 'side', 'high' );
}
function featured_meta_box( $post ) {
    $featured = get_post_meta( $post->ID, 'featured-checkbox', true );
    ?>
	<p>
    <div class="featured-row-content">
        <label for="featured-checkbox"> 
            <input type="checkbox" name="featured-checkbox" id="featured-checkbox" value="yes" <?php if ( isset ( $featured ) ) checked( $featured, 'yes' ); ?> /> 
            <?php _e( 'Featured this product', 'textdomain' )?>
        </label>   
    </div>
</p>
 
    <?php
}
add_action( 'add_meta_boxes', 'add_featured_meta' );
   
//SaveAll Meta
function save_all_meta( $post_id) { //print_r($_POST); exit;
	if( !isset( $_POST['price_meta_box_nonce'] ) || !wp_verify_nonce( $_POST['price_meta_box_nonce'],basename(__FILE__)) ) return;
	$varaints =array();
	//Price
	if ( isset($_POST['giftwrap_price']) AND is_numeric($_POST['giftwrap_price']) ) { 
		update_post_meta($post_id, 'giftwrap_price', sanitize_text_field(esc_sql($_POST['giftwrap_price'])));
	}
	//Products Image 
	if ( isset($_POST['product_image']) AND !empty($_POST['product_image']) ) { 
		set_post_thumbnail( $post_id, $_POST['product_image']);
	}else{ delete_post_thumbnail( $post_id ); }
	
	//Variants
	$varaints = array();
	foreach($_POST['gfwr-image']['id'] as $imgKey => $imgVal):
		if ( isset($imgVal) AND !empty($imgVal) ) {
			$varaints[$imgKey]['id'] = $imgVal;
			$varaints[$imgKey]['url'] = $_POST['gfwr-image']['url'][$imgKey];
			$varaints[$imgKey]['size'] = $_POST['gfwr-image']['size'][$imgKey];
			$varaints[$imgKey]['color'] = $_POST['gfwr-image']['color'][$imgKey];   		
		}
	endforeach;
	update_post_meta($post_id, 'giftwrap_varaints', json_encode($varaints));
	
	//Featured 
	if ( isset($_POST['featured-checkbox']) AND !empty($_POST['featured-checkbox']) AND $_POST['featured-checkbox']=='yes') { 
		update_post_meta($post_id, 'featured-checkbox', sanitize_text_field(esc_sql($_POST['featured-checkbox'])));
	}else{ delete_post_meta($post_id, 'featured-checkbox'); }
	//Designer Image 
	if ( isset($_POST['designer_image']) AND !empty($_POST['designer_image'])) { 
		update_post_meta($post_id, 'giftwrap_designer_image', sanitize_text_field(esc_sql($_POST['designer_image'])));
	}else{ delete_post_meta($post_id, 'giftwrap_designer_image'); } 
	
	//Print Text 1 
	if ( isset($_POST['designer_text1']) AND !empty($_POST['designer_text1'])) { 
		update_post_meta($post_id, 'giftwrap_designer_text1', sanitize_text_field(esc_sql($_POST['designer_text1'])));
	}else{ delete_post_meta($post_id, 'giftwrap_designer_text1'); }
	
	//Print Text 2 
	if ( isset($_POST['designer_text2']) AND !empty($_POST['designer_text2'])) { 
		update_post_meta($post_id, 'giftwrap_designer_text2', sanitize_text_field(esc_sql($_POST['designer_text2'])));
	}else{ delete_post_meta($post_id, 'giftwrap_designer_text2'); } 
	
	//Generates Images with saved meta
	require_once(plugin_dir_path(__DIR__).'customizer.php');
	creatPrduct('save', $post_id);

}
add_action('save_post', 'save_all_meta');









