/**
 * Isset functionality for multi-dimensional objects
 *
 * Looks recursively for the given property within the given object
 *
 * @param object   obj  The object to test
 * @param property prop The property within the object to test
 *
 * @returns boolean True if the property is set, false otherwise.
 */
SwiftypeComponents.issetRecursive = function (obj, prop) {
  var parts = prop.split("."),
    l = parts.length;

  for (var i = 0; i < l; i++) {
    var part = parts[i];

    if (obj !== null && typeof obj === "object" && part in obj) {
      obj = obj[part];
    } else {
      return false;
    }
  }

  return true;
};

SwiftypeComponents.config = {
  small_search_form: {
    placeholder: SwiftypeConfigs.trans.search,
    button_label: SwiftypeConfigs.trans.search_button,
    input_field_id: "small_search_form",
    onsubmit: [
      {
        type: "clear_all",
      },
      {
        type: "add_param",
        data: {
          field: "q",
        },
        update_results: true,
        reset_pagination: true,
      },
    ],
    redirect_to: {
      path: SwiftypeConfigs.redirect_to + "#?",
      parameters: [
        {
          urlKey: "q",
          argumentKey: "q",
        },
      ],
    },
    autocomplete: {
      min_characters: 3,
      sections: [],
    },
  },

  menu_search_form: {
    placeholder: SwiftypeConfigs.trans.search,
    button_label: SwiftypeConfigs.trans.search_button,
    input_field_id: "menu_search_form",
    onsubmit: [
      {
        type: "clear_all",
      },
      {
        type: "add_param",
        data: {
          field: "q",
        },
        update_results: true,
        reset_pagination: true,
      },
    ],
    redirect_to: {
      path: SwiftypeConfigs.redirect_to + "#?",
      parameters: [
        {
          urlKey: "q",
          argumentKey: "q",
        },
      ],
    },
    autocomplete: {
      min_characters: 3,
      sections: [],
    },
  },

  search_form: {
    placeholder: SwiftypeConfigs.trans.search,
    button_label: SwiftypeConfigs.trans.search_button,
    onsubmit: [
      {
        type: "clear_all",
      },
      {
        type: "add_param",
        data: {
          field: "q",
        },
        update_results: true,
        reset_pagination: true,
      },
    ],
    redirect_to: {
      path: SwiftypeConfigs.redirect_to + "#?",
      parameters: [
        {
          urlKey: "q",
          argumentKey: "q",
        },
      ],
    },
    autocomplete: {
      min_characters: 3,
      sections: [],
    },
  },

  misspelling: {
    store_name: "search",
  },

  routing: {
    onload: function (params) {
      return [
        {
          type: "add_param",
          data: {
            field: "q",
            value: params.q,
          },
          update_results: true,
        },
      ];
    },
  },

  sort: {
    sortables: [
      {
        label: SwiftypeConfigs.trans.reference,
        value: "_score desc",
      },
      {
        label: SwiftypeConfigs.trans.pub_date_new,
        value: "published_at desc",
      },
      {
        label: SwiftypeConfigs.trans.pub_date_old,
        value: "published_at asc",
      },
      {
        label: SwiftypeConfigs.trans.most_commented,
        value: "comment_count desc",
      },
    ],
    default:
      (SwiftypeConfigs.sort_field || "published_at") +
      " " +
      (SwiftypeConfigs.sort_direction || "desc"),
  },

  total: {
    separator: ",",
  },

  content_type_facet: {
    title: SwiftypeConfigs.trans.content_type,
    clear_link: SwiftypeConfigs.trans.clear,
    field: "content_type",
    limit: 7,
    disable_checkbox: true,
    sort_by: ["count", "name"], // name, count, selected, or function
    sort_by_direction: [false, true], // false = desc, true = asc
  },

  topics_facet: {
    title: SwiftypeConfigs.trans.topics,
    and_clause: true,
    clear_link: SwiftypeConfigs.trans.clear,
    field: "topics",
    limit: SwiftypeComponents.issetRecursive(
      SwiftypeConfigs,
      "date_filters.topics_facet:checkbox-facet.limit"
    )
      ? SwiftypeConfigs.date_filters["topics_facet:checkbox-facet"].limit
      : 7,
    disable_checkbox: true,
    sort_by: ["count", "name"], // name, count, selected, or function
    sort_by_direction: [false, true], // false = desc, true = asc

    /**
     * Filter each individual item shown in this facet
     *
     * @param object item The current facet item
     *
     * @returns string|false The facet item name. False on failure or omission.
     */
    filter: function (item) {
      var facet_name = "topics_facet:checkbox-facet",
        item_name = item.name;

      // If there are options for the topics facet, process them
      if (
        SwiftypeComponents.issetRecursive(
          SwiftypeConfigs,
          "date_filters." + facet_name
        )
      ) {
        // Filter each item in the facet; allowing only items in the allowed_items array
        item_name = SwiftypeComponents.config.filter_allowed_facet_items(
          item_name,
          facet_name
        );

        // Filter each item in the facet; hiding items in the facet disallowed list
        item_name = SwiftypeComponents.config.filter_disallowed_facet_items(
          item_name,
          facet_name
        );
      }

      return item_name;
    },
  },

  tags_facet: {
    title: SwiftypeConfigs.trans.tags,
    clear_link: SwiftypeConfigs.trans.clear,
    field: "tags",
    limit: SwiftypeComponents.issetRecursive(
      SwiftypeConfigs,
      "date_filters.tags_facet:checkbox-facet.limit"
    )
      ? SwiftypeConfigs.date_filters["tags_facet:checkbox-facet"].limit
      : 7,
    disable_checkbox: true,
    sort_by: ["count", "name"], // name, count, selected, or function
    sort_by_direction: [false, true], // false = desc, true = asc

    /**
     * Filter each individual item shown in this facet
     *
     * @param object item The current facet item
     *
     * @returns string|false The facet item name. False on failure or omission.
     */
    filter: function (item) {
      var facet_name = "tags_facet:checkbox-facet",
        item_name = item.name;

      // If there are options for the topics facet, process them
      if (
        SwiftypeComponents.issetRecursive(
          SwiftypeConfigs,
          "date_filters." + facet_name
        )
      ) {
        // Filter each item in the facet; allowing only items in the allowed_items array
        item_name = SwiftypeComponents.config.filter_allowed_facet_items(
          item_name,
          facet_name
        );

        // Filter each item in the facet; hiding items in the facet disallowed list
        item_name = SwiftypeComponents.config.filter_disallowed_facet_items(
          item_name,
          facet_name
        );
      }

      return item_name;
    },
  },

  author_facet: {
    title: SwiftypeConfigs.author,
    and_clause: true,
    clear_link: SwiftypeConfigs.trans.clear,
    field: "author",
    limit: 7,
    disable_checkbox: true,
    sort_by: ["count", "name"], // name, count, selected, or function
    sort_by_direction: [false, true], // false = desc, true = asc
  },

  appeared_in_facet: {
    title: SwiftypeConfigs.appeared_in_print,
    clear_link: SwiftypeConfigs.trans.clear,
    field: "appeared_in_print",
    limit: 7,
    disable_checkbox: true,
    filter: function (result) {
      return "Yes" === result.name;
    },
    sort_by: ["count", "name"], // name, count, selected, or function
    sort_by_direction: [false, true], // false = desc, true = asc
  },

  date_options: {
    default_option: SwiftypeComponents.issetRecursive(
      SwiftypeConfigs,
      "date_filters.date_options:radio-options.default_option"
    )
      ? SwiftypeConfigs.date_filters["date_options:radio-options"]
          .default_option
      : 0,
    options: [
      {
        label: SwiftypeConfigs.trans.all,
        actions: [
          {
            type: "add_filter_range",
            data: {
              field: "published_at",
              from: function () {
                var date = new Date();
                date.setDate(date.getDate() - 99999999);
                return date.toISOString();
              },
              to: function () {
                var date = new Date();
                return date.toISOString();
              },
            },
            update_results: true,
            reset_pagination: true,
          },
        ],
      },
      {
        label: SwiftypeConfigs.trans.twentyfour_hours,
        actions: [
          {
            type: "add_filter_range",
            data: {
              field: "published_at",
              from: function () {
                var date = new Date();
                date.setDate(date.getDate() - 1);
                return date.toISOString();
              },
              to: function () {
                var date = new Date();
                return date.toISOString();
              },
            },
            update_results: true,
            reset_pagination: true,
          },
        ],
      },
      {
        label: SwiftypeConfigs.trans.seven_days,
        actions: [
          {
            type: "add_filter_range",
            data: {
              field: "published_at",
              from: function () {
                var date = new Date();
                date.setDate(date.getDate() - 7);
                return date.toISOString();
              },
              to: function () {
                var date = new Date();
                return date.toISOString();
              },
            },
            update_results: true,
            reset_pagination: true,
          },
        ],
      },
      {
        label: SwiftypeConfigs.trans.thirty_days,
        actions: [
          {
            type: "add_filter_range",
            data: {
              field: "published_at",
              from: function () {
                var date = new Date();
                date.setDate(date.getDate() - 30);
                return date.toISOString();
              },
              to: function () {
                var date = new Date();
                return date.toISOString();
              },
            },
            update_results: true,
            reset_pagination: true,
          },
        ],
      },
      {
        label: SwiftypeConfigs.trans.twelve_months,
        actions: [
          {
            type: "add_filter_range",
            data: {
              field: "published_at",
              from: function () {
                var date = new Date();
                date.setDate(date.getDate() - 365);
                return date.toISOString();
              },
              to: function () {
                var date = new Date();
                return date.toISOString();
              },
            },
            update_results: true,
            reset_pagination: true,
          },
        ],
      },
    ],
  },

  pagination: {
    inner_window: 2,
  },

  result: {
    wrapper_class_name: "block-group",
    result_class: "result block",
    template_id: "result",
  },

  // An array of stores to save the search results in.
  store_names: ["search", "autocomplete"],

  // Default values for all components.
  defaults: {
    store_name: "search",
  },

  conditional: {
    store_names: "search",
  },

  // The following defines defaults for the api. For more information see:
  // https://swiftype.com/documentation/searching
  store_parameters: {
    defaults: {
      apiURL: "https://api.swiftype.com/api/v1/public/engines/search.json",
      engine_key: SwiftypeConfigs.engine_key, // Replace with your engine key.
      page: 1, // Start at page 1
      document_type: "page", // "page" is for crawler based engines
      q: "", // Start with no query
      per_page: 10, // The default page number
      spelling: "strict",
      sort_direction: {
        page: SwiftypeConfigs.sort_direction || "desc", // Defaults to sorting order
      },
      sort_field: {
        page: SwiftypeConfigs.sort_field || "published_at", // Defaults to sorting field
      },
    },
    autocomplete: {
      apiURL: "https://api.swiftype.com/api/v1/public/engines/suggest.json",
      per_page: 5,
    },
  },

  /**
   * If there are specific topics listed in the allowed_items
   * only show those items. This is done by returning false by default,
   * unless one of the allowed_items is found—then that topic name is returned.
   *
   * @param string item_name  The name of the current item being displayed in a facet
   * @param string facet_name The facet item name.
   *
   * @returns false|string Returns false
   */
  filter_allowed_facet_items: function (item_name, facet_name) {
    var allowed_items = [];

    if ("" !== item_name && "" !== facet_name) {
      if (
        SwiftypeComponents.issetRecursive(
          SwiftypeConfigs,
          "date_filters." + facet_name + ".allowed_items"
        )
      ) {
        if (0 < SwiftypeConfigs.date_filters[facet_name].allowed_items.length) {
          allowed_items =
            SwiftypeConfigs.date_filters[facet_name].allowed_items;

          if (-1 === allowed_items.indexOf(item_name)) {
            item_name = false;
          }
        }
      }
    }

    return item_name;
  },

  /**
   * If there are specific topics listed in the disallowed_items
   * hide them from being shown in the facet. This is accomplished
   * by returning false if a disallowed topic is found. Otherwise the item
   * title is returned for display in the facet filter.
   *
   * @param string item_name  The name of the current item being displayed in a facet
   * @param string facet_name The facet item name.
   *
   * @returns false|string Returns false
   */
  filter_disallowed_facet_items: function (item_name, facet_name) {
    var disallowed_items = [];

    // If we have a facet for the given facet name,
    // and it contains disallowed items, and the current
    // item name is within the disallowed items, return false.
    if ("" !== item_name && "" !== facet_name) {
      if (
        SwiftypeComponents.issetRecursive(
          SwiftypeConfigs,
          "date_filters." + facet_name + ".disallowed_items"
        )
      ) {
        if (
          0 < SwiftypeConfigs.date_filters[facet_name].disallowed_items.length
        ) {
          disallowed_items =
            SwiftypeConfigs.date_filters[facet_name].disallowed_items;

          if (-1 !== disallowed_items.indexOf(item_name)) {
            item_name = false;
          }
        }
      }
    }

    return item_name;
  },
};

