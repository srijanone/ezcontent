"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const base_resolver_1 = require("./base-resolver");
const gonzales = require('gonzales-pe-sl');
class SpaceBeforeBang extends base_resolver_1.default {
    fix() {
        const { ast } = this;
        ast.traverseByTypes(['important', 'default', 'global', 'optional'], (_, index, parent) => {
            const prev = parent.content[index - 1] || {};
            const isSpace = prev.type === 'space';
            if (this.shouldAddSpaceBeforeBang()) {
                if (!isSpace) {
                    const spaceNode = gonzales.createNode({
                        type: 'space',
                        content: ' ',
                    });
                    parent.content.splice(index, 0, spaceNode);
                }
            }
            else if (isSpace) {
                parent.removeChild(index - 1);
            }
        });
        return ast;
    }
    shouldAddSpaceBeforeBang() {
        return this.parser.options.include;
    }
}
exports.default = SpaceBeforeBang;
