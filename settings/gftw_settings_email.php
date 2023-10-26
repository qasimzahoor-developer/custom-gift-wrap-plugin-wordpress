<?php

function register_gftw_settings_email() {
	//register our settings
	register_setting( 'gftw-settings-email', 'gftw_order_main' );
	register_setting( 'gftw-settings-email', 'gftw_order_approve' );
	register_setting( 'gftw-settings-email', 'gftw_order_reject' );
	//register_setting( 'gftw-settings-email', 'gftw_order_sh' );

}

function gftw_settings_page_email() {   
?>
<div class="wrap">
<h1>Gift Wrap Settings</h1>

<form method="post" action="<?php echo admin_url('options.php') ; ?>">
    <?php settings_fields( 'gftw-settings-email' ); ?>
    <?php do_settings_sections( 'gftw-settings-email' ); ?> 
    <table class="form-table">
        <tr valign="top"> 
            <th scope="row">Order Email</th>  
            <td><?php echo wp_editor( ( get_option('gftw_order_main')), 'gftwordermain', array('textarea_name' => 'gftw_order_main')  ); ?> </td>
        </tr>   
        <tr valign="top">
            <th scope="row">Order Approved Email</th>  
            <td><?php echo wp_editor( ( get_option('gftw_order_approve')), 'gftworderapprove', array('textarea_name' => 'gftw_order_approve')); ?> </td>
        </tr> 
        <tr valign="top"> 
            <th scope="row">Order Rejected Email</th>  
            <td><?php echo wp_editor( ( get_option('gftw_order_reject')), 'gftworderreject', array('textarea_name' => 'gftw_order_reject')); ?> </td>
        </tr>
    </table>
     
    <?php submit_button(); ?>

</form>
</div>
<?php } ?>