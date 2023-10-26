<?php

function register_gftw_settings_ps() {
	//register our settings
	register_setting( 'gftw-settings-ps', 'gftw_shipping_per_order' );
	register_setting( 'gftw-settings-ps', 'gftw_paymeny_pp_api_key' ); 
	register_setting( 'gftw-settings-ps', 'gftw_paymeny_pp_api_user' ); 
	register_setting( 'gftw-settings-ps', 'gftw_paymeny_pp_api_password' ); 
	register_setting( 'gftw-settings-ps', 'gftw_paymeny_pp_api_sandbox' );   

}

function gftw_settings_page_ps() {
?>
<div class="wrap">
<h1>Shipping & Payment Settings</h1>

<form method="post" action="<?php echo admin_url('options.php') ; ?>"> 
    <?php settings_fields( 'gftw-settings-ps' ); ?>
    <?php do_settings_sections( 'gftw-settings-ps' ); ?>
    <h2>Shipping</h2>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Flat shipping rate</th>
            <td>$<input type="text" name="gftw_shipping_per_order" value="<?php echo esc_attr( get_option('gftw_shipping_per_order') ); ?>" /></td>
        </tr>
    </table>
    
    <h2>PayPal Express Payment</h2>    
    <table class="form-table"> 
        <tr valign="top">
            <th scope="row">PayPal API Signature</th>
            <td><input type="text" name="gftw_paymeny_pp_api_key" value="<?php echo esc_attr(get_option('gftw_paymeny_pp_api_key') ); ?>" /></td>
        </tr>
        <tr valign="top">
            <th scope="row">PayPal API User</th>
            <td><input type="text" name="gftw_paymeny_pp_api_user" value="<?php echo esc_attr( get_option('gftw_paymeny_pp_api_user') ); ?>" /></td>
        </tr>
        <tr valign="top">
            <th scope="row">PayPal API Password</th> 
            <td><input type="password" name="gftw_paymeny_pp_api_password" value="<?php echo esc_attr( get_option('gftw_paymeny_pp_api_password') ); ?>" /></td>
        </tr>
        <tr valign="top">
            <th scope="row">PayPal Sandbox Mode (Uncheck for production)</th>  
            <td><input type="checkbox" name="gftw_paymeny_pp_api_sandbox" value="1"  <?php checked( 1 == esc_attr( get_option('gftw_paymeny_pp_api_sandbox') ) ); ?>/></td>
        </tr> 
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php } ?>