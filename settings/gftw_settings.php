<?php

function register_gftw_settings() {
	//register our settings
	register_setting( 'gftw-settings-group', 'gftw_shop_name' );
	register_setting( 'gftw-settings-group', 'gftw_shop_address' );
	register_setting( 'gftw-settings-group', 'gftw_shop_email' );
	register_setting( 'gftw-settings-group', 'gftw_shop_phone' ); 

}

function gftw_settings_page() {
?>
<div class="wrap">
<h1>Gift Wrap Settings</h1>

<form method="post" action="<?php echo admin_url('options.php') ; ?>">
    <?php settings_fields( 'gftw-settings-group' ); ?>
    <?php do_settings_sections( 'gftw-settings-group' ); ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row">Shop Name</th>
            <td><input type="text" name="gftw_shop_name" value="<?php echo esc_attr( get_option('gftw_shop_name') ); ?>" /></td>
        </tr>
        <tr valign="top">
            <th scope="row">Shop Address</th>
            <td><textarea name="gftw_shop_address"><?php echo esc_attr( get_option('gftw_shop_address') ); ?></textarea></td>
        </tr>
        <tr valign="top">
            <th scope="row">Shop Email</th>
            <td><input type="email" name="gftw_shop_email" value="<?php echo esc_attr( get_option('gftw_shop_email') ); ?>" /></td>
        </tr>
        <tr valign="top"> 
            <th scope="row">Shop Phone</th>
            <td><input type="tel" name="gftw_shop_phone" value="<?php echo esc_attr( get_option('gftw_shop_phone') ); ?>" /></td>
        </tr>
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php } ?>