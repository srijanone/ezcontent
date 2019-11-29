"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const base_resolver_1 = require("./base-resolver");
const gonzales = require('gonzales-pe-sl');
class FinalNewline extends base_resolver_1.default {
    constructor(ast, parser) {
        super(ast, parser);
        this._newlineDelimiter = '\n';
    }
    fix() {
        const { ast } = this;
        let newContent = ast.toString();
        if (this.shouldRemoveNewline()) {
            newContent = newContent.trimRight();
        }
        else if (this.shouldAddNewline(newContent)) {
            newContent += this._newlineDelimiter;
        }
        return gonzales.parse(newContent, {
            syntax: ast.syntax,
        });
    }
    shouldAddNewline(raw) {
        return this.parser.options.include && !raw.endsWith(this._newlineDelimiter);
    }
    shouldRemoveNewline() {
        return !this.parser.options.include;
    }
}
exports.default = FinalNewline;
