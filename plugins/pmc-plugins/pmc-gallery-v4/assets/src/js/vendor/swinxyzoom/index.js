/**
 * Scripts for swinxyzoom.
 *
 * First the zoom featured was created using react-image-magnify package however it was not as smooth as swinxyzoom and
 * did not have the same features as we currently have on WWD, hence it was created again using the same library used on WWD.
 *
 * window, dock, slippy, lens are for different features. We need only the window feature.
 *
 * @see example http://okb.us/wp-content/plugins/swinxyzoom/demo/index.html
 */

// Gestures
import './gestures/touch';
import './gestures/gesture-scale';
import './gestures/gesture-tap';
import './gestures/helper-bound';

import './zoom';
import './window';
