<?php $skins = $theme->skins() ?>
<div class="page-heading">
	<div class="grid-4 first">&nbsp;</div>
	<div class="grid-8">
		<h1><?php echo $this->pageTitle = s('Customize') ?></h1>
	</div>
	<div class="clear"></div>
</div>

<?php echo $this->form->create('/sites/custom_theme', array(
	'id' => 'form-custom-theme',
	'class' => 'form-edit default-form',
)) ?>

	<fieldset style="position: relative;">
		<div class="themes">
			<div class="tip-big">
				<h2><?php echo s('customize your theme') ?></h2>
			</div>
			<div class="customize-theme">
				<ul class="featured-list">
					<li class="open">
						<div class="link">
							<span class="icon"></span>
							<h3><?php echo s('appearance') ?></h3>
							<small><?php echo s('you can add a restaurant menu, products, services, etc') ?></small>
							<span class="arrow open"></span>
						</div>
						<div class="content">
							<p class="title"><?php echo $theme->name() ?></p>
							<ul class="skin-picker">
								<?php foreach ($skins as $themeSkin): ?>
								<li class="<?php if ($skin->id() == $themeSkin->id()) echo 'selected' ?>" data-skin="<?php echo $themeSkin->id() ?>">
									<span style="background-color: #<?php echo $themeSkin->mainColor() ?>"></span>
								</li>
								<?php endforeach ?>
							</ul>
							<div class="colors-wrap">
								<?php foreach ($skins as $themeSkin): ?>
								<ul id="color-picker-<?php echo $themeSkin->id() ?>" class="color-picker <?php if ($skin->id() != $themeSkin->id()) echo 'hidden' ?>">
									<?php foreach($themeSkin->colors() as $name => $color): ?>
									<li>
										<span><?php echo $name; ?></span>
										<span class="color" data-color="<?php echo $name ?>" data-value="<?php echo $color ?>" style="background-color: <?php echo $color ?>"></span>
									</li>
									<?php endforeach ?>
								</ul>
								<?php endforeach ?>
							</div>
						</div>
					</li>
				</ul>
			</div>
		</div>
		<?php
		echo $this->form->input('parent_id', array(
			'type' => 'hidden',
			'value' => $skin->id(),
			'id' => 'parent_id'
		));

		foreach ($skin->colors() as $name => $color) { 
			echo $this->form->input("colors[$name]", array(
				'type' => 'hidden',
				'value' => $color,
				'id' => $name
			));
		}
		?>
		<?php echo $this->element('sites/theme_preview', array(
			'site' => $site,
			'autoload' => true
		)) ?>
	</fieldset>
	
	<fieldset class="actions">
		<?php echo $this->form->submit(s('Save and Continue'), array(
			'class' => 'ui-button red larger save-continue'
		)) ?>
		<?php echo $this->form->submit(s('Save'), array(
			'class' => 'ui-button red larger save'
		)) ?>
	</fieldset>

<?php echo $this->form->close() ?>
