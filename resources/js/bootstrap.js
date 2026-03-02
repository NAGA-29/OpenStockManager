import _ from 'lodash';
window._ = _;

/**
 * Bootstrap JS is kept for modal, dropdown, and collapse behaviors.
 * CSS has been migrated to Tailwind with a compatibility layer.
 */

import * as Popper from '@popperjs/core';
window.Popper = Popper;

import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

/**
 * We'll load the axios HTTP library which allows us to easily issue requests
 * to our Laravel back-end. This library automatically handles sending the
 * CSRF token as a header based on the value of the "XSRF" token cookie.
 */

import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
