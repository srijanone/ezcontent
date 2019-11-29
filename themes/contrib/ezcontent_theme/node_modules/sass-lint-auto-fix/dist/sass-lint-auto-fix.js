"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const helpers_1 = require("./helpers");
const typings_1 = require("./typings");
const fs = require('fs');
const glob = require('glob');
const path = require('path');
const slConfig = require('sass-lint/lib/config');
const slRules = require('sass-lint/lib/rules');
const gonzales = require('gonzales-pe-sl');
function autoFixSassFactory(config) {
    const { logger } = config;
    const _slRules = config.slRules || slRules;
    const _slConfig = config.slConfig || slConfig;
    const files = glob.sync(config.files.include, {
        ignore: config.files.ignore,
    });
    return function* autoFixSass(options) {
        for (const filename of files) {
            const content = fs.readFileSync(filename).toString();
            if (content !== null && content !== undefined && content.length > 0) {
                const fileExtension = path
                    .extname(filename)
                    .substr(1)
                    .toLowerCase();
                if (isValidExtension(fileExtension)) {
                    let ast;
                    try {
                        ast = gonzales.parse(content, {
                            syntax: fileExtension,
                        });
                    }
                    catch (e) {
                        logger.warn('parse', filename, e);
                        return;
                    }
                    const rules = _slRules(_slConfig(options));
                    const filteredRules = rules.filter((rule) => config.resolvers[rule.rule.name]);
                    for (const rule of filteredRules) {
                        const { name } = rule.rule;
                        let resolver;
                        try {
                            resolver = createModule({
                                name,
                                ast,
                                rule,
                            });
                        }
                        catch (e) {
                            logger.warn('resolver', `Module '${name}' doesn't exist.`);
                            return;
                        }
                        try {
                            const detects = rule.rule.detect(ast, rule);
                            if (detects.length > 0) {
                                logger.verbose('fix', `Running resolver "${name}" on "${filename}"`);
                                ast = resolver.fix();
                                yield {
                                    ast,
                                    filename,
                                    rule,
                                };
                            }
                        }
                        catch (e) {
                            if (!config.options.optOut) {
                                helpers_1.reportIncident(e);
                            }
                            // TODO: Friendly way to inform user that an unexpected error occured
                            logger.warn('error', e);
                        }
                    }
                }
            }
        }
    };
}
exports.autoFixSassFactory = autoFixSassFactory;
function createModule({ ast, name, rule, }) {
    const _module = require(`./resolvers/${name}`).default;
    return new _module(ast, rule);
}
exports.createModule = createModule;
function isValidExtension(fileExtension) {
    return fileExtension in typings_1.ValidFileType;
}
exports.isValidExtension = isValidExtension;
