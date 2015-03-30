<div class="slide-header">
	<div class="grid-4 first"><?php echo $this->html->link(s('‹ back'), '/categories', array( 'class' => 'ui-button large back pop-scene' )) ?>
	</div>
	<div class="grid-8">
		<h1><?php echo $this->pageTitle = s('Edit Category') ?></h1>
		<?php echo $this->element('common/breadcrumbs', array(
			'category' => $category
		)) ?>
	</div>
	<div class="clear"></div>
</div>
<?php echo $this->element('categories/form', array(
	'action' => null,
	'category' => $category,
	'site' => $site
)) ?>
