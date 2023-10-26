<?php //var_dump($states) ?>
<div class="wrapper checkout-page">    
            <div class="row">
            	<div class="col col-md-12">
                <?php  if (!empty($errors->errors)): ?><div class="alert alert-danger"><strong>Something went wrong!</strong>
                <ul class="list-unstyled">
                <?php
                    foreach ( $errors->get_error_messages() as $err )
                        echo "<li><strong>Error</strong> $err</li>\n";
                ?> 
                </ul>
                </div>
				<?php endif; ?>
                </div>
                <div class="col col-lg-6 col-md-6 col-sm-6 col-xs-12">
                <form class="form-horizontal" method="post" action="">
 
                    <!--SHIPPING METHOD-->
                    <div class="card">
                        <div class="card-header">Address</div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="col col-md-12">
                                    <h4>Shipping Address</h4>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col col-md-6 col-xs-12">
                                    <strong>First Name:</strong>
                                    <input type="text" name="first_name" class="form-control" value="" required="required"/>
                                </div>
                                <div class="span1"></div>
                                <div class="col col-md-6 col-xs-12">
                                    <strong>Last Name:</strong>
                                    <input type="text" name="last_name" class="form-control" value="" required="required"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col col-md-12"><strong>Address:</strong></div>
                                <div class="col col-md-12">
                                    <input type="text" name="address" class="form-control" value="" required="required"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col col-md-12"><strong>City:</strong></div>
                                <div class="col col-md-12">
                                    <input type="text" name="city" class="form-control" value="" required="required"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col col-md-6">
                                <strong>State:</strong>
                                    <select type="text" name="state" class="form-control" required="required">
                                    	<option value="">Select State</option>
									<?php foreach($states as $state):?>
                                    	<option value="<?php echo $state['name']; ?>"><?php echo $state['name']; ?></option>
                                    <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col col-md-6">
                                <strong>Zip Code:</strong>
                                    <input type="zip" name="zip_code" class="form-control" value="" required="required"/>
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col col-md-12"><strong>Phone Number:</strong></div>
                                <div class="col col-md-12"><input type="tel" name="phone_number" class="form-control" value="" required="required"/></div>
                            </div>
                            <div class="form-group row">
                                <div class="col col-md-12"><strong>Email Address:</strong></div>
                                <div class="col col-md-12"><input type="email" name="email_address" class="form-control" value="" required="required"/></div>
                            </div>
                        </div>
                    </div>
                    <!--SHIPPING METHOD END-->
                    
                </div>
                <div class="col col-lg-6 col-md-6 col-sm-6 col-xs-12">
                   <!--CREDIT CART PAYMENT-->
                    <div class="card card-info">
                        <div class="card-header"><span><i class="glyphicon glyphicon-lock"></i></span> Secure Payment</div>
                        <div class="card-body">
                            <div class="form-group">
                                <div class="col col-md-12"><strong>PayPal</strong></div>
                                <div class="col col-md-12">
                                	<img src="<?php echo plugins_url('giftwrap/assts/img/paypal-payment.jpg')?>" alt="PayPal Secure payment" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!--CREDIT CART PAYMENT END--> <br /> 
                    		<div class="form-group">
                                <div class="col col-md-12 col-sm-12 col-xs-12">
                                    <button type="submit" class="btn btn-primary btn-submit-fix">Place Order Now</button>
                                </div>
                            </div>
                    
                </div>
                
                </form>
            </div>
            <div class="row cart-footer"> 
        
            </div>
    </div>