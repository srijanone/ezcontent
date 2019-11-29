"use strict";
Object.defineProperty(exports, "__esModule", { value: true });
const base_resolver_1 = require("./base-resolver");
class NoCssComments extends base_resolver_1.default {
    fix() {
        this.ast.traverseByType('multilineComment', (commentNode, commentIndex, commentParent) => {
            if (commentNode.content.charAt(0) !== '!') {
                commentParent.removeChild(commentIndex);
            }
        });
        return this.ast;
    }
}
exports.default = NoCssComments;
