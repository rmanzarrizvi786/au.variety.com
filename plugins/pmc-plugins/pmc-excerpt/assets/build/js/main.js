!function(a){var b={init:function(){var b,c,d,e,f,g=a("[name=excerpt]");"undefined"!=typeof pmcExcerptConfig&&0!==g.length&&(b=pmcExcerptConfig.pmc_excerpt_limit,c=pmcExcerptConfig.pmc_excerpt_prevent,d=a("<div/>").css({width:"99%"}),e=a("<span/>").addClass("excerpt-chars").text("0"),f=a("<span/>").text(b),d.append(e).append(" / ").append(f).append(" Characters"),d.addClass("excerpt-char-count").insertAfter(g),e.text(g.val().length),"enable"===c&&g.attr("maxlength",b),g.on("input propertychange",function(){var a=g.val().length;"enable"===c&&a>b||e.text(a)}))}};a(document).ready(function(){b.init()})}(jQuery);