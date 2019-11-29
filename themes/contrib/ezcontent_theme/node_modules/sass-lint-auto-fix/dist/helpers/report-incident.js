"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const Sentry = require('@sentry/node');
const { version } = require('../../package.json');
Sentry.init({
    dsn: 'https://01713b27b2bf4584a636aa5f2bb68ae7@sentry.io/1213043',
    release: version,
});
exports.reportIncident = (e) => Sentry.captureException(e);
