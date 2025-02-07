<div class="container emyui-container">
	<div class="row justify-content-start mega-menu-header emyui-domain">
		<div class="col-lg-6 col-md-6 col-sm-6 mb-9 pr-md-0">
			<h4 class="title text-center text-white">
				<?php _e('Instantly name your website with our AI generator', 'emyui'); ?>
			</h4>
		</div>
		<div class="col-lg-6 col-md-6 col-sm-6 mb-9 pr-md-0">
			<h4 class="title text-center text-white">
				<?php _e('Your domain is your online identity. Choose one that makes a lasting impression', 'emyui'); ?>
			</h4>
		</div>
	</div>
</div>
<div class="row justify-content-start mega-menu-header emyui-domain">
	<a class="toggle-domain-s-t d-inline-block col-md-6 active-link-border pl-lg-10 pt-lg-8 pb-lg-5 pl-6 pt-4 pb-4 rounded-top-left-8" href="#">
		<h3 class="coodiv-text-8 title color-blackish-blue text-left w-100 d-block text-white">
			<span><?php _e('FIND A DOMAIN', 'emyui'); ?></span> 
		</h3>
	</a>
	<a class="toggle-domain-s-t d-md-inline-block d-none col-md-6 not-active-link-border pl-lg-10 pt-lg-8 pb-lg-5 pl-6 pt-4 pb-4 rounded-top-right-8" href="#">
		<h3 class="coodiv-text-8 title color-blackish-blue text-left w-100 d-block text-white">
			<span><?php _e('GENERATE A DOMAIN WITH AI', 'emyui'); ?></span>
		</h3>
	</a>
</div>
<div class="px-lg-10 pb-lg-10 pt-lg-13 pt-10 px-5 pb-8 mega-menu-body emyui-domain-panel">
	<div class="row justify-content-center">
		<div class="col-xl-12 col-lg-7 col-md-8 col-sm-11">
			<div class="domain-search-form mb-8">
				<form action="" method="post" name="emyui-domain-search">
					<div class="form-group position-relative text-lg-left text-center">
						<div class="emyui-domain-field-wrap">
							<input class="form-control coodiv-text-9 border-separate mb-lg-6 mb-2 min-height-px-64" type="text" id="domain" size="20" name="domain" placeholder="<?php _e('Enter your domain name', 'emyui'); ?>">
							<button type="submit" type="submit" class="form-btn btn btn-primary mr-2 coodiv-abs-md-cr min-height-px-50 w-100 w-md-auto emyui-btn-search-domain"><?php _e('Search', 'emyui'); ?>
								<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none;"></span>
	                            <span class="sr-only">Loading...</span>
							</button>
						</div>
					</div>
					<span class="emyui-error-msg error"></span>
				</form>
			</div>
			<div class="form-bottom excerpt text-center"></div>
		</div>
	</div>
</div>
<div class="px-lg-10 pb-lg-10 pt-lg-13 pt-10 px-5 pb-8 mega-menu-body emyui-domai-ai-panel hidden">
	<div class="row justify-content-center">
		<div class="col-xl-12 col-lg-7 col-md-8 col-sm-11">
			<div class="domain-search-form mb-8">
				<form action="" method="post">
                    <div class="form-group position-relative text-lg-left text-center">
                        <textarea class="form-control coodiv-text-9 border-separate mb-lg-6 mb-2 min-height-px-150" 
                                  id="domain-search" 
                                  name="domain_query" 
                                  placeholder="Enter your search here" 
                                  rows="4"></textarea>
                        <div class="text-right small text-muted">
                            <span id="char-count">0</span>/250
                        </div>
                        <button type="submit" class="btn btn-warning btn-lg w-100 mt-3">
                            <?php _e('GENERATE DOMAIN', 'emyui'); ?>
                        </button>
                    </div>
                </form>
			</div>
			<div class="form-bottom excerpt text-center"></div>
		</div>
	</div>
</div>
