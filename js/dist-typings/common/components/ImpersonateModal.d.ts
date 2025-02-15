import { IFormModalAttrs } from 'flarum/common/components/FormModal';
import FormModal from 'flarum/common/components/FormModal';
import Stream from 'flarum/common/utils/Stream';
import type User from 'flarum/common/models/User';
import type Mithril from 'mithril';
export interface ImpersonateModalAttrs extends IFormModalAttrs {
    user: User;
    callback?: () => void;
}
export default class ImpersonateModal extends FormModal<ImpersonateModalAttrs> {
    user: Stream<User>;
    reason: Stream<string>;
    loading: Stream<boolean>;
    reasonEnabled: Stream<boolean>;
    reasonRequired: Stream<boolean>;
    oninit(vnode: Mithril.Vnode<ImpersonateModalAttrs, this>): void;
    className(): string;
    title(): string;
    content(): Mithril.Children;
    onsubmit(e: Event): void;
}
