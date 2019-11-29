import { Integration, SentryEvent, SentryEventHint } from '@sentry/types/esm';
/** JSDoc */
interface ExtraErrorDataOptions {
    depth?: number;
}
/** Patch toString calls to return proper name for wrapped functions */
export declare class ExtraErrorData implements Integration {
    private readonly options;
    /**
     * @inheritDoc
     */
    name: string;
    /**
     * @inheritDoc
     */
    static id: string;
    /**
     * @inheritDoc
     */
    constructor(options?: ExtraErrorDataOptions);
    /**
     * @inheritDoc
     */
    setupOnce(): void;
    /**
     * Attaches extracted information from the Error object to extra field in the SentryEvent
     */
    enhanceEventWithErrorData(event: SentryEvent, hint?: SentryEventHint): SentryEvent;
    /**
     * Extract extra information from the Error object
     */
    private extractErrorData;
}
export {};
