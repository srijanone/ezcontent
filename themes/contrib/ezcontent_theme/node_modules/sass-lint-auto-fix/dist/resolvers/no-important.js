"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const base_resolver_1 = require("./base-resolver");
class NoImportant extends base_resolver_1.default {
    fix() {
        this.ast.traverseByType('important', (_, impIndex, impParent) => impParent.removeChild(impIndex));
        return this.ast;
    }
}
exports.default = NoImportant;