// Add to object if the config says so
if (
  "1" === SwiftypeConfigs.specific_dates &&
  "object" === typeof SwiftypeComponents.config.date_options.options
) {
  SwiftypeComponents.config.date_options.options.push({
    label: SwiftypeConfigs.trans.specific_dates,
    actions: [
      {
        type: "add_filter_range",
        data: {
          field: "published_at",
          from: function () {
            if (!jQuery("#specific_dates").length) {
              // Used to determine date format for jQuery UI
              var dateFormat = "yy-mm-dd";

              jQuery("<div/>", {
                id: "specific_dates",
                html:
                  "<div id='border-cut'></div><span><label for='swiftype-from'>" +
                  SwiftypeConfigs.from_str +
                  "</label><input type='text' id='swiftype-from' name='swiftype-from'></span><span><label for='swiftype-to'>" +
                  SwiftypeConfigs.to_str +
                  "</label><input type='text' id='swiftype-to' name='swiftype-to'></span><a id='specific-dates-submit'>" +
                  SwiftypeConfigs.refine_search_str +
                  "</a>",
                // Get the ID for the radio button.
              })
                .appendTo("#date_options")
                .slideDown("fast");

              var from = jQuery("#swiftype-from")
                .datepicker({
                  buttonText: "<span class='ui-icon ui-icon-calendar'></span>",
                  changeMonth: true,
                  defaultDate: "-1w",
                  numberOfMonths: 2,
                  showOn: "button",
                  beforeShow: function (textbox, instance) {
                    jQuery(".swiftype").append(
                      jQuery(this).datepicker("widget")
                    );
                  },
                })
                .on("change", function () {
                  to.datepicker("option", "minDate", getDate(this));
                  toggleClass();
                });

              var to = jQuery("#swiftype-to")
                .datepicker({
                  buttonText: "<span class='ui-icon ui-icon-calendar'></span>",
                  changeMonth: true,
                  devaultDate: null,
                  numberOfMonths: 2,
                  showOn: "button",
                  beforeShow: function (textbox, instance) {
                    jQuery(".swiftype").append(
                      jQuery(this).datepicker("widget")
                    );
                  },
                })
                .on("change", function () {
                  from.datepicker("option", "maxDate", getDate(this));
                  toggleClass();
                });

              // A method that will set the valid class on the submit button which will update its styles.
              function toggleClass() {
                if (
                  jQuery("#swiftype-to").val() &&
                  jQuery("#swiftype-from").val()
                ) {
                  jQuery("#specific-dates-submit").addClass("valid");
                } else {
                  jQuery("#specific-dates-submit").removeClass("valid");
                }
              }

              function getDate(element) {
                var date;
                try {
                  date = jQuery.datepicker.parseDate(dateFormat, element.value);
                } catch (error) {
                  date = null;
                }

                return date;
              }

              // Create an onclick event for the submit button
              jQuery("#specific-dates-submit").on("click", function () {
                if (jQuery(this).hasClass("valid")) {
                  jQuery("#date_options input:checked").click(); // Simlating click in case there are click events attached to this radio selector
                  SwiftypeComponents.stores.search.parameter.trigger(
                    "arguments_changed"
                  );
                }
              });
            }

            // If both of the datepicker calendars have a value return them.
            if (
              jQuery("#swiftype-from").val() &&
              jQuery("#swiftype-to").val()
            ) {
              var date = new Date(jQuery("#swiftype-from").val());
              return date.toISOString();
            }
          },
          to: function () {
            if (
              jQuery("#swiftype-to").val() &&
              jQuery("#swiftype-from").val()
            ) {
              var date = new Date(jQuery("#swiftype-to").val());
              return date.toISOString();
            }
          },
        },
        update_results: false,
        reset_pagination: true,
      },
    ],
  });
}

