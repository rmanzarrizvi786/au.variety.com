<div class="nav-network-wrap" style="width: 100%; z-index: 11;">
    <a href="#" class="l_toggle_menu_network">
        <img src="https://cdn.thebrag.com/tbm/The-Brag-Media-300px-light.png" alt="The BRAG Media" style="height: 15px !important;">
        <i class="caret-down" style="font-size: 12px"></i>
    </a>
    <div class="brands__sub-menu is-open" id="menu-network" style="display: none;">
        <div class="" id="brands_wrap">
            <div class="brands__grid brands__wrap our-brands">
                <?php foreach (brands() as $brand => $brand_details) : ?>
                    <div class="brands-box">
                        <a href="<?php echo $brand_details['link']; ?>" title="<?php echo $brand_details['title']; ?>" target="_blank">
                            <img src="https://images.thebrag.com/common/brands/<?php echo $brand_details['logo_name']; ?>-light.<?php echo isset($brand_details['ext']) ? $brand_details['ext'] : 'jpg'; ?>" alt="<?php echo $brand_details['title']; ?>" style="<?php echo isset($brand_details['width']) ? 'width: ' . $brand_details['width'] . 'px;' : ''; ?>">
                        </a>
                    </div>
                <?php endforeach;
                $brands_network = brands_network();
                ksort($brands_network);
                foreach ($brands_network as $brand => $brand_details) : ?>
                    <div class="brands-box">
                        <a href="<?php echo $brand_details['link']; ?>" title="<?php echo $brand_details['title']; ?>" target="_blank" class="d-block p-2" rel="noreferrer">
                            <img src="https://images.thebrag.com/common/pubs-white/<?php echo str_replace(' ', '-', strtolower($brand_details['title'])); ?>.png" alt="<?php echo $brand_details['title']; ?>" style="<?php echo isset($brand_details['width']) ? 'width: ' . $brand_details['width'] . 'px;' : ''; ?>">
                        </a>
                    </div>
                <?php endforeach; ?>
            </div><!-- .our-brands -->

            <?php if (0) : ?>
                <div class="text-center text-white py-3 my-0 text-network-pmc" style="border-bottom: 1px solid #2b2b2b; font-size: 1.5rem; text-align: center; color: #fff; padding: 1rem; width: 100%;">AUSTRALIAN NETWORK IN PARTNERSHIP WITH <a href="https://pmc.com/" target="_blank"><img src="https://images.thebrag.com/common/pubs-white/pmc.svg" alt="PMC" title="PMC" width="100" style="height: 1.1rem; margin-top: -5px; margin-left: 5px;"></a></div>

                <div class="brands__grid brands__wrap network-brands flex-fill">
                    <?php foreach (brands_network() as $brand => $brand_details) : ?>
                        <div class="brands-box">
                            <a href="<?php echo $brand_details['link']; ?>" title="<?php echo $brand_details['title']; ?>" target="_blank">
                                <img src="https://images.thebrag.com/common/pubs/<?php echo $brand_details['logo_name']; ?>.jpg" alt="<?php echo $brand_details['title']; ?>">
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div><!-- .network-brands -->
            <?php endif; ?>
        </div>
    </div>
</div>