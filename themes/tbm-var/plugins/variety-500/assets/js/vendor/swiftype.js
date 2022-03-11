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

  total: {
    separator: ",",
  },

  pagination: {
    inner_window: 2,
  },

  result: {
    wrapper_class_name: "l-search__list",
    result_class: "l-search__item",
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
      per_page: 24, // The default page number
      spelling: "strict",
      filters: {
        page: {
          content_type: "Variety 500",
        },
      },
      sort_direction: {}, // Defaults to sorting by relevancy descending
    },
    autocomplete: {
      apiURL: "https://api.swiftype.com/api/v1/public/engines/suggest.json",
      per_page: 5,
    },
  },
};

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

              if ("undefined" !== typeof ga) {
                ga("send", "pageview", current_search_url);
              }
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
  split_country: function (input) {
    var str_input = String(input);

    return str_input ? str_input.split("|") : str_input;
  },
});
