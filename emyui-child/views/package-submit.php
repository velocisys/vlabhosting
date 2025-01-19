<div class="container">
    <div class="row justify-content-center">
        <div class=";">
            <div class="hero-content dark-mode-texts mb-15 mb-lg-30 text-center text-md-center">
                <h4 class="coodiv-text-12 text-uppercase mb-3 coodiv-color-white-opacity-7">
                    <?php _e('EMYUI TEMPLATE', 'emyui'); ?>   
                </h4>
                <h1 class="coodiv-text-4 text-white d-block mb-0"></h1>
                <h1 class="coodiv-text-4 position-relative mb-5">
                    <span id="typed" class="strike-bottom green d-inline-block"><?php _e('for your web hosting company', 'emyui'); ?></span>
                    <span class="typed-cursor" aria-hidden="true">|</span></h1>
                <p data-i18n="[html]genaral.homapgesubtitle" class="coodiv-text-9 px-7 mb-0 coodiv-color-white-opacity-7 mb-10"></p>
                <form class="domain-header-search-form d-flex flex-wrap bg-white" action="" method="post">
                    <div class="single-input w-100 w-sm-50 w-lg-35 py-4 col-md-8 pr-lg-0 emyui-domain-choose">
                        <label class="emyui-label">
                            <input type="radio" name="emyui_domain" class="emyui-domain" checked="checked" value="new">
                            <?php _e('New Domain', 'emyui'); ?>
                        </label>
                        <label class="emyui-label">
                            <input type="radio" name="emyui_domain" class="emyui-domain" value="existing">
                            <?php _e('Existing Domain', 'emyui'); ?>
                        </label>
                    </div>
                    <div class="single-input w-100 w-sm-50 w-lg-35 py-4 col-md-8 pr-lg-0 emyui-domain-search">
                        <input type="text" class="inputdomainsearch" placeholder="eg. example.com" autocapitalize="none" name="query" size="20">
                    </div>
                    <div class="single-input w-100 w-sm-50 w-lg-35 py-4 col-md-2 pr-lg-0 emyui-domain-tdls">
                        <select name="emyui_domain_tlds">
                            <?php
                                $domains = emyui_domain();
                                if(is_array($domains) && !empty($domains)){
                                    foreach ($domains as $key => $domain) {
                                        ?>
                                            <option value="<?php echo $key; ?>"><?php _e($domain, 'emyui'); ?></option>
                                        <?php
                                    }
                                } 
                            ?>
                        </select>
                    </div>
                    <div class="single-input w-100 w-lg-30 d-flex align-items-center justify-content-center border-0 pb-4 pt-lg-4 pt-0 pl-lg-0 col-md-4">
                        <button class="btn btn-primary search-btn rounded-right-10-0 full-border-radius-10-sm" type="submit">
                            <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true" style="display:none;"></span>
                            <span class="sr-only">Loading...</span>
                            <?php _e('Search', 'emyui'); ?>
                        </button>
                    </div>
                </form>
                <i class="feather fe-icon"></i>
            </div>
        </div>
    </div>
</div>