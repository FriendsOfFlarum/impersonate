/// <reference types="mithril" />
import Button from 'flarum/common/components/Button';
import type { IButtonAttrs } from 'flarum/common/components/Button';
export default class ReturnToUserButton extends Button<IButtonAttrs> {
    loading: boolean;
    view(): JSX.Element;
    returnToOriginalUser(): void;
}
