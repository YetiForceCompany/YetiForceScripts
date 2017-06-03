<?php
include_once 'include/main/WebUI.php';

?>
<table border="1" style="width: 100%;">
	<thead>
		<tr>
			<th>
				<span><?php echo App\Language::translate('LBL_LIBRARY', 'Settings::ConfReport'); ?></span>
			</th>
			<th>
				<span><?php echo App\Language::translate('LBL_INSTALLED', 'Settings::ConfReport'); ?></span>
			</th>
			<th>
				<span><?php echo App\Language::translate('LBL_MANDATORY', 'Settings::ConfReport'); ?></span>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach (\Settings_ConfReport_Module_Model::getConfigurationLibrary() as $key => $value) { ?>
			<tr <?php if ($value['status'] == 'LBL_NO') { ?>  style="color: red;" <?php } ?>>
				<td>
					<label><?php echo App\Language::translate($key, 'Settings::ConfReport'); ?></label>
				</td>
				<td>
					<label><?php echo App\Language::translate($value['status'], 'Settings::ConfReport'); ?></label>
				</td>
				<td>
					<label>
						<?php
						if ($value['mandatory']) {
							echo App\Language::translate('LBL_MANDATORY', 'Settings::ConfReport');
						} else {
							echo App\Language::translate('LBL_OPTIONAL', 'Settings::ConfReport');
						}

						?>
					</label>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<br><hr><br>
<table border="1" style="width: 100%;">
	<thead>
		<tr>
			<th>
				<span><?php echo App\Language::translate('LBL_PARAMETER', 'Settings::ConfReport'); ?></span>
			</th>
			<th>
				<span><?php echo App\Language::translate('LBL_RECOMMENDED', 'Settings::ConfReport'); ?></span>
			</th>
			<th>
				<span><?php echo App\Language::translate('LBL_VALUE', 'Settings::ConfReport'); ?></span>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach (\Settings_ConfReport_Module_Model::getConfigurationValue() as $key => $value) { ?>
			<tr <?php if ($value['status']) { ?>  style="color: red;" <?php } ?>>
				<td>
					<label><?php echo App\Language::translate($key, 'Settings::ConfReport'); ?></label>
				</td>
				<td>
					<label><?php echo App\Language::translate($value['prefer'], 'Settings::ConfReport'); ?></label>
				</td>
				<td>
					<label><?php echo App\Language::translate($value['current'], 'Settings::ConfReport'); ?></label>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
<br><hr><br>
<table border="1" style="width: 100%;">
	<thead>
		<tr >
			<th colspan="2">
				<h4><?php echo App\Language::translate('LBL_ENVIRONMENTAL_INFORMATION', 'Settings::ConfReport'); ?></h4>
			</th>
		</tr>
		<tr >
			<th>
				<span><?php echo App\Language::translate('LBL_PARAMETER', 'Settings::ConfReport'); ?></span>
			</th>
			<th>
				<span><?php echo App\Language::translate('LBL_VALUE', 'Settings::ConfReport'); ?></span>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach (\Settings_ConfReport_Module_Model::getSystemInfo() as $key => $value) { ?>
			<tr>
				<td>
					<label><?php echo App\Language::translate($key, 'Settings::ConfReport'); ?></label>
				</td>
				<td>
					<label><?php echo $value; ?></label>
				</td>
			</tr>
		<?php } ?>
	</tbody>
</table>
