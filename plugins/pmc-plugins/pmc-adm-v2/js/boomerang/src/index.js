/* globals pmc */
import pmcDisplayAds from './modules/display-ads.js';
import pmcAdManager from './modules/pmc_admanager.js';
import pmcSkin from './modules/skin.js';
import sourcebuster from './modules/sourcebuster.js';
import prerollPlayer from './modules/preroll-player.js';

/* global blogherads */
blogherads.adq = blogherads.adq || [];
window.pmc = window.pmc || {};
pmc.sbjs = new sourcebuster();
pmc.displayAds = new pmcDisplayAds();
window.pmc_admanager = new pmcAdManager();
pmc.prerollPlayer = new prerollPlayer();
blogherads.adq.push(function() {
    pmc.sbjs.init();
    pmc.displayAds.init();
    pmc.skinAds = new pmcSkin();
    pmc.skinAds.init();
    pmc.prerollPlayer.init();
});