if (SwiftypeConfigs.author_list.length > 0) {
  SwiftypeComponents.config.author_facet.filter = function (author) {
    var whitelistedAuthorNames = SwiftypeConfigs.author_list;
    return whitelistedAuthorNames.indexOf(author.name) >= 0;
  };
}

if ("object" === typeof SwiftypeConfigs.custom_facet_settings) {
  for (var facet in SwiftypeConfigs.custom_facet_settings) {
    SwiftypeConfigs.custom_facet_settings[facet].filter = function (item) {
      if (
        !SwiftypeComponents.issetRecursive(
          SwiftypeConfigs,
          "custom_facet_settings." + facet + ".facet_name"
        )
      ) {
        return false;
      }
      var facet_name = SwiftypeConfigs.custom_facet_settings[facet].facet_name,
        item_name = item.name;

      // If there are options for the topics facet, process them
      if (
        SwiftypeComponents.issetRecursive(
          SwiftypeConfigs,
          "date_filters." + facet_name
        )
      ) {
        // Filter each item in the facet; allowing only items in the allowed_items array
        item_name = SwiftypeComponents.config.filter_allowed_facet_items(
          item_name,
          facet_name
        );

        // Filter each item in the facet; hiding items in the facet disallowed list
        item_name = SwiftypeComponents.config.filter_disallowed_facet_items(
          item_name,
          facet_name
        );
      }

      return item_name;
    };
  }
  jQuery.extend(
    SwiftypeComponents.config,
    SwiftypeConfigs.custom_facet_settings
  );
}

