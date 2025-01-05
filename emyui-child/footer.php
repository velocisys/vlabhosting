<?php global $emyuiredux; ?>
            </div>

            <?php if (  function_exists( 'boostify_footer_active' ) && boostify_footer_active() ): ?>
                <?php boostify_get_footer_template(); //Custom header ?>
            <?php else: ?>
                <div class="dark-mode-texts footer-gradient-default overflow-hidden position-relative"> 
                <svg class="bg-wave-box-end" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"> 
                    <path fill="#fff" fill-opacity="1" d="M0,64L80,101.3C160,139,320,213,480,213.3C640,213,800,139,960,117.3C1120,96,1280,128,1360,144L1440,160L1440,320L1360,320C1280,320,1120,320,960,320C800,320,640,320,480,320C320,320,160,320,80,320L0,320Z"></path>                     
                </svg>                 
                <div class="container"> 
                    <div class="footer-section dark-mode-texts"> 
                        <div class="container"> 
                            <?php if(class_exists('ReduxFramework') and class_exists('Boostify_Header_Footer_Builder')){ ?>   
                            <div class="footer-top pt-15 pt-lg-25 pb-lg-19"> 
                                <div class="row"> 
                                    <div class="col-6 col-lg-4"> 
                                        <div class="footer-block mb-13 mb-lg-9"> 
                                            <div class="brand-logo mb-10"> 
                                                <a href="#"><img src="<?php echo esc_url($emyuiredux['opt2']['url']); ?>" alt="<?php get_bloginfo( 'name' ); ?>" class="dark-version-logo"/></a> 
                                            </div>
                                            <h4 class="block-title coodiv-text-7 mb-7 position-relative">
                                                <?php _e('About US', 'emyui'); ?></h4>                                             
                                            <p><?php echo esc_html($emyuiredux['opt-text3']); ?></p> 
                                            <ul class="footer-contact-list list-unstyled"> 
                                                <li> 
                                                    <span class="badge coodiv-badge badge-warning rounded-pill coodiv-text-12 position-relative"><i class="far fa-envelope"></i><?php echo esc_html($emyuiredux['opt-text5']); ?></span> 
                                                </li>                                                 
                                                <?php /*<li> 
                                                    <span class="badge coodiv-badge badge badge-info rounded-pill coodiv-text-12 position-relative"><i class="fas fa-phone"></i><?php echo esc_html($emyuiredux['opt-text6']); ?></span> 
                                                </li> */?>                                                 
                                            </ul>                                             
                                        </div>                                         
                                    </div>                                     
                                    <div class="col-6 col-lg-2"> 
                                        <div class="footer-block mb-13 mb-lg-9"> 
                                            <h4 class="block-title coodiv-text-7 mb-7 position-relative"><?php echo esc_html($emyuiredux['opt-text66']); ?></h4> 
                                            <?php
                                                wp_nav_menu( array(
                                                    'menu'         => 'footer-one', 
                                                    'depth'           => 1,
                                                    'menu_class'   => 'footer-list list-unstyled', 
                                                    
                                                ) );
                                                
                                                ?>                                           
                                        </div>                                         
                                    </div>                                     
                                    <div class="col-6 col-lg-3 pl-lg-15"> 
                                        <div class="footer-block mb-13 mb-lg-9"> 
                                            <h4 class="block-title coodiv-text-7 mb-7 position-relative"><?php echo esc_html($emyuiredux['opt-text77']); ?></h4> 
                                            <?php
                                                wp_nav_menu( array(
                                                    'menu'         => 'footer-two', 
                                                    'depth'           => 1,
                                                    'menu_class'   => 'footer-list list-unstyled', 
                                                    
                                                ) );
                                                
                                                ?>                                           
                                        </div>                                         
                                    </div>                                     
                                    <div class="col-6 col-lg-3 pl-lg-15"> 
                                        <div class="footer-block mb-13 mb-lg-9"> 
                                            <h4 class="block-title coodiv-text-7 mb-7 position-relative"><?php echo esc_html($emyuiredux['opt-text88']); ?></h4> 
                                                <?php
                                                wp_nav_menu( array(
                                                    'menu'         => 'footer-three', 
                                                    'depth'           => 1,
                                                    'menu_class'   => 'footer-list list-unstyled', 
                                                    
                                                ) );
                                                
                                                ?>
                                                                                    
                                        </div>                                         
                                    </div>                                     
                                </div>                                 
                            </div>   
                            <?php } ?>             
                            <?php if(class_exists('ReduxFramework') and class_exists('Boostify_Header_Footer_Builder')){ ?>               
                            <div class="bottom-footer-area border-top pt-9 pb-8"> 
                                <div class="row align-items-center"> 
                                    <div class="col-lg-8"> 
                                        <p class="copyright-text coodiv-text-11 mb-6 mb-lg-0 coodiv-text-color-opacity text-center text-lg-left"><?php echo esc_html($emyuiredux['opt-text7']); // phpcs:ignore ?></p> 
                                    </div>                                     
                                    <div class="col-lg-4 text-center text-lg-right"> 
                                        <ul class="payment-getway list-unstyled mb-0"> 
                                            <li class="ml-1">
                                                <img src="<?php echo esc_url($emyuiredux['opt21']['url']); ?>" alt="<?php get_bloginfo( 'name' ); ?>"/>
                                            </li>                                             
                                            <li class="ml-1">
                                                <img src="<?php echo esc_url($emyuiredux['opt22']['url']); ?>" alt="<?php get_bloginfo( 'name' ); ?>"/>
                                            </li>                                             
                                            <li class="ml-1">
                                                <img src="<?php echo esc_url($emyuiredux['opt23']['url']); ?>" alt="<?php get_bloginfo( 'name' ); ?>"/>
                                            </li>                                             
                                            <li class="ml-1">
                                                <img src="<?php echo esc_url($emyuiredux['opt24']['url']); ?>" alt="<?php get_bloginfo( 'name' ); ?>"/>
                                            </li>                                             
                                            <li class="ml-1">
                                                <img src="<?php echo esc_url($emyuiredux['opt25']['url']); ?>" alt="<?php get_bloginfo( 'name' ); ?>"/>
                                            </li>                                             
                                            <li class="ml-1">
                                                <img src="<?php echo esc_url($emyuiredux['opt26']['url']); ?>" alt="<?php get_bloginfo( 'name' ); ?>"/>
                                            </li>                                             
                                        </ul>                                      
                                    </div>                                     
                                </div>                                 
                            </div>   
                            <?php }else{ ?>
                            <div class="bottom-footer-area border-top pt-9 pb-8"> 
                                <div class="row align-items-center"> 
                                    <div class="col-lg-8"> 
                                        <p class="copyright-text coodiv-text-11 mb-6 mb-lg-0 coodiv-text-color-opacity text-center text-lg-left"><?php echo esc_html__('© 2022 Copyright, All Right Reserved, Made with lots of coffee','emyui'); // phpcs:ignore ?></p> 
                                    </div>                                     
                                    <div class="col-lg-4 text-center text-lg-right"> 
                                        <ul class="payment-getway list-unstyled mb-0"> 
                                            <li class="ml-1">
                                                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/image/payment/visa.png" alt="<?php get_bloginfo( 'name' ); ?>"/>
                                            </li>                                             
                                            <li class="ml-1">
                                                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/image/payment/mastercard.png" alt="<?php get_bloginfo( 'name' ); ?>"/>
                                            </li>                                             
                                            <li class="ml-1">
                                                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/image/payment/discover.png" alt="<?php get_bloginfo( 'name' ); ?>"/>
                                            </li>                                             
                                            <li class="ml-1">
                                                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/image/payment/amex.png" alt="<?php get_bloginfo( 'name' ); ?>"/>
                                            </li>                                             
                                            <li class="ml-1">
                                                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/image/payment/jcb.png" alt="<?php get_bloginfo( 'name' ); ?>"/>
                                            </li>                                             
                                            <li class="ml-1">
                                                <img src="<?php echo esc_url( get_template_directory_uri() ); ?>/image/payment/maestro.png" alt="<?php get_bloginfo( 'name' ); ?>"/>
                                            </li>                                             
                                        </ul>                                         
                                    </div>                                     
                                </div>                                 
                            </div>  
       
                       
                            <?php } ?>                       
                        </div>                         
                    </div>                     
                </div>                 
            </div>             
            </div>  
            <?php endif; ?>

                   


   
        <!-- Vendor Scripts -->                  
        <!-- Plugin's Scripts -->   
                                                                                   
        <!-- Activation Script -->                  
        <?php wp_footer(); ?>
    </body>     
</html>
