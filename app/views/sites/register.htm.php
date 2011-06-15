<?php $this->layout = 'register' ?>
<?php $this->selectedTab = 1 ?>
<?php $this->pageTitle = __('Crie seu Mobi') ?>

<?php echo $this->form->create('/sites/register', array(
    'id' => 'form-register-site-info',
    'class' => 'form-register',
    'object' => $site,
    'method' => 'file'
)) ?>

    <?php echo $this->element('sites/edit_form', array(
        'action' => 'register',
        'site' => $site
    )) ?>

<fieldset class="actions">
    <?php echo $this->form->submit(__('Avançar ›'), array(
        'class' => 'ui-button red large'
    )) ?>
</fieldset>

<?php echo $this->form->close() ?>
