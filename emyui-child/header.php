 
<!DOCTYPE html> 
<html <?php language_attributes(); ?>> 
    <head> 
        <meta charset="<?php bloginfo( 'charset' ); ?>"/> 
        <meta http-equiv="X-UA-Compatible" content="ie=edge"/> 
        <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no"/>                                                      
        <!-- favicon -->         
        <link rel="shortcut icon" href="<?php echo esc_url( get_template_directory_uri() ); ?>/image/favicon/favicon.png" type="image/x-icon"/> 
        <!-- Bootstrap , fonts & icons  -->                                    
        <?php wp_head(); 
        if(class_exists('ReduxFramework') and class_exists('Boostify_Header_Footer_Builder')){ 
        global $emyuiredux;
        $prel = $emyuiredux['opt3']; 
        } ?>

    </head>     
    <body class="<?php echo implode(' ', get_body_class()); ?>">
        <?php if( function_exists( 'wp_body_open' ) ) wp_body_open(); ?> 
       
            
        <!-- start site-wrapper -->         
        <div class="site-wrapper overflow-hidden"> 
            <!-- Header shadow background -->             
            <?php /*<div class="header-shadow-bottom"></div>*/ ?>   
                
            
            <?php if (  function_exists( 'boostify_header_active' ) && boostify_header_active() ): ?>
	<?php boostify_get_header_template(); //Custom header ?>
<?php else: ?>
       <!-- START template header -->             
       <header class="site-header header-with-right-menu dark-mode-texts site-header--absolute fixed-header-layout"> 
                <div class="container-fluid pr-lg-9 pl-lg-9"> 
                    <!-- START navbar -->                     
                    <nav class="navbar site-navbar offcanvas-active navbar-expand-lg px-0"> 
                        <!-- START Logo header -->                         
                        <div class="brand-logo mr-8"> 
                        <?php
                        if(class_exists('ReduxFramework') and class_exists('Boostify_Header_Footer_Builder')){ ?> 
                        <!-- light version logo (logo must be black)-->
                        <a href="<?php echo esc_url(get_home_url()); ?>"> 
                        <img src="<?php echo esc_url($emyuiredux['opt29']['url']); ?>" alt="<?php get_bloginfo( 'name' ); ?>" class="light-version-logo"/>
                        <!-- Dark version logo (logo must be White)-->
                        <img src="<?php echo esc_url($emyuiredux['opt2']['url']); ?>" alt="<?php get_bloginfo( 'name' ); ?>" class="dark-version-logo"/> 
                        </a> 
                        <?php }else{ ?>
                         <!-- light version logo (logo must be black)-->
                         <a href="<?php echo esc_url(get_home_url()); ?>"> 
                        <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/image/logo-main-black.png" alt="<?php get_bloginfo( 'name' ); ?>" class="light-version-logo"/>
                        <!-- Dark version logo (logo must be White)-->
                        <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/image/logo-main-white.png" alt="<?php get_bloginfo( 'name' ); ?>" class="dark-version-logo"/> 
                        </a> 
                        <?php } ?> 

                        </div>                         
                        <!-- END Logo header -->                         
                        <!-- START header main navbar -->                         
                        <div class="collapse navbar-collapse" id="mobile-menu"> 
                            <div class="navbar-nav-wrapper nos"> 
                                <?php
                                wp_nav_menu( array(
                                    'theme_location'  => 'primary',
                                    'depth'           => 3, // 1 = no dropdowns, 2 = with dropdowns.
                                    'container'       => 'div',
                                    'container_class' => '',
                                    'container_id'    => 'bs-example-navbar-collapse-1',
                                    'menu_class'      => 'navbar-nav mr-auto',
                                    'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
                                    'walker'          => new WP_Bootstrap_Navwalker(),
                                ) );
                                ?>

                            </div>                             
                        </div>                         
                        <!-- END header main navbar -->                         
                                            
                        <!-- Mobile Menu Buttons-->                         
                        <button class="navbar-toggler btn-close-off-canvas hamburger-icon border-0" type="button" data-toggle="collapse" data-target="#mobile-menu" aria-controls="mobile-menu" aria-expanded="false" aria-label="Toggle navigation"> 
                            <span class="hamburger hamburger--squeeze js-hamburger"> <span class="hamburger-box"> <i class="feather icon-menu"></i> <i class="feather icon-x"></i> </span> </span> 
                        </button>                         
                        <!--/ END Mobile Menu Buttons -->                         
                    </nav>                     
                    <!-- end navbar -->                     
                </div>                 
            </header>             
            <!-- END template header -->   
<?php endif; ?>
                   
            <!-- Hero Area -->             
            <div class="colsite">