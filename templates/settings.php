<div class="wrap">

	<?php  echo '<img src="' . plugins_url('../images/CuriyoFull.png', __FILE__) . '" /> '; ?>
    <h2>Curiyo Links - Settings <span id="wp-curiyo_version" style="font-size:12px">Version 1.3</span> </h2>

    <form id="curiyo-settings-form" method="post" action="options.php">

		<?php @settings_fields('curiyo-group'); ?>
        <?php @do_settings_fields('curiyo-group'); ?>


        <?php curiyo_do_settings_sections('curiyo'); ?>
        <?php submit_button('Update'); ?>
 </form>
    
    <div class="support">
    <p>Need help? Have feedback? Contact us at <a target="_blank" href="mailto:publishers@curiyo.com">publishers@curiyo.com</a>.</p>
    </div>
</div>