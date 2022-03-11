const clonedeep = require( 'lodash.clonedeep' );

const maketing_landing_vip = clonedeep( require( './marketing-landing.prototype' ) );

maketing_landing_vip.c_lazy_image.c_lazy_image_src_url     = 'https://pmcvariety.files.wordpress.com/2013/09/chinawood-cover.jpg';
maketing_landing_vip.c_lazy_image.c_lazy_image_srcset_attr = false;
maketing_landing_vip.c_lazy_image.c_lazy_image_sizes_attr  = false;
maketing_landing_vip.c_lazy_image.c_lazy_image_link_url      = '/premier-archives-registration/';

module.exports = maketing_landing_vip;
