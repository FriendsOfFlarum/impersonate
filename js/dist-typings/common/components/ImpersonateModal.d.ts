import Modal, { IInternalModalAttrs } from 'flarum/common/components/Modal';
import Stream from 'flarum/common/utils/Stream';
import type User from 'flarum/common/models/User';
import type Mithril from 'mithril';
import type { NestedStringArray } from '@askvortsov/rich-icu-message-formatter';
export interface ImpersonateModalAttrs extends IInternalModalAttrs {
    user: User;
    callback?: () => void;
}
export default class ImpersonateModal extends Modal<ImpersonateModalAttrs> {
    user: Stream<User>;
    reason: Stream<string>;
    loading: Stream<boolean>;
    reasonEnabled: Stream<boolean>;
    reasonRequired: Stream<boolean>;
    oninit(vnode: Mithril.Vnode<ImpersonateModalAttrs, this>): void;
    className(): string;
    title(): NestedStringArray;
    content(): Mithril.Children;
    onsubmit(e: Event): void;
}
