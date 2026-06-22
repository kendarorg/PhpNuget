class Permissions {
    constructor(i) {
        this.i = i;
    }

    // Private helper methods
    prR(str) {
        return str.includes('R') || str.includes('C') || str.includes('U') || str.includes('D');
    }

    prRO(str) {
        return str.includes('O') || this.prR(str);
    }

    prC(str) {
        return str.includes('R') || str.includes('C');
    }

    prCO(str) {
        return str.includes('O') || this.prC(str);
    }

    prD(str) {
        return str.includes('D');
    }

    prDO(str) {
        return str.includes('O') || this.prD(str);
    }

    prU(str) {
        return str.includes('U');
    }

    prUO(str) {
        return str.includes('O') || this.prU(str);
    }

    // Public permission checks
    canRead() {
        return this.prR(this.i);
    }

    canReadOwn() {
        return this.prRO(this.i);
    }

    canCreate() {
        return this.prC(this.i);
    }

    canCreateOwn() {
        return this.prCO(this.i);
    }

    canDelete() {
        return this.prD(this.i);
    }

    canDeleteOwn() {
        return this.prDO(this.i);
    }

    canUpdate() {
        return this.prU(this.i);
    }

    canUpdateOwn() {
        return this.prUO(this.i);
    }

    limited() {
        return this.prR(this.i) || this.i.includes('L');
    }

    limitedOwn() {
        return this.i.includes('X');
    }

    // Throwing methods
    canReadThrow() {
        if (!this.canRead()) sendUnauthorizedResponse(localize(0, "NOT_AUTHORIZED"));
    }

    canReadOwnThrow() {
        if (!this.canReadOwn()) sendUnauthorizedResponse(localize(0, "NOT_AUTHORIZED"));
    }

    canCreateThrow() {
        if (!this.canCreate()) sendUnauthorizedResponse(localize(0, "NOT_AUTHORIZED"));
    }

    canCreateOwnThrow() {
        if (!this.canCreateOwn()) sendUnauthorizedResponse(localize(0, "NOT_AUTHORIZED"));
    }

    canDeleteThrow() {
        if (!this.canDelete()) sendUnauthorizedResponse(localize(0, "NOT_AUTHORIZED"));
    }

    canDeleteOwnThrow() {
        if (!this.canDeleteOwn()) sendUnauthorizedResponse(localize(0, "NOT_AUTHORIZED"));
    }

    canUpdateThrow() {
        if (!this.canUpdate()) sendUnauthorizedResponse(localize(0, "NOT_AUTHORIZED"));
    }

    canUpdateOwnThrow() {
        if (!this.canUpdateOwn()) sendUnauthorizedResponse(localize(0, "NOT_AUTHORIZED"));
    }

    limitedThrow() {
        if (!this.limited()) sendUnauthorizedResponse(localize(0, "NOT_AUTHORIZED"));
    }

    // Utility methods
    value() {
        return this.i;
    }

    canAccess() {
        return this.value().length > 0;
    }
}

