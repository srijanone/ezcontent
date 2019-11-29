#!/usr/bin/env node
"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const helpers_1 = require("./helpers");
const sass_lint_auto_fix_1 = require("./sass-lint-auto-fix");
const process = require('process');
const program = require('commander');
const fs = require('fs');
const { version } = require('../package.json');
(() => {
    program
        .version(version)
        .usage('"<pattern>" [options]')
        .option('-c, --config <path>', 'custom config path (e.g /path/to/.sass-lint-auto-fix.yml)')
        .option('--config-sass-lint <path>', 'custom sass lint config path (e.g /path/to/.sass-lint.yml')
        .option('-s, --silent', 'runs in silent mode')
        .option('-d, --debug', 'runs in debug mode')
        .parse(process.argv);
    const logger = helpers_1.createLogger({
        silentEnabled: program.silent,
        debugEnabled: program.debug,
    });
    const config = helpers_1.getConfig(require.resolve('./config/default.yml'));
    let slConfig = {};
    let defaultOptions = Object.assign({}, config);
    if (program.config) {
        // TOOD: Handle different configuration types
        const customConfiguration = helpers_1.getConfig(program.config);
        defaultOptions = helpers_1.mergeConfig(defaultOptions, customConfiguration);
    }
    // Pass in custom sass-lint configuration
    if (program.configSassLint) {
        slConfig = helpers_1.getConfig(program.configSassLint);
    }
    if (!defaultOptions.options.optOut) {
        logger.debug('Installing sentry');
    }
    process.on('unhandledRejection', (error) => {
        if (!defaultOptions.options.optOut) {
            helpers_1.reportIncident(error);
        }
        logger.error(error);
    });
    process.on('uncaughtException', (error) => {
        if (!defaultOptions.options.optOut) {
            helpers_1.reportIncident(error);
        }
        logger.error(error);
        process.exit(1);
    });
    const pattern = program.args[0];
    defaultOptions.files.include = pattern || defaultOptions.files.include;
    const sassLintAutoFix = sass_lint_auto_fix_1.autoFixSassFactory(Object.assign({ logger }, defaultOptions));
    // TODO: Add sass-lint config, right now will merge with default rule set
    for (const { filename, ast } of sassLintAutoFix(slConfig)) {
        fs.writeFileSync(filename, ast.toString());
        logger.verbose('write', `Writing resolved tree to ${filename}`);
    }
})();
