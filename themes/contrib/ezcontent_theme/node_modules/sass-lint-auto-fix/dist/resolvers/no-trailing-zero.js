"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const base_resolver_1 = require("./base-resolver");
class NoTrailingZero extends base_resolver_1.default {
    constructor(ast, parser) {
        super(ast, parser);
        this._trailingZeroRegex = /^(\d+\.|\.)+(\d*?)0+$/;
    }
    fix() {
        const { ast } = this;
        ast.traverseByType('number', (node) => {
            const value = node.content;
            if (this.hasTrailingZero(value)) {
                // Converting to number and back to string drops trailing zeros
                node.content = Number(value).toString();
            }
        });
        return ast;
    }
    hasTrailingZero(value) {
        return !!value.match(this._trailingZeroRegex);
    }
}
exports.default = NoTrailingZero;
