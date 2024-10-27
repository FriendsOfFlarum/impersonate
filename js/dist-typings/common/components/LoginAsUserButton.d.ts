/// <reference types="mithril" />
import Button from 'flarum/common/components/Button';
import type { IButtonAttrs } from 'flarum/common/components/Button';
import type User from 'flarum/common/models/User';
export interface ILoginAsUserButtonAttrs extends IButtonAttrs {
    user: User;
    redirectTo?: string;
}
export default class LoginAsUserButton extends Button<ILoginAsUserButtonAttrs> {
    view(): JSX.Element;
    loginAsUser(): void;
}