if (SwiftypeConfigs.autocomplete.tags.include) {
  var st_tags = {
    store_name: "autocomplete",
    title: SwiftypeConfigs.autocomplete.tags.name,
    template_id: "ac_tag",
    onComplete: function (result, url) {
      window.location = result.url;
    },
    preprocess: function (results) {
      var tags = [];
      var lodash = SwiftypeComponents._();
      lodash.each(results, function (result) {
        tags = tags.concat(result.tags);
      });
      tags = lodash
        .chain(tags)
        .filter(function (tag) {
          return !("undefined" === typeof tag || "undefined" === tag);
        })
        .countBy()
        .pairs()
        .sortBy(1)
        .reverse()
        .take(4)
        .value();
      return lodash.map(tags, function (tag) {
        return {
          name: tag[0],
          url: SwiftypeConfigs.home_url + "tag/" + tag[0],
        };
      });
    },
  };

  SwiftypeComponents.config.search_form.autocomplete.sections.push(st_tags);
  SwiftypeComponents.config.small_search_form.autocomplete.sections.push(
    st_tags
  );
  SwiftypeComponents.config.menu_search_form.autocomplete.sections.push(
    st_tags
  );

  /**
   * Swiftype only allows names within post meta tag.
   *
   * Example:
   * <meta class="swiftype" name="tags" data-type="string" content="Lord &amp; Taylor" />
   *
   * This is problematic as we cannot reliably build a link from just the name.
   * To ensure this works and limit calls to API, we will on-the-fly reach out to
   * WordPress API to get tag information and direct to correct page. Since this is
   * all JavaScript powered, there's no negative affect to SEO.
   */
  jQuery("#swiftype-search-widget, #swiftype-search-result").on(
    "click",
    ".ac_tag",
    function (e) {
      e.preventDefault();
      e.stopPropagation();

      // Use html() because we need to match entities like &amp; against name in API results.
      var $anchor = jQuery(this).find("a"),
        default_url = $anchor.attr("href"),
        name = $anchor.html();

      /**
       * Doing a simple search should result in tag that was clicked.
       */
      jQuery.get("/wp-json/wp/v2/tags?search=" + name, function (data) {
        // If data is not an array from call, do default behavior.
        if (!Array.isArray(data)) {
          window.location = default_url;
        }

        // Run through all results, but should be the first.
        for (var i = 0; i < data.length; i++) {
          var tag = data[i];

          // Ensure we have the correct tag and use correct link from API.
          if (name === tag.name) {
            window.location = tag.link;
            return;
          }
        }

        // If all fails do default behavior.
        window.location = default_url;
      });
    }
  );
}

