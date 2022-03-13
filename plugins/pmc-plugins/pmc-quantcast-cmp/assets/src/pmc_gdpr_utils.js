var pmc_gdpr_utils = {
  // RFC4122 complaint UUID
  uuid: function () {
    var uuid = '';
    var i;
    var random;

    for (i = 0; i < 32; i++) {
      random = Math.random() * 16 | 0;

      if (i === 8 || i === 12 || i === 16 || i === 20) {
        uuid += '-';
      }

      uuid += (i === 12
        ? 4
        : (i === 16
          ? (random & 3 | 8)
          : random
        )
      ).toString(16);
    }

    return uuid;
  },

  attachHandler: function (el, type, f) {
    if (el.addEventListener) {
      el.addEventListener(type, f, false);
    } else if (el.attachEvent) {
      el.attachEvent('on' + type, f);
    }
  },

  generateUUID: function () {
    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
      // eslint-disable-next-line no-bitwise
      const r = Math.random() * 16 | 0;

      const v = c === 'x' ? r : ((r & 0x3) | 0x8);

      return v.toString(16);
    });
  },

  getNow: function () {
    return Date.now ? Date.now() : (new Date()).getTime();
  },

  getXhrOptions: function (options) {
    return {
      method: options.method || 'POST',
      url: options.url,
      headers: options.headers || {
        'Content-type': 'application/json; charset=utf-8'
      }
    };
  },

  getMeta: function (options) {
    return {
      id: this.uuid(),
      namespace: options.namespace
    };
  },

  getRequestData: function (data, meta) {
    return JSON.stringify({
      payload: data,
      meta: meta
    });
  }
};

export default pmc_gdpr_utils;
