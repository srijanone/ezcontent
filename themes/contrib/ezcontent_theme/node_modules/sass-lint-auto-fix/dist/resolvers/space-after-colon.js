"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const base_resolver_1 = require("./base-resolver");
class SpaceAfterColon extends base_resolver_1.default {
    fix() {
        const { ast, parser } = this;
        const include = parser.options.include;
        ast.traverseByTypes(['propertyDelimiter', 'operator'], (delimiter, i, parent) => {
            if (delimiter.content === ':') {
                const next = parent.content[i + 1] || {};
                if (next.type === 'space') {
                    if (!include) {
                        parent.content.splice(i + 1, 1);
                    }
                }
                else if (parser.options.include) {
                    delimiter.content += ' ';
                }
            }
        });
        return ast;
    }
}
exports.default = SpaceAfterColon;
