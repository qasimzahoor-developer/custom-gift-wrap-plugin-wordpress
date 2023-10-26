<?php $alert = displayAlert();
function display_cart($cID, $pID){
	$upload_dir = wp_upload_dir();  
	if(isset($_SESSION['printImage'][$cID]) AND is_array($_SESSION['printImage'][$cID])){  
		$imgPath = $upload_dir['baseurl'].'/giftwrap_temp/'.session_id().'/'.$cID.'/'.basename(get_attached_file(get_post_thumbnail_id($pID))); 
	}else{ 
		$imgPath = $upload_dir['baseurl'].'/giftwrap/products/'.$pID.'/'.basename(get_attached_file(get_post_thumbnail_id($pID)));
	}
	return $imgPath;	
} 

?>
<div class="container">
	<div class="row">
		<div class="col col-sm-12">
        <?php if(!empty($alert['type'])):
			    if($alert['type'] === 'error'): ?>
                <div class="alert alert-danger">
                  <strong>Error!</strong> <?php echo $alert['msg']; ?>
                </div> 
		   <?php else:?>
                <div class="alert alert-success">
                  <strong>Success!</strong> <?php echo $alert['msg']; ?>
                </div>
         <?php endif;
		endif; ?>
        </div>
		<div class="col col-sm-12">
            <div class="card">
				<div class="card-header">
					<div class="card-title">
						<div class="row">
							<div class="col col-sm-6">
								<h5><span class="glyphicon glyphicon-shopping-cart"></span> Shopping Cart</h5>
							</div>
							<div class="col col-sm-6">
								<a href="<?php echo home_url(); ?>" class="btn btn-primary btn-sm btn-block" >
									<span class="glyphicon glyphicon-share-alt"></span> Continue shopping
								</a>
							</div>
						</div>
					</div>
				</div>
				<div class="card-body">
                <?php if(count($Cart) < 1 OR empty($Cart)){ ?>
                	<div class="jumbotron">
                      <p><center>Cart is empty.</center></p>
                    </div>
				<?php } ?>
                <form action="<?php echo home_url(); ?>/ajax/?path=cart&updateitems" method="post" id="updateItems"> 
					<?php 
						$total = 0;
						foreach($Cart as $item){
						$price = get_post_meta($item->product, 'giftwrap_price', true );
						$total += ($item->qty*$price); 
						$options = json_decode($item->product_opt);
					?>
                    <div class="row cart-item">
						<div class="col col-sm-2"><img class="img-responsive" src="<?php echo display_cart($item->bundle, $item->product); ?>">  
						</div> 
						<div class="col col-sm-4">
							<h4 class="product-name"><strong><?php echo get_the_title($item->product); ?></strong></h4>
                            <p><small>
                            <?php foreach($options as $option=>$value):
								  print ucfirst(str_replace('opt_', '', $option)).': <small>'.$value.'</small> ';
								  endforeach; ?>
                            </small></p>
						</div>
						<div class="col col-sm-6">
							<div class="row">
                            <div class="col-sm-6 text-right">
								<h6><strong><?php echo $price; ?><span class="text-muted">x</span></strong></h6>
							</div>
							<div class="col col-sm-4">
								<input type="text" class="form-control input-sm" name="qty[<?php echo $item->id; ?>]" value="<?php echo $item->qty; ?>">
							</div> 
							<div class="col col-sm-2">
                            	<a href="javascript:void(0);" class="btn btn-xs btn-danger" onclick=" if (confirm('Are you sure you want to delete this?')) { deleteCart('del<?php echo $item->id; ?>'); }" data-id="<?php echo $item->id; ?>" data-action="<?php echo home_url(); ?>/ajax/?path=cart&deleteitem" id="del<?php echo $item->id; ?>" >
                                	x 
                                </a>
							</div>
                            </div>
						</div>
					</div>
					<?php } ?>
					<hr>
                    <?php if(count($Cart) > 0 OR !empty($Cart)){ ?>
					<div class="row text-center"> 
							<div class="col col-sm-9">
								<h6 class="text-right">Added items?</h6>
							</div>
							<div class="col col-sm-3">
								<button type="button" id="updateButton" class="btn btn-default btn-sm btn-block" onclick="updateCart('updateItems')">
									Update cart
								</button>
							</div>
					</div> 
                    <?php } ?>
                    </form>
				</div>
                <?php if(count($Cart) > 0 OR !empty($Cart)){ $shipping = esc_attr( get_option('gftw_shipping_per_order') ); ?>
				<div class="card-footer">
					<div class="row text-center">
						<div class="col col-sm-9">
							<h6 class="text-right">Sub Total: <strong>$<?php echo $total; ?></strong></h4>
                            <h6 class="text-right">Shipping Flat Rate:  <strong>$<?php echo $shipping; ?></strong></h4>
                            <h4 class="text-right">Total: <strong>$<?php echo $total+$shipping; ?></strong></h4>
						</div>
						<div class="col col-sm-3">
							<a href="<?php echo home_url(); ?>/checkout/" class="btn btn-success btn-block">
								Checkout
							</a>
						</div>
					</div>
				</div>
                <?php } ?>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript"> 
$("#updateItems").submit(function (e) { e.preventDefault(); updateCart('updateItems'); }); 
function updateCart(form){
	var form = $('#'+form);
	var url = form.attr('action');  
	var data = form.serialize();
	$('#updateButton').html('Please Wait...').attr('disabled', true);
	var jqxhr = $.post( url, data, function() {  }) 
	  .done(function() {
		$('#updateButton').html('Update cartss').attr('disabled', false);
			location.reload();
	  })
	  .fail(function() {
		alert( "error" );
	  })
	  .always(function() {
		$('#updateButton').html('Update cart').attr('disabled', false);
	  });
}
function deleteCart(recId){ 
	var url = $('#'+recId).data("action");
	var pID = $('#'+recId).data("id"); 
	var jqxhr = $.post( url, {id:pID}, function() { 
	
	})
	  .done(function() { 
		location.reload();
	  })
	  .fail(function() {
		alert( "error" );
	  })
	  .always(function() { 
	  });
} 
</script>