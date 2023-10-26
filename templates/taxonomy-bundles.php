<?php
/**
 * The template for displaying archive pages
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Seventeen
 * @since 1.0
 * @version 1.0
 */ 
get_header();
wp_enqueue_script( 'giftwrap-assts-designer', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js','', true);
wp_enqueue_style( 'giftwrap-assts-designer', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/themes/smoothness/jquery-ui.css','', true);
wp_enqueue_script('jquery-ui-custom-objects', plugins_url('giftwrap/assts/js/designer_objects.js'));
wp_enqueue_script('spectrum', plugins_url('giftwrap/templates/js/spectrum.js'));
wp_enqueue_style('spectrum', plugins_url('giftwrap/templates/css/spectrum.css'));
wp_enqueue_script('nice-select', plugins_url('giftwrap/templates/js/jquery.nice-select.min.js'));
wp_enqueue_style('nice-select', plugins_url('giftwrap/templates/css/nice-select.css'));
global $wp_query; 
	
	if(isset($wp_query->query_vars['product']) AND !empty($wp_query->query_vars['product'])){
		//Featured 
		$args = ['post_type' => 'products','name'=>$wp_query->query_vars['product'], 'posts_per_page'=>1, 'tax_query'=>[[ 'taxonomy' => 'bundles','field' => 'term_id', 'terms' => get_queried_object()->term_id]]];
		$featured = get_posts( $args );
		$featuredMeta = get_post_meta($featured[0]->ID);
		$featuredCustom = json_decode($featuredMeta['giftwrap_varaints'][0]);
		$args = ['post_type' => 'products', 'exclude'=>[$featured[0]->ID], 'tax_query'=>[[ 'taxonomy' => 'bundles','field' => 'term_id', 'terms' => get_queried_object()->term_id]]];
		$others = get_posts( $args );
	}else{
		//Featured 
		$args = ['post_type' => 'products','posts_per_page'=>1, 'tax_query'=>[[ 'taxonomy' => 'bundles','field' => 'term_id', 'terms' => get_queried_object()->term_id]], 'meta_query' => [['key' => 'featured-checkbox', 'value'=>'yes', 'compare' => '=' ]]];
		$featured = get_posts( $args );
		$featuredMeta = get_post_meta($featured[0]->ID);
		$featuredCustom = json_decode($featuredMeta['giftwrap_varaints'][0]);
		$args = ['post_type' => 'products', 'tax_query'=>[[ 'taxonomy' => 'bundles','field' => 'term_id', 'terms' => get_queried_object()->term_id]],'meta_query' => [['key' => 'featured-checkbox', 'compare' => 'NOT EXISTS' ]]];
		$others = get_posts( $args );
	}
	if(!$featured[0]->ID){
		
		//Featured 
		$args = ['post_type' => 'products','name'=>$wp_query->query_vars['product'], 'posts_per_page'=>1, 'tax_query'=>[[ 'taxonomy' => 'bundles','field' => 'term_id', 'terms' => get_queried_object()->term_id]]];
		$featured = get_posts( $args );
		$featuredMeta = get_post_meta($featured[0]->ID);
		$featuredCustom = json_decode($featuredMeta['giftwrap_varaints'][0]);
		$args = ['post_type' => 'products', 'exclude'=>[$featured[0]->ID], 'tax_query'=>[[ 'taxonomy' => 'bundles','field' => 'term_id', 'terms' => get_queried_object()->term_id]]];
		$others = get_posts( $args );
		    
		}  
		
$fonts = $wpdb->get_results("select * from ".$wpdb->prefix."gftw_fonts;", OBJECT); 
$dMeta = isset($_SESSION['design'][get_queried_object()->term_id]) ? $_SESSION['design'][get_queried_object()->term_id]: NULL;  
function display_img($cID, $pID, $url=false){ 
	$upload_dir = wp_upload_dir();  
	if(isset($_SESSION['printImage'][$cID]) AND is_array($_SESSION['printImage'][$cID])){  
		$imgPath = $upload_dir['baseurl'].'/giftwrap_temp/'.session_id().'/'.$cID.'/'; 
	}else{ 
		$imgPath = $upload_dir['baseurl'].'/giftwrap/products/'.$pID.'/';  
	} 
	if($url !== false) return $imgPath.basename($url);
	return $imgPath.basename(get_attached_file(get_post_thumbnail_id($pID)));	 
}
?>
<div class="container">
<div class="row">
    <div class="col-md-12">
    <header class="product-header">
			<?php
				the_archive_title( '<h1>', '</h1>' );
				the_archive_description( '<div>', '</div>' );  
			?>
		</header><!-- .page-header -->

    <section class="panel">
          <div class="panel-body row">
              <div class="col-md-5">
                  <div class="pro-img-details"> 
                  	<form method="post" id="dPreview">
                    	<input type="hidden" name="bundle" value="<?php echo get_queried_object()->term_id; ?>" />
                        <input type="hidden" name="id" value="<?php echo $featured[0]->ID;?>" />
                         <div id="imageContiner" class="gftw-container"> 
                            <!--<div class="imgDesigner gftw-designer"> </div>imgDesigner--> 
                                <img src="<?php echo display_img(get_queried_object()->term_id, $featured[0]->ID); ?>" alt="" id="fImage"> 
                         </div>
                     </form>
                   </div>   
                      <div class="pro-img-list">
                          <?php foreach($others  as $others): ?>
                          <a href="<?php echo get_term_link(get_queried_object()->term_id).$others->post_name.'/'; ?>"> 
                              <img src="<?php echo display_img(get_queried_object()->term_id, $others->ID); ?>" alt="" width="100">
                          </a>
                          <?php endforeach; ?> 
                      </div>
              </div> 
              <div class="col-md-7">
              	  <h4 class="pro-d-title"><?php echo $featured[0]->post_title;?></h4>   
                  <div class="m-bot15 pro-price"> <strong>Price : </strong> <span>$<?php echo $featuredMeta['giftwrap_price'][0];?></span></div>
				  <form action="<?php echo home_url(); ?>/cart?addtocart" enctype="multipart/form-data" method="post" id="productData"> 
                  <input type="hidden" name="id" value="<?php echo $featured[0]->ID;?>" />
                  <input type="hidden" name="cID" value="<?php echo get_queried_object()->term_id;?>" />
                  <input type="hidden" name="bundle" value="<?php echo get_queried_object()->term_id; ?>" />
                  <?php if(isset($featuredCustom[0]->color)): $printedA=array(); ?>
                  <div class="form-group">
                      <label>Color</label> 
                      <select name="opt_color" id="fColor" class="form-control">
                      	<?php foreach($featuredCustom as $fColor): if(!in_array($fColor->color, $printedA) AND !empty($fColor->color)): $printedA[] = $fColor->color; ?>  
                        <option data-color="<?php echo sanitize_title($fColor->color); ?>"  data-url="<?php echo display_img(get_queried_object()->term_id, $featured[0]->ID, $fColor->url); ?>" value="<?php echo $fColor->color;?>"><?php echo $fColor->color;?></option>
                        <?php endif; endforeach; ?>
                      </select> 
                  </div>
                  <?php endif; ?>
                  <?php if(isset($featuredCustom[0]->size)): ?>
                  <div class="form-group">
                      <label>Size</label> 
                      <select name="opt_size" id="fSize" class="form-control">
                      	<?php foreach($featuredCustom as $fSize): if($fSize->color == $featuredCustom[0]->color AND!empty($fSize->size) ){ ?>
                        <option value="<?php echo $fSize->size;?>"><?php echo $fSize->size;?></option>
                        <?php } endforeach; ?>
                      </select>  
                  </div>
                  <?php endif; ?>
                  <div class="input-group mb-2">
                  <div class="custom-file">
                    <input type="file" class="custom-file-input" name="printImage"  id="printOnImage"  >
                    <label class="custom-file-label form-control-file" for="inputGroupFile01"><?php echo (isset($dMeta))? basename($dMeta['image']) : 'Chose  Image'?></label>
                  </div>
                </div>
                <div class="input-group mb-3">
                  <input type="text" id="dText1" name="dText1" value="<?php echo (isset($dMeta))? $dMeta['text1']->text : ''?>" placeholder="Custom Text" aria-label="Custom Text" aria-describedby="basic-addon2"><br />
                  <input type="text" class="dcolor" id="dColor1" name="dColor1" value="<?php echo (isset($dMeta))? $dMeta['text1']->css->fontColor : '#000000'?>">
                  <select class="nice-select" id="dFamily1" name="dFamily1">  
                    <?php foreach($fonts as $font){ ?>
                    	<option value="<?php echo $font->name ?>" data-display="<?php echo $font->name ?>" data-image="<?php echo $font->p_url ?>" <?php echo (isset($dMeta) AND $dMeta['text1']->css->fontfamily == $font->name)? 'selected="selected"' : ''?>><?php echo $font->name ?></option>  
                    <?php } ?>
                  </select> 
                  <select class="custom-select" id="dStyle1" name="text-style"  style="display:none;">
                    <option>Font Style</option>
                    <option value="normal">Normal</option>
                    <option value="italic">Italic</option>
                  </select> 
                  <div class="input-group-append" style="display:none">
                    <button class="btn btn-outline-secondary" id="dAdd1" type="button">Add Text</button>
                  </div>  
                </div>
                <div class="input-group mb-3">
                  <input type="text" id="dText2" name="dText2" value="<?php echo (isset($dMeta))? $dMeta['text2']->text : ''?>" placeholder="Custom Text" aria-label="Custom Text" aria-describedby="basic-addon2">
                  <input  type="text" class="dcolor" id="dColor2" name="dColor2" value="<?php echo (isset($dMeta))? $dMeta['text2']->css->fontColor : '#000000'?>"> 
                  <select class="nice-select" id="dFamily2" name="dFamily2">
                     <?php foreach($fonts as $font){ ?> 
                    	<option value="<?php echo $font->name ?>" data-display="<?php echo $font->name ?>" data-image="<?php echo $font->p_url ?>" <?php echo (isset($dMeta) AND $dMeta['text2']->css->fontfamily == $font->name)? 'selected="selected"' : ''?>><?php echo $font->name ?></option> 
                    <?php } ?>
                  </select>  
                  <select class="custom-select" id="dStyle2" name="text-style"  style="display:none;">
                    <option>Font Style</option>
                    <option value="normal">Normal</option>
                    <option value="italic">Italic</option>   
                  </select> 
                  <div class="input-group-append" style="display:none">
                    <button class="btn btn-outline-secondary" id="dAdd2" type="button">Add Text</button>
                  </div>
                </div>
                <script>
				$( document ).ready(function() {
					//Font
					$('.nice-select').niceSelect();
					
					//Color
					$(".dcolor").spectrum({
						preferredFormat: "hex3"
					}); 
				});
				</script>
                  <script type="text/livescript">	
				 
				  /*$( "#dAdd1" ).click(function() {
					 var dText = $( "#dText1" ).val() 
					 if(dText === ''){ alert('"Print Text 1" is Required'); return }
											$( ".gftw-designer" ).txtPrinter({ name: 'designer_text1', text:dText, containment:'.gftw-container', 
												required:0, css:{fontfamily:$( "#dFamily1" ).val(), 
												fontColor:$( "#dColor1" ).val(), fontSize:77, fontStyle:$( "#dStyle1" ).val(), 
												left:100, top:40} });
					 });
					 $( "#dAdd2" ).click(function() {
					 var dText = $( "#dText2" ).val() 
					 if(dText === ''){ alert('"Print Text 2" is Required'); return }
											$( ".gftw-designer" ).txtPrinter({ name: 'designer_text2', text:dText, containment:'.gftw-container', 
												required:0, css:{fontfamily:$( "#dFamily2" ).val(), 
												fontColor:$( "#dColor2" ).val(), fontSize:22, fontStyle:$( "#dStyle2" ).val(), 
												left:100, top:40} });
					 });*/
				  		   
                  document.getElementById('printOnImage').addEventListener('change', readURL, true);
				  function readURL(){
					 var fileName = $(this).val().replace(/C:\\fakepath\\/i, ''); 
  					 $(this).next('.form-control-file').addClass("selected").html(fileName);
					  
					var form = $('#productData'); 
					var formdata = false; 
					if (window.FormData){
						formdata = new FormData(form[0]);
					} 
					$.ajax({
						url         : '<?php echo home_url(); ?>/ajax/?path=customizer&upload',
						data        : formdata ? formdata : form.serialize(),
						cache       : false,
						contentType : false,
						processData : false, 
						type        : 'POST',
						beforeSend: function( xhr ) { $('#printOnImage').next('.form-control-file').html('Please wait...'); },
						success     : function(data, textStatus, jqXHR){ 
							var json = $.parseJSON(data);  
							if(json.file){ 
								//$('#printOnImage').next('.form-control-file').html(fileName); 
								//$('.gftw-designer').imgPrinter({ name: 'designer_image', url:json.file, containment:'.gftw-container', attachement:null, css:{width:100, left:100, top:30} });
								
							}
							if(json.error === 'true'){ 
								alert(json.msg);
							}
						},
						//complete: function (){ $('#printOnImage').next('.form-control-file').html('Change Image'); }
					});
					}
                  </script>
                  <div class="form-group">
                      <label>Quantity</label>
                      <input type="quantiy" name="qty" value="1" class="form-control quantity">
                  </div>
                  <p>
                      <button class="btn btn-round btn-primary" id="dPreviewb" type="button"><i class="fa fa-shopping-cart"></i> Preview Now</button>
                      <button class="btn btn-round btn-danger" id="addCart" type="submit"><i class="fa fa-shopping-cart"></i> Add to Cart</button>
                  </p>
                  </form>
                  <script type="text/javascript">    
					<?php
					//generate options
					$printedA = array(); 
					foreach($featuredCustom as $options):  if(!in_array($options->color, $printedA)){ $printedA[] = $options->color; 
						echo 'var c_'.str_replace('-','_',sanitize_title($options->color)).' = [';
						foreach($featuredCustom as $size): if($size->color == $options->color) echo '"'.$size->size.'",'; endforeach; 
						echo ' ""]; ';  
					} endforeach; ?>
					$('#fColor:not(:has(option))').closest('.form-group').hide();
					$('#fSize:not(:has(option))').closest('.form-group').hide();
					$('#fColor').change(function(){ 
						var key = $(this).find(':selected').data("color");
						var url = $(this).find(':selected').data("url");
						$('#fSize').find('option').remove();
						for(var i=0; i< (window['c_'+key].length-1);i++)
						{
							$('<option/>', { value: window['c_'+key][i], html: window['c_'+key][i] }).appendTo('#fSize');	
						}
						$('#fImage').attr('src', url); 
					});
					 
					// preview
					$('#dPreviewb').click(function(){
						
						var form = $('#productData'); 
						var formdata = false;
						if (window.FormData){
							formdata = new FormData(form[0]);
						} 
						$.ajax({
							url         : '<?php echo home_url(); ?>/ajax/?path=customizer&preview', 
							data        : formdata ? formdata : form.serialize(),
							cache       : false,
							contentType : false,
							processData : false, 
							type        : 'POST',
							beforeSend: function( xhr ) { $('#dPreviewb, #addCart').html('Please wait...').attr('disabled','disabled'); },
							success     : function(data, textStatus, jqXHR){ 
								//var json = $.parseJSON(data); 
								location.reload(true);
								/*if(json.file){  
									 
									  
								}
								if(json.error === 'true'){  
									alert(json.msg);
								}*/
							},
							//complete: function (){ $('#dPreviewb').html('Preview');$('#dPreviewb, #addCart').removeAttr('disabled'); } 
						});
						 
					});
				  </script>
              </div>
    <div class="col-md-7">
    <section>
    	<ul class="nav nav-tabs"> <li class="active"> <a data-toggle="tab" href="javascript:void(0);">Details</a> </li></ul>
        <div class="tab-content">
        <div class="details"><?php echo $featured[0]->post_content; ?></div>
                  </div>
    </section>
    </div>
          </div>
      </section>
      </div>
</div><!--row -->

</div><!-- .container -->
<?php
get_footer();
