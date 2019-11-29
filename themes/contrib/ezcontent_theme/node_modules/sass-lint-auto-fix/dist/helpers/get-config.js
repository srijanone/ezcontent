"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const merge = require('merge');
const path = require('path');
const fs = require('fs');
const yaml = require('js-yaml');
const _configurationProxy = new Proxy({
    yml: parseYaml,
    yaml: parseYaml,
    json: parseJSON,
}, {
    get(target, filename) {
        const resolvedParserKey = Object.keys(target).find(targetExtension => filename.endsWith(`.${targetExtension}`));
        const resolvedParser = (resolvedParserKey && target[resolvedParserKey]) || parseModule;
        return resolvedParser(filename);
    },
});
function parseYaml(filename) {
    return yaml.safeLoad(fs.readFileSync(filename).toString());
}
function parseJSON(filename) {
    const file = fs.readFileSync(filename).toString();
    return JSON.parse(file);
}
function parseModule(filename) {
    return require(path.resolve(filename));
}
exports.getConfig = (filename) => _configurationProxy[filename];
exports.mergeConfig = (baseConfig, extendedConfig) => merge.recursive(true, baseConfig, extendedConfig);
