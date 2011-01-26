<!DOCTYPE html>
<html>
    <head>
        <?php echo $this->html->charset() ?>
        <title><?php echo __('MeuMobi Restaurant - Seu restaurante na Web móvel em 3 minutos!') ?></title>
        <link rel="shortcut icon" href="<?php echo Mapper::url("/images/layout/favicon.png") ?>" type="image/png" />
		<?php echo $this->html->stylesheet('home', 'uikit') ?>
    </head>
    
    <body>
		<div id="header">
		    <div class="logo">
			    <?php echo $this->html->link($this->html->image('layout/logo-home.png', array('alt'=>'MeuMobi')), '/', array('class'=>'logo')) ?>
			</div>
			
			<p class="login">
			    <?php echo $this->html->link(__('Efetue login'), '/login') ?> <?php echo __('ou') ?> <?php echo $this->html->link(__('Cadastre-se'), '/register') ?>
			</p>
			
			<div class="get-started">
			    <h2><?php echo __('Seu restaurante na palma da mão em menos de 3 minutos.') ?></h2>
			    <p class="subtitle"><?php echo __('MeuMobi Restaurant coloca o seu negócio na
                Internet móvel em instantes.') ?></p>
                <?php echo $this->html->link(__('crie seu mobi já!'), '/register') ?>
			</div>
			
			<div id="slideshow">
			    <?php echo $this->html->image('home/slides/iphone.png'); ?>
			    <?php echo $this->html->image('home/slides/blackberry.png'); ?>
			    <?php echo $this->html->image('home/slides/android.png'); ?>
			</div>
            
			<div class="clear"></div>
			<div id="login-window">
                <p><?php echo $this->html->link(__('Efetue login'), '/login') ?></p>
                <?php echo $this->form->create('/users/login') ?>
                    <?php echo $this->form->input('email', array(
                        'label' => __('E-Mail'),
                        'class' => 'ui-text'
                    )) ?>
                    <?php echo $this->form->input('password', array(
                        'label' => __('Senha'),
                        'class' => 'ui-text'
                    )) ?>
                    <?php echo $this->form->input('remember', array(
                        'label' => false,
                        'type' => 'checkbox'
                    )) ?>
                    <label for="FormRemember" class="checkbox"><?php echo __('Manter conectado') ?></label>
                    <?php echo $this->form->submit(__('Login'), array(
                        'class' => 'ui-button red'
                    ))?>
                    <?php echo $this->html->link(__('cancelar'), '#', array(
                        'class' => 'cancel'
                    )) ?>
                <?php echo $this->form->close() ?>
            </div>
		</div>
	
	    <div id="content">
            <?php echo $this->contentForLayout ?>
        </div>
        
        <?php echo $this->element("layouts/footer") ?>
        
        <?php echo $this->html->script('jquery', 'jquery.cycle.all.min.js') ?>
        <?php echo $this->html->scriptsForLayout ?>
        <script type="text/javascript">
            $('#slideshow').cycle({'fx': 'scrollUp'});
        
            $('p.login a:first-of-type').click(function(e){
                $('#login-window').show();
                $('#FormEmail').focus();
                e.preventDefault();
            });
        
            $('#login-window a').click(function(e){
                $('#login-window').hide();
                e.preventDefault();
            });
        
            $(document).keyup(function(e) {
              if (e.keyCode == 27) { $('#login-window').hide(); }
            });
        </script>
    </body>
</html>