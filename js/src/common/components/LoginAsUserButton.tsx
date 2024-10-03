import app from 'flarum/common/app';
import Button from 'flarum/common/components/Button';
import type { IButtonAttrs } from 'flarum/common/components/Button';
import type User from 'flarum/common/models/User';
import ImpersonateModal from './ImpersonateModal';

export interface ILoginAsUserButtonAttrs extends IButtonAttrs {
  user: User;
}

export default class LoginAsUserButton extends Button<ILoginAsUserButtonAttrs> {
  view() {
    return (
      <Button icon="fas fa-id-card" onclick={this.loginAsUser.bind(this)}>
        {app.translator.trans('fof-impersonate.lib.user_controls.impersonate_button')}
      </Button>
    );
  }

  loginAsUser(): void {
    const { user } = this.attrs;
    console.log('loginAsUser', user);
    app.modal.show(ImpersonateModal, {
      callback: () => window.location.reload(),
      user,
    });
  }
}
