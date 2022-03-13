import pmc_gdpr_utils from './pmc_gdpr_utils';

/**
 * Module that saves data to local storage but expires it afteer a certain
 * period of time, forming a local cache.
 */
const retrieved = {};

const w = window;

/**
 * Set data in the browser local storage.
 * @param {string} name
 * @param {object} data
 * @param {number} expires
 */
function setData (name, data, expires) {
  const cache = { 'expires': expires, 'data': data };

  retrieved[name] = cache;
  try {
    w.localStorage.setItem('pmc.cache.' + name, JSON.stringify(cache));
  } catch (e) { /* Do nothing. */ }
}

/**
 * Get data from the browser local storage.
 * @param {string} name
 * @returns {object} data
 */
function getData (name) {
  let cached = retrieved[name];

  if (!cached) {
    try {
      cached = JSON.parse(w.localStorage.getItem('frisbee.cache.' + name));
      retrieved[name] = cached;
    } catch (e) { /* Do nothing. */ }
  }

  // Expires == 0 or null means it never expires.
  if (cached && (!cached.expires || cached.expires > pmc_gdpr_utils.getNow())) {
    return cached.data;
  }

  return null;
}

/**
 * Delete data from the browser local storage.
 * @param {string} name
 */
function dropData (name) {
  delete retrieved[name];

  try {
    w.localStorage.removeItem('frisbee.cache.' + name);
  } catch (e) { /* Do nothing. */ }
}

// Watch for changes to the cache in other windows, and store the changes
// locally in this window as well so we don't go to the disk if we don't
// have to.
pmc_gdpr_utils.attachHandler(w, 'storage', function (e) {
  if (e.key.indexOf('frisbee.cache.') === 0) {
    delete retrieved[e.key];

    try {
      retrieved[e.key] = JSON.parse(e.newValue);
    } catch (e) { /* Do nothing. */ }
  }
});

export default {
  setData: setData,
  getData: getData,
  dropData: dropData
};