if (SwiftypeConfigs.autocomplete.articles.include) {
  var st_articles = {
    store_name: "autocomplete",
    title: SwiftypeConfigs.autocomplete.articles.name,
    template_id: "ac_article",
    preprocess: function (results) {
      if (
        typeof window.pmc === "object" &&
        typeof window.pmc.proxy_url === "function" &&
        window.pmc.is_proxied()
      ) {
        SwiftypeComponents._().each(results, function (result) {
          // force proxy url
          result.url = window.pmc.proxy_url(result.url, true);
        });
      }
      return results;
    },
  };

  SwiftypeComponents.config.search_form.autocomplete.sections.push(st_articles);
  SwiftypeComponents.config.small_search_form.autocomplete.sections.push(
    st_articles
  );
  SwiftypeComponents.config.menu_search_form.autocomplete.sections.push(
    st_articles
  );
}

SwiftypeComponents.debug.setLevel(0);
SwiftypeComponents.onReady(function () {
  var pageview_event_attached = false;

  // Need this event to be trigger first before any result processing take place
  if (
    typeof window.pmc === "object" &&
    typeof window.pmc.proxy_url === "function" &&
    window.pmc.is_proxied()
  ) {
    SwiftypeComponents.stores.search.result.on(
      "search_query_success",
      function () {
        SwiftypeComponents._().each(
          SwiftypeComponents.stores.search.result.records(),
          function (result) {
            result.url = window.pmc.proxy_url(result.url, true);
          }
        );
      }
    );
    SwiftypeConfigs.home_url = window.pmc.proxy_url(
      SwiftypeConfigs.home_url,
      true
    );
  }

  SwiftypeComponents.react.mountAll();
  var value = window.location.hash.substr(2);
  if (value) {
    var value_array_amp = value.split("&");
    for (var i = 0; i < value_array_amp.length; i++) {
      var value_array_eq = value_array_amp[i].split("=");
      if (
        value_array_eq &&
        "q" === value_array_eq[0] &&
        "undefined" !== typeof value_array_eq[1]
      ) {
        SwiftypeConfigs.q = value_array_eq[1];
        break;
      }
    }
  }
  if (null !== document.getElementById("st-search-form-input")) {
    document.getElementById("st-search-form-input").value = decodeURIComponent(
      SwiftypeConfigs.q
    );
  }

  SwiftypeComponents.stores.search.parameter.on(
    "arguments_changed",
    function () {
      document.body.scrollTop = 0;
      if (!pageview_event_attached) {
        pageview_event_attached = true;
        SwiftypeComponents.stores.search.result.on(
          "search_query_success",
          function () {
            var current_search_url = window.location.href.replace("#", "");
            try {
              if (
                typeof window.pmc === "object" &&
                typeof window.pmc.reverse_proxy_url === "function" &&
                window.pmc.is_proxied()
              ) {
                current_search_url =
                  window.pmc.reverse_proxy_url(current_search_url);
              }

              window.pmc = window.pmc || {};
              window.pmc.analytics = window.pmc.analytics || [];

              window.pmc.analytics.push(function () {
                window.pmc.analytics.track_pageview(
                  location.pathname + location.hash
                );
              });

              // support old GA tracking call
              if (typeof _gaq !== "undefined") {
                _gaq.push(["_trackPageview", current_search_url]);
              }
              // trigger additional page view event fire as needed
              if ("undefined" !== typeof jQuery) {
                jQuery.event.trigger({
                  type: "pmc-track-pageview",
                  url: current_search_url,
                });
              }
            } catch (ignore) {}
          }
        );
      } // if ! pageview event attached
    }
  ); // on swiftype search arguments_changed
});

Liquid.registerFilters({
  unescape: function (input) {
    var input = String(input),
      tags = ["b", "strong", "em", "i", "span"];

    for (var i = 0; i < tags.length; i++) {
      input = input
        .replace(
          new RegExp("&lt;" + tags[i] + "( .[^&gt;]*)?&gt;", "gim"),
          "<" + tags[i] + ">"
        )
        .replace(
          new RegExp("&lt;&#x2F;" + tags[i] + "&gt;", "gim"),
          "</" + tags[i] + ">"
        );
    }

    return input;
  },
  pp_array: function (collection) {
    if (SwiftypeComponents._().isArray(collection)) {
      return collection.join(", ");
    } else {
      return collection;
    }
  },
});
