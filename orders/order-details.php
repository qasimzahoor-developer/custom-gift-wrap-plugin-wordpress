<?php 
$order_nonce = wp_create_nonce( 'delete_order' );
$alert = displayAlert();
//var_dump($payment);?>
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
<br /><br />
		<div id="rejectOrder" class="" style="display:none;">
        	<form method="post" enctype="multipart/form-data" action="<?php echo admin_url('admin.php?page=orders&action=reject&order='.$order->id.'&_wpnonce='.$order_nonce); ?>"> 
                <div id="universal-message-container">
                <h2>Order Rejection</h2> 
     
                <div class="options"> 
                    <p> 
                        <label>Comments</label>
                        <br />
                        <textarea type="text" name="order_reject_comments"></textarea>
                    </p>
                </div>
			<?php
				wp_nonce_field( 'reject-order', 'reject-order' );
				submit_button(); 
        	?>
            </div>
            </form> 
   </div>
<table class="wp-list-table widefat fisxed orders" style="max-width:98.5%">
<tr colspan="2"><td><h1>View Order #<?php echo $order->order_no; ?></h1></td>
<td style="text-align:right"><span style="font-size:16px; margin-right:10px;">Status: <?php echo $order->order_status; ?></span> 
<?php if($order->order_status =='Waiting'): ?>
<a href="<?php echo admin_url('admin.php?page=orders&action=approve&order='.$order->id.'&_wpnonce='.$order_nonce); ?>" class="button button-primary">Approve Order</a>
<a href="#TB_inline?&width=300&height=200&inlineId=rejectOrder" class="button button-default thickbox">Reject Order</a> 
<?php endif; ?>
<?php if($order->order_status =='Rejected'): ?> 
<a href="<?php echo admin_url('admin.php?page=orders&action=approve&order='.$order->id.'&_wpnonce='.$order_nonce); ?>" class="button button-primary">Approve Order</a>
<?php endif; ?>
</td>
</tr>
<tr>
<td>
<table class="wp-list-table widefat fixed striped striped">
    <tr>
        <td><h4>Shipping Deatils</h4></td>
    </tr>
    <tr><td>
    	<strong>Name:</strong> <?php echo $order->first_name; ?> <?php echo $order->last_name; ?><br />
        <strong>Email:</strong> <a href="<?php echo $order->email; ?>"><?php echo $order->email; ?></a><br /> 
        <strong>Phone:</strong> <?php echo $order->phone; ?><br /><br />
        <strong>Address:</strong> <br /> <?php echo $order->address; ?><br /><?php echo $order->city; ?>, <?php echo $order->zipcode; ?><br /><?php echo $order->state; ?>, USA
        
    </td></tr>
</table>

</td>
<td>


<table class="wp-list-table widefat fixed striped striped">
    <tr>
        <td><h4>Payment Details</h4></td>
    </tr>
    <tr><td> <?php ?>
    	<strong>Payment Method:</strong>  <h3 style="display:inline-block; margin:0 10px;">PayPal</h3>(<?php echo $payment['TRANSACTIONTYPE']; ?>)<br /><br /> 
        <strong>Payment Status:</strong> <?php echo $payment['PAYMENTSTATUS']; ?><br />
        <strong>PayPal Email:</strong> <a href="<?php echo $payment['EMAIL']; ?>"><?php echo $payment['EMAIL']; ?></a><br /> 
        <strong>Transaction ID:</strong> <?php echo $payment['TRANSACTIONID']; ?><br /><br />
        <strong>Total Payment Collected:</strong> <?php echo $payment['AMT']; ?>  <?php echo $payment['CURRENCYCODE']; ?><br /><br /><?php //print_r($payment); ?>
    </td></tr>
</table>

</td>
</tr>
<?php if($order->comments !=='' AND $order->order_status == 'Rejected'):?>
<tr>
<td>


<table class="wp-list-table widefat fixed striped striped">
    <tr>
        <td><h4>Comments</h4></td>
    </tr>
    <tr><td><?php echo $order->comments; ?></td></tr>
</table>

</td>

</tr>
<?php endif; ?>
<tr><td colspan="2">

<h2>Purchased Items</h2>

<table class="wp-list-table widefat fixed striped striped">
    <tr><td>Image</td><td>ID</td><td>Name</td><td>Price</td><td>Qty.</td><td>Sub Total</td></tr>
    <?php $subtotal = 0; foreach($cart as $cItem):
	$subtotal+=$cItem->price*$cItem->qty; ?>
    <tr>
    	<td><img width="100%" src="<?php echo $uploads['baseurl'].'/giftwrap/orders/order_'.$order->order_no.'/'.basename($cItem->img); ?>" /></td>
    	<td><?php echo $cItem->id;?></td>
        <td><strong><?php echo $cItem->name;?> </strong><br /> 
			<?php foreach($cItem->options as $oKey=>$oVal): ?>
            	<small><?php echo $oKey;?>: <i><?php echo $oVal;?></i> </small><br/>
            <?php endforeach; ?>
            <?php if(isset($cItem->printtext) AND $cItem->printtext[0]->text !==null){ $i=1; foreach($cItem->printtext as $print): 
			$text = trim($print->text);
			if(!empty($text)){
			 //var_dump($print); //exit; ?>
            	<small>Text <?php echo $i?>: <i><?php echo $text;?></i> </small><br/>
                <small>Color <?php echo $i?>: <i><?php echo $print->css->fontColor;?></i> </small><br/>
                <small>Font <?php echo $i?>: <i><?php echo $print->css->fontfamily;?></i> </small><br/> 
            <?php } $i++; endforeach; } ?>
        </td>
        <td><?php echo $cItem->price;?></td>
        <td><?php echo $cItem->qty;?></td>
        <td><?php echo $cItem->price*$cItem->qty;?></td></tr>
    <?php endforeach; ?>
</table>

</td></tr>

<tr>

<td><?php if($order->order_status !=='Pending'): ?>Download used images <a href=#"">click here</a><?php endif; ?></td>

<td style="text-align:right; margin-right:10px;">

<h3>Order Sub Total: <?php echo $subtotal; ?> $</h3>
<h3>Order Shipping: <?php echo $cItem->shipping; ?> $</h3>
<h3>Order Grand Total: <?php echo $subtotal+$cItem->shipping; ?> $</h3>
<br />
<br />
</td></tr>

</table>

<?php add_thickbox(); ?>
