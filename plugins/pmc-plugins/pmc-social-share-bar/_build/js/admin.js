function sort_li(e,r){return parseInt(jQuery(e).data("position"),0)>parseInt(jQuery(r).data("position"),0)}jQuery(function(){PMC_Social_Share_Bar_Admin={settings:{ajax_url:pmc_social_share_bar_options.url,pmc_social_share_bar_nonce:pmc_social_share_bar_options.pmc_social_share_bar_nonce,current_order:pmc_social_share_bar_options.pmc_social_share_order},save:function(){var e=jQuery("#pmc-post-type").val(),r=[],i=[];jQuery(".spinner").css("visibility","visible"),jQuery("#primary-icons").find("li").each(function(){var e=jQuery(this).attr("id");void 0!==e&&r.push(e)}),jQuery("#secondary-icons").find("li").each(function(){var e=jQuery(this).attr("id");void 0!==e&&i.push(e)}),jQuery.ajax({type:"post",url:this.settings.ajax_url,data:{action:"save_order",post_type:e,primary_icons:r,secondary_icons:i,pmc_social_share_bar_nonce:this.settings.pmc_social_share_bar_nonce},complete:function(){jQuery(".spinner").css("visibility","hidden")}})},get:function(){var e=jQuery(".spinner"),r=jQuery("#pmc-post-type").val(),i=this;e.css("visibility","visible"),i.disable_sorting(),jQuery.ajax({type:"post",url:this.settings.ajax_url,data:{action:"get_order",post_type:r,pmc_social_share_bar_nonce:this.settings.pmc_social_share_bar_nonce},success:function(r){if(i.enable_sorting(),e.css("visibility","hidden"),!r.success)return!1;var s=r.data;i.reorder(s)},complete:function(){i.enable_sorting(),e.css("visibility","hidden")}})},reset:function(){var e=jQuery("#pmc-post-type").val(),r=this;jQuery(".spinner").css("visibility","visible"),r.disable_sorting(),jQuery.ajax({type:"post",url:this.settings.ajax_url,data:{action:"reset_order",post_type:e,pmc_social_share_bar_nonce:this.settings.pmc_social_share_bar_nonce},success:function(e){if(r.enable_sorting(),!e.success)return!1;var i=e.data;r.reorder(i)},complete:function(){r.enable_sorting(),jQuery(".spinner").css("visibility","hidden")}})},setup:function(){jQuery("#pmc-post-type").val("default"),this.get()},reorder:function(e){var r=0,i=0;for(r in e.primary){var s=jQuery("#"+e.primary[r]).clone();s=s.data("position",r+1),jQuery("#"+e.primary[r]).remove(),jQuery("#primary-icons").append(s)}for(i in e.secondary){var s=jQuery("#"+e.secondary[i]).clone();s=s.data("position",i+1),jQuery("#"+e.secondary[i]).remove(),jQuery("#secondary-icons").append(s)}jQuery("#primary-icons li").sort(sort_li).appendTo("#primary-icons"),jQuery("#secondary-icons li").sort(sort_li).appendTo("#secondary-icons")},init_sortable:function(){var e=this;jQuery("#primary-icons").sortable({opacity:.6,axis:"x",revert:!0,items:".share-buttons-sortables",connectWith:".dropme",cursor:"pointer",receive:function(r,i){jQuery("#primary-icons").children("li").length<pmc_social_share_bar_options.min_primary_count||jQuery("#primary-icons").children("li").length>pmc_social_share_bar_options.max_primary_count?jQuery(i.sender).sortable("cancel"):e.add_icon_to_list(i,this)}}),jQuery("#secondary-icons").sortable({opacity:.6,axis:"y",revert:!0,items:".share-buttons-sortables",connectWith:".dropme",cursor:"pointer",receive:function(r,i){jQuery("#primary-icons").children("li").length<pmc_social_share_bar_options.min_primary_count?jQuery(i.sender).sortable("cancel"):e.add_icon_to_list(i,this)}})},enable_sorting:function(){jQuery("#primary-icons").sortable("enable"),jQuery("#secondary-icons").sortable("enable")},disable_sorting:function(){jQuery("#primary-icons").sortable("disable"),jQuery("#secondary-icons").sortable("disable")},add_icon_to_list:function(e,r){jQuery(e.item).fadeOut(function(){"secondary-icons"===jQuery(r).attr("id")?jQuery(e.item).find("span").removeClass("primary-hide"):jQuery(e.item).find("span").addClass("primary-hide"),jQuery(e.item).css("display","inline-block"),jQuery(e.item).fadeIn()})}}}),jQuery(document).ready(function(){PMC_Social_Share_Bar_Admin.init_sortable(),jQuery("#primary-icons, #secondary-icons").disableSelection(),jQuery("#pmc-social-share-bar-save").on("click",function(e){e.preventDefault(),PMC_Social_Share_Bar_Admin.save()}),jQuery("#pmc-social-share-bar-reset").on("click",function(e){e.preventDefault(),PMC_Social_Share_Bar_Admin.reset()}),jQuery("#pmc-post-type").on("change",function(){PMC_Social_Share_Bar_Admin.get()}),PMC_Social_Share_Bar_Admin.setup()});